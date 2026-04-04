<?php session_cache_expire(30);
    session_start();
    //the purpose of this page is to allow admins to adjust the hours a person worked.

    require_once('include/input-validation.php');
    require_once('database/dbEvents.php');
    require_once('database/dbRoleEvents.php');
    require_once('database/dbRoles.php');
    require_once('database/dbpersonhours.php');
    require_once('domain/Person.php');
    require_once('domain/Event.php');
    require_once('include/output.php');

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

        if(!isset($args['event_id'])){
            echo "No event ID provided.";
            die();
        }
        if(!isset($args['user_id'])){
            echo "No user ID provided.";
            die();
        }
        $event = retrieve_event($args['event_id']);
        if($_POST['nav'] === 'viewEventSignUps'){

        }

    }

    
    $date = null;
    if ($event->getStartDate()) {
        $date = $event->getStartDate();
        $datePattern = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
        $timeStamp = strtotime($date);
        if (!preg_match($datePattern, $date) || !$timeStamp) {
            header('Location: calendar.php');
            die();
        }
    }

    include_once('database/dbinfo.php'); 
    $con=connect();  

    $allRoles = getRolesForEvent($args['event_id']); // For displaying the form
?><!DOCTYPE html>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <title>Adjust Hours &#x7c Love Thy Neighbor Community Food Pantry</title>
    </head>
    <body>
        <?php require_once('header.php') ?>
        <h1 style="color: var(--main-color); font-weight: bold;">Adjust <?php $person = get_person_by_id($_POST['user_id']); echo $person['first_name'] . ' ' . $person['last_name'] ;?>'s Volunteer Hours</h1>
        <main class="date">
            <h2 style="color: var(--main-color); font-weight: bold;"></h2>
                <div class="event-sect">
                <label for="name">Event Name</label>
                <p><?php echo $event->getName();?></p> 
                </div>

                <div class="event-sect">
                <div class="event-datetime">
                    <div class="event-time">
                        <div class="event-date">
                            <label for="name">Date</label>
                            <p><?php echo $date;?></p>
                        </div>
                        <div class="event-date">
                            <label for="name">Start Time</label>
                            <p><?php echo time24hto12h($event->getStartTime());?></p>
                        </div>
                        <div class="event-date">
                            <label for="name">End Time</label>
                            <p><?php echo time24hto12h($event->getEndTime());?></p>
                        </div>
                    </div>
                </div>
                </div>

                <div class="event-sect">
                    <label>Roles</label>
                    <table class="roles-table" style="width: 90%;">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th style="text-align: left;">Clock In Time</th>
                                <th>Clock Out Time</th>
                                <th></th>
                            </tr>
                        </thead> 
                        <tbody>
                        <tbody>
                            <?php
                                $query = "SELECT roleID, start_time, end_time FROM dbpersonhours WHERE personID = '" . $args['user_id'] . "' AND eventID = '" . $args['event_id'] . "'";
                                $result = mysqli_query($con, $query);
                                $rolesPerformed = array();
                                foreach($result as $row){
                                    $rolesPerformed[$row['roleID']] = TRUE;
                                }
                            ?>
                            <?php foreach ($allRoles as $role): ?>
                                <form method=POST>
                                <tr>
                                    <td><?php echo htmlspecialchars($role['role_name']); ?></td>
                                    <td>
                                        <?php if(isset($rolesPerformed[$role['roleID']])): ?>
                                            <input type="text" id="start-time" name="start-time" value="<?php echo 'echo their start time';?>" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter start time. Ex. 12:00 PM">
                                        <?php else: ?>
                                            <input type="text" id="start-time" name="start-time" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter start time. Ex. 12:00 PM">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(isset($rolesPerformed[$role['roleID']])): ?>
                                            <input type="text" id="end-time" name="end-time" value="<?php echo 'echo their end time';?>" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter start time. Ex. 12:00 PM">
                                        <?php else: ?>
                                            <input type="text" id="end-time" name="end-time" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter start time. Ex. 12:00 PM">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="submit" class="button">Adjust Hours</button>
                                    </td>
                                </tr>
                                <input type="hidden" name="nav" value="adjustEventHours"/>
                                </form>
                            <?php endforeach; ?>
                        </tbody>

                    </table>   
                </div>
                </fieldset>

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
                    <a class="button cancel" href="index.php" style="margin-top: -.5rem; font-weight:bold;">Return to Dashboard</a>

 
        </main>
    </body>
</html>
