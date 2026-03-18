<?php
    //- Brooke did this page for Love Thy Neighbor KG (Sign up)
    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();

    require_once('include/input-validation.php');
    require_once('database/dbEvents.php');
    require_once('database/dbPersons.php');
    require_once('database/dbRoleEvents.php');

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;

    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    } 

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $args = sanitize($_POST, null);

        $required = array("event-name", "account-name", "roleID");
  
        if (!wereRequiredFieldsSubmitted($args, $required)) {
            echo 'bad form data';
            die();
        }

        $name = htmlspecialchars_decode($args['event-name']);
        $account_name = htmlspecialchars_decode($args['account-name']);
        $role = isset($args['role']) ? $args['role'] : '';
        $notes = '';
        $id = sign_up_for_event($name, $account_name, $role, $notes); 
        if (!$id) {
            header('Location: eventFailure.php');
            exit();
        }

        require_once('database/dbMessages.php');
        send_system_message(
            $userID,
            "You are now signed up for $name!",
            "Thank you for signing up for $name!"
        );

        header('Location: signupSuccess.php');
        die();
    }

    // Connect to database
    include_once('database/dbinfo.php'); 
    $con = connect();  

    // Get event info from GET parameters (accept either `id` or `event_id`)       \
    if (isset($_GET['id'])) {
        $event_id = intval($_GET['id']);
    } elseif (isset($_GET['event_id'])) {
        $event_id = intval($_GET['event_id']);
    } else {
        $event_id = 0;
    }
    // Stop signup if event has already ended
    if (is_archived($event_id)) {
        echo "This event has already ended and signups are closed.";
        exit();
    }
    $event_name = isset($_GET['event_name']) ? htmlspecialchars($_GET['event_name']) : '';
    $type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : '';

    if ($event_id === 0){
        header('Location: requestFailed.php');
                die();
    }

    // Retrieve user info from session
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $account_name = isset($_SESSION['_id']) ? $_SESSION['_id'] : '';

    // Get the roles for this particular event -Brooke
    $roles = getRolesForEvent($event_id);
?>
<!DOCTYPE html>
<html>
    <head>
        <?php require_once('universal.inc') ?>
        <title>Sign-Up for Event | Love Thy Neighbor Community Food Pantry</title>
    </head>
    <body>
        <?php require_once('header.php') ?>
        <h1 style="color: #004AAD; font-weight: bold;">Sign-Up for Event</h1>
        <main class="date">

            <form id="new-event-form" method="post">
                <!-- ✅ Hidden event ID -->
                <div class="event-sect">     
                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

                    <label for="event-name">* Event Name </label>
                        <input type="text" id="event-name" name="event-name" required 
                        value="<?php echo htmlspecialchars_decode($event_name, ENT_QUOTES); ?>"
                        placeholder="Event name" readonly>
                </div>        
                
                <!-- Autofill and make the account name readonly -->    
                <div class="event-sect">           
                    <label for="account-name">* Your Account Name </label>
                    <input type="text" id="account-name" name="account-name" 
                        <?php echo ($accessLevel >= 2) ? '' : 'readonly'; ?> 
                        value="<?php echo htmlspecialchars($account_name); ?>" 
                        placeholder="Enter account name">
                </div>

                <div class="event-sect">
                    <label for="notes">Notes</label>
                    <input type="text" id="notes" name="notes">
                </div>
                <div class="event-sect">
                    <label>* Choose a Role</label>
                    <table class="roles-table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Role</th>
                                <th>Availability</th>
                                <th>Description</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($roles as $role): ?>
                            <tr>
                                <td class="role-select">
                                    <input
                                        type="radio"
                                        name="roleID"
                                        value="<?php echo (int)$role['roleID']; ?>"
                                        required
                                    >
                                </td>

                                <td class="role-name">
                                    <?php echo htmlspecialchars($role['role_name'] ?? ''); ?>
                                </td>

                                <td class="role-capacity">
                                    <?php echo (int)($role['capacity'] ?? 0); ?> spots available
                                </td>

                                <td class="role-description">
                                    <?php echo htmlspecialchars($role['role_description'] ?? ''); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>    
                <br/>
                <input type="submit" value="Sign up for Event">
            </form>

            <a class="button cancel" href="index.php" style="margin-top: -.5rem">Return to Dashboard</a>
        </main>
    </body>
</html>
