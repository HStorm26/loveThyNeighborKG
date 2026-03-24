<?php session_cache_expire(30);
    session_start();
    // -Brooke did this for Love Thy Neighbor KG (Create Event)
    // Make session information accessible, allowing us to associate
    // data with the logged-in user.

    require_once('include/input-validation.php');
    require_once('database/dbEvents.php');
    require_once('database/dbRoleEvents.php');
    require_once('database/dbRoles.php');

    ini_set("display_errors",1);
    error_reporting(E_ALL);

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    } 
    // Require admin privileges
    if ($accessLevel < 2) {
        header('Location: login.php');
        //echo 'bad access level';
        die();
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //require_once('include/input-validation.php');
        //require_once('database/dbEvents.php');
        $args = sanitize($_POST, null);
        $required = array(
            "name", "date", "start-time", "end-time", "description", "location"
        );
        $roles = $_POST['roles'] ?? array(); //For the total capacity
        //$startTimes = $_POST['start_times'] ?? array(); --BROOKE DELETE
        //$endTimes = $_POST['end_times'] ?? array();  --BROOKE DELETE

        /* For the total capacity */
        $totalCapacity = 0;
        foreach ($roles as $roleID => $count) {
            $count = (int)$count;

            if($count < 0) {
                $count = 0;
            }
            $totalCapacity += $count;
        }

        /* At least one role was chosen */
        if ($totalCapacity <= 0) {
            echo "You must enter at least one volunteer role.";
            die();
        }

        
        /*Database will skip roles with 0 volunteers, so it doesn't store
        the unnecessary roles!*/
        //foreach ($roles as $role => $count) {
            //$count = (int)$count;

            //if($count > 0) {
                //$start = $startTimes[$role];
                //$end = $endTimes[$role];
                // save this role to the database
            //}
        //}
        

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            echo 'bad form data';
            die();
        }

        
            // Accept either HTML5 24h time (HH:MM) or 12h times with am/pm
        if (validate24hTimeRange($args['start-time'], $args['end-time'])) {
            $startTime = $args['start-time'];
            $endTime = $args['end-time'];
        } else {
            $validated = validate12hTimeRangeAndConvertTo24h($args["start-time"], $args["end-time"]);
            if (!$validated) {
                echo 'bad time range';
                die();
            }
            $startTime = $args['start-time'] = $validated[0];
            $endTime = $args['end-time'] = $validated[1];
        }
        $date = $args['date'] = validateDate($args["date"]);
        $args["training_level_required"] = $_POST['training_level_required'] ?? 'None';
    
        $args['date']   = $date;   
        $args['startTime'] = $startTime;
        $args['endTime']   = $endTime;


        //1. Start of use case #8 recurring, etc
        $isRecurring = isset($_POST['recurring']) ? 1 : 0;
        $recurrenceType = $isRecurring ? ($_POST['recurrence_type'] ?? '') : '';
        $customDays = ($isRecurring && $recurrenceType === 'custom') ? (int)($_POST['custom_days'] ?? 0) : null;

            
        if ($isRecurring) {
            if (!in_array($recurrenceType, ['daily','weekly','monthly','custom'], true)) {
                echo 'invalid recurrence type';
                die();
            }
            if ($recurrenceType === 'custom' && (!$customDays || $customDays < 1)) {
                echo 'invalid custom interval';
                die();
            }
            $args['is_recurring'] = 1;
            $args['recurrence_type'] = $recurrenceType;                  // daily|weekly|monthly|custom
            $args['recurrence_interval_days'] = ($recurrenceType === 'custom') ? $customDays : null;
        } else {
            $args['is_recurring'] = 0;
            $args['recurrence_type'] = null;
            $args['recurrence_interval_days'] = null;
        }
        //1. Start of use case #8 recurring, etc

        // FIXED: Replaced the broken check "if (!$date > 11)"
        if (!$startTime || !$endTime || !$date){
            echo 'bad args';
            die();
        }
        $args['capacity'] = $totalCapacity;
        //$args['roles'] = $roles;
        //$args['start_times'] = $startTimes;
        //$args['end_times'] = $endTimes;

        //$args['series_id'] = bin2hex(random_bytes(16)); // new new /*It really disliked it, so I had to change it -Brooke */
        $args['series_id'] = random_int(100, 999999); 

        $id = create_event($args);
        if (!$id) {
            die();
        } else {

            // Save the roles for this event
            save_event_roles($id, $roles);
    
            $counts = [
                'daily'   => 30,  // next 30 days
                'weekly'  => 12,  // next 12 weeks
                'monthly' => 6,   // next 6 months
                'custom'  => 12,  // 12 custom intervals
            ];
                
            $intervalMap = [
                'daily'   => 'P1D',
                'weekly'  => 'P1W',
                'monthly' => 'P1M',
            ];
            if ($recurrenceType === 'custom') {
                $customDays = max(1, $customDays);
                $intervalSpec = 'P' . $customDays . 'D';
            } else {
                $intervalSpec = $intervalMap[$recurrenceType] ?? null;
            }

            if ($isRecurring && $intervalSpec && isset($counts[$recurrenceType])) {
                $current = new DateTime($args['date']);  
                $step    = new DateInterval($intervalSpec);
                $times   = $counts[$recurrenceType];

                for ($i = 0; $i < $times; $i++) {
                    $current->add($step);
                    $ymd = $current->format('Y-m-d');

                    $dup = $args;                 
                    $dup['date']      = $ymd;    

                    create_event($dup);
                }
            }        
            
            header('Location: eventsuccess.php');
            exit();
        }
    }

    
    $date = null;
    if (isset($_GET['date'])) {
        $date = $_GET['date'];
        $datePattern = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
        $timeStamp = strtotime($date);
        if (!preg_match($datePattern, $date) || !$timeStamp) {
            header('Location: calendar.php');
            die();
        }
    }

    include_once('database/dbinfo.php'); 
    $con=connect();  

    $allRoles = get_roles(); // For displaying the form
?><!DOCTYPE html>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <title>Create Event | Love Thy Neighbor Community Food Pantry</title>
    </head>
    <body>
        <?php require_once('header.php') ?>
        <h1 style="color: var(--main-color); font-weight: bold;">Create Event</h1>
        <main class="date">
            <h2 style="color: var(--main-color); font-weight: bold;">New Event Form</h2>
            <form id="new-event-form" method="POST">
                <div class="event-sect">
                <label for="name">* Event Name </label>
                <input type="text" id="name" name="name" required placeholder="Enter name"> 
                </div>

                <div class="event-sect">
                <div class="event-datetime">
                    <div class="event-time">
                        <div class="event-date">
                        <label for="name">* Date </label>
                        <input type="date" id="date" name="date" <?php if ($date) echo 'value="' . $date . '"'; ?> min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="event-date">
                        <label for="name">* Start Time </label>
                        <input type="time" id="start-time" name="start-time" required>
                        </div>
                    </div>
                    <div class="event-time"> 
                        <!-- <div class="event-date">
                        <label for="name">* End Date</label>
                        <input type="date" id="end-date" name="end-date" <?php if ($date) echo 'value="' . $date . '"'; ?> min="<?php echo date('Y-m-d'); ?>" required>
                        </div> -->
                        <div class="event-date">
                        <label for="name">* End Time </label>
                        <input type="time" id="end-time" name="end-time" required>
                        </div>
                    </div>
                </div>
                </div>
                <div class="event-sect">
                <label for="name">* Description </label>
                <input type="text" id="description" name="description" required placeholder="Enter description">

                <label for="name">Location </label>
                <input type="text" id="location" name="location" placeholder="Enter location">
                </div>    

                <div class="event-sect">
                    <label>* Roles</label>
                    <table class="roles-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Number Needed</th>
                                <th>Description</th>
                            </tr>
                        </thead> 
                        <tbody>
                        <tbody>
                            <?php foreach ($allRoles as $role): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($role['role']); ?></td>
                                    <td>
                                        <input
                                            type="number"
                                            class="role-count"
                                            name="roles[<?php echo (int)$role['role_id']; ?>]"
                                            value="0"
                                            min="0"
                                            max="100"
                                            oninput="if(this.value > 100) this.value = 100"
                                            onkeydown="if(event.key === '-') event.preventDefault();"
                                        >
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($role['role_description']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                            <!--<tr>
                                <td>Truck Unloader</td>
                                <td>
                                    <input type="number" class="role-count" name="roles[truck_unloader]" value="0" min="0" 
                                    max="100" oninput="if(this.value > 100) this.value = 100" onkeydown="if(event.key === '-') event.preventDefault();">
                                </td> 
                                <td>
                                    <p type="name" id="truck_unloader_desc" name="description">Show up during the time slot</p>
                                </td>
                            </tr>   
                            <tr>
                                <td>Sorting</td>
                                <td>
                                    <input type="number" class="role-count" name="roles[sorting]" value="0" min="0" 
                                    max="100" oninput="if(this.value > 100) this.value = 100" onkeydown="if(event.key === '-') event.preventDefault();">
                                </td>    
                                <td>
                                    <p type="name" id="sorting_desc" name="description">Show up during the time slot</p>
                                </td>
                            </tr> 
                            <tr>
                                <td>Distribution</td>
                                <td>
                                    <input type="number" class="role-count" name="roles[distribution]" value="0" min="0" 
                                    max="100" oninput="if(this.value > 100) this.value = 100" onkeydown="if(event.key === '-') event.preventDefault();">
                                </td>   
                                <td>
                                    <p type="name" id="distribution_desc" name="description">Show up during time slot</p>
                                </td>
                            </tr>  
                            <tr>
                                <td>Setup</td>
                                <td>
                                    <input type="number" class="role-count" name="roles[setup]" value="0" min="0"
                                    max="100" oninput="if(this.value > 100) this.value = 100" onkeydown="if(event.key === '-') event.preventDefault();">
                                </td>
                                <td>
                                    <p type="name" id="setup_desc" name="description">Arrive 15 minutes early</p>
                                </td>
                            </tr> 
                            <tr>
                                <td>Cleanup</td>
                                <td>
                                    <input type="number" class="role-count" name="roles[cleanup]" value="0" min="0" 
                                    max="100" oninput="if(this.value > 100) this.value = 100" onkeydown="if(event.key === '-') event.preventDefault();" >
                                </td>
                                <td>
                                    <p type="name" id="clean_up_desc" name="description">Stay 15 minutes afterwards</p>
                                </td>  
                            </tr> 
                        </tbody> -->
                        <tfoot>
                            <tr>
                                <th>Total Capacity</th>
                                <th>
                                    <input type="number" id="totalCapacity" name="capacity" placeholder="Enter total capacity" value="0" readonly>
                                </th>
                        </tfoot>

                    </table>   
                    <!-- <input type="hidden" id="rolesRequired" required> -->
                </div>

                <div class="event-sect">
                    <fieldset style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                        <legend style="color: var(--main-color); font-weight:bold;">Make this a recurring event</legend>
                        <label style="margin-top:12px; padding:12px; border:1px solid #e0e0e0; border-radius:8px;">
                            <input type="checkbox" id="recurring" name="recurring" value="1">
                            Recurring
                        </label>
                </div>

                    <div id="recurring-options" style="display:none; margin-top:6px;">
                        <label for="recurrence_type">Recurrence:</label>
                        <select name="recurrence_type" id="recurrence_type">
                            <option value="">-- Select --</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="custom">Custom</option>
                        </select>

                        <div id="custom-interval" style="display:none; margin-top:8px;">
                            <label for="custom_days">Repeat every:</label>
                            <input type="number" min="1" id="custom_days" name="custom_days" placeholder="e.g. 10">
                            <span>days</span>
                        </div>
                    </div>
                </fieldset>
                
                <input type="submit" value="Create Event" style="width:100%; font-weight:bold;">
                
            </form>
                <script>
                    // Debug: log submit attempts and list invalid fields
                    (function(){
                        const form = document.getElementById('new-event-form');
                        if(!form) return;
                        form.addEventListener('submit', function(e){
                            try{
                                console.log('addEvent form submit event', e);
                                const ok = form.checkValidity();
                                console.log('form.checkValidity()', ok);
                                if(!ok){
                                    e.preventDefault();
                                    const invalids = [];
                                    form.querySelectorAll(':invalid').forEach(function(el){ invalids.push({name: el.name, type: el.type, value: el.value}); });
                                    console.error('Form invalid fields:', invalids);
                                    alert('Form validation failed for: ' + invalids.map(i=>i.name).join(', '));
                                } else {
                                    console.log('Form appears valid; letting submit proceed');
                                }
                            }catch(err){
                                console.error('Error in submit debug handler', err);
                            }
                        }, false);
                    })();
                </script>
                <?php if ($date): ?>
                    <a class="button cancel" href="calendar.php?month=<?php echo substr($date, 0, 7) ?>" style="margin-top: -.5rem; font-weight:bold;">Return to Calendar</a>
                <?php else: ?>
                    <a class="button cancel" href="index.php" style="margin-top: -.5rem; font-weight:bold;">Return to Dashboard</a>
                <?php endif ?>

                <script type="text/javascript">
                    $(document).ready(function(){
                        var checkboxes = $('.checkboxes');
                        checkboxes.change(function(){
                            if($('.checkboxes:checked').length>0) {
                                checkboxes.removeAttr('required');
                            } else {
                                checkboxes.attr('required', 'required');
                            }
                        });
                    });

                    (function(){
                        const recurring = document.getElementById('recurring');
                        const options = document.getElementById('recurring-options');
                        const recurrenceType = document.getElementById('recurrence_type');
                        const customBlock = document.getElementById('custom-interval');
                        const customDays = document.getElementById('custom_days');

                        function toggleOptions(){
                            const on = recurring && recurring.checked;
                            if (options) options.style.display = on ? 'block' : 'none';
                            if (!on) {
                                if (recurrenceType) recurrenceType.value = '';
                                if (customBlock) customBlock.style.display = 'none';
                                if (customDays) customDays.value = '';
                            }
                        }
                        function toggleCustom(){
                            if (!recurrenceType || !customBlock) return;
                            customBlock.style.display = (recurrenceType.value === 'custom') ? 'block' : 'none';
                            customBlock.style.display = (recurrenceType.value === 'custom') ? 'block' : 'none';
                        }

                        if (recurring) {
                            recurring.addEventListener('change', toggleOptions);
                            toggleOptions();
                        }
                        if (recurrenceType) {
                            recurrenceType.addEventListener('change', toggleCustom);
                            toggleCustom();
                        }

                        /* The capacity will add up as the roles are incremented by the admin on the form */
                        function updateTotalCapacity() {
                            const roles = document.querySelectorAll(".role-count");
                            let total = 0;

                            roles.forEach(function(role) {
                                total += parseInt(role.value) || 0;
        
                            });
                            document.getElementById("totalCapacity").value = total;
                        }

                        document.querySelectorAll(".role-count").forEach(function(input) {
                            input.addEventListener("input", updateTotalCapacity);
                        });
                        updateTotalCapacity();


                    })();
                </script>
                <script>
                const roleCounts = document.querySelectorAll(".role-count");
                const firstRoleInput = document.querySelector(".role-count");
                const totalCapacity = document.getElementById("totalCapacity");

                // Clear error as soon as user types
                roleCounts.forEach(function (input) {
                    input.addEventListener("input", function () {
                        input.setCustomValidity("");
                    });
                });

                document.getElementById("new-event-form").addEventListener("submit", function (e) {

                    let total = 0;

                    // Clear errors and calculate total
                    roleCounts.forEach(function (countInput) {
                        countInput.setCustomValidity("");
                        total += Number(countInput.value) || 0;
                    });

                    totalCapacity.value = total;

                    // Require at least one role
                    if (total === 0) {
                        e.preventDefault();
                        firstRoleInput.setCustomValidity("At least one role must have a number greater than 0.");
                        firstRoleInput.reportValidity();
                        firstRoleInput.focus();
                    }
                });
                </script>

        </main>
    </body>
</html>
