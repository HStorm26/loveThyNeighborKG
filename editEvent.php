<?php
    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();

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
        echo 'bad access level';
        die();
    }

    require_once('include/input-validation.php');
    require_once('database/dbEvents.php');

    $errors = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $args = sanitize($_POST, null);
        $required = array(
            "id", "name", "date", "start-time", "description", "end-time", "capacity", "location"
        );

        if (!wereRequiredFieldsSubmitted($args, $required)) {
            echo 'bad form data';
            die();
        } else {
            require_once('database/dbPersons.php');

            $id = $args['id'];
            $existingEvent = fetch_event_by_id($id);

            $validated = validate12hTimeRangeAndConvertTo24h($args["start-time"], $args["end-time"]);
            if (!$validated) {
                $errors .= '<p>The provided time range was invalid.</p>';
            }

            $startTime = $args['start-time'] = $validated[0];
            $endTime   = $args['end-time']   = $validated[1];
            $date      = $args['date']       = validateDate($args["date"]);

            $capacity = intval($args["capacity"]);
            $assignedVolunteerCount = count(getvolunteers_byevent($id));
            $difference = $assignedVolunteerCount - $capacity;
            if ($capacity < $assignedVolunteerCount) {
               $errors .= "<p>There are currently $assignedVolunteerCount volunteers assigned to this event. The new capacity must not exceed this number. You must remove $difference volunteer(s) from the event to reduce the capacity to $capacity.</p>";
            }

            if (!$startTime || !$date > 11){
                $errors .= '<p>Your request was missing arguments.</p>';
            }

            if (!$errors) {
                $success = update_event($id, $args);
                if (!$success){
                    echo "Oopsy!";
                    die();
                }

                $isRecurring    = isset($_POST['recurring']) ? 1 : 0;
                $recurrenceType = $isRecurring ? ($_POST['recurrence_type'] ?? '') : '';
                $customDays     = ($isRecurring && $recurrenceType === 'custom')
                                  ? (int)($_POST['custom_days'] ?? 0)
                                  : 0;

                if (
                    $isRecurring &&
                    in_array($recurrenceType, ['daily','weekly','monthly','custom'], true) &&
                    (!$existingEvent || empty($existingEvent['series_id']))
                ) {
                    require_once('database/dbinfo.php');
                    $con = connect();

                    $counts = [
                        'daily'   => 30,
                        'weekly'  => 12,
                        'monthly' => 6,
                        'custom'  => 12,
                    ];
                    $intervalMap = [
                        'daily'   => 'P1D',
                        'weekly'  => 'P1W',
                        'monthly' => 'P1M',
                    ];

                    if ($recurrenceType === 'custom') {
                        if ($customDays < 1) {
                            $customDays = 1;
                        }
                        $intervalSpec = 'P' . $customDays . 'D';
                    } else {
                        $intervalSpec = $intervalMap[$recurrenceType] ?? null;
                    }

                    if ($intervalSpec && isset($counts[$recurrenceType])) {
                        $current = new DateTime($date);
                        $step    = new DateInterval($intervalSpec);
                        $times   = $counts[$recurrenceType];

                        for ($i = 0; $i < $times; $i++) {
                            $current->add($step);
                            $ymd = $current->format('Y-m-d');

                            $dup = $args;
                            $dup['date']       = $ymd;
                            $dup['start-time'] = $args['start-time'];
                            $dup['end-time']   = $args['end-time'];
                            $dup['series_id']  = $series_id;

                            create_event($dup);
                        }
                    }

                    mysqli_close($con);
                }

                header('Location: event.php?id=' . $id . '&editSuccess');
                die();
            }
        }
    }

    if (!isset($_GET['id'])) {
        die();
    }

    $args  = sanitize($_GET);
    $id    = $args['id'];
    $event = fetch_event_by_id($id);
    if (!$event) {
        echo "Event does not exist";
        die();
    }

    require_once('include/output.php');

    include_once('database/dbinfo.php'); 
    $con = connect();
?>
<!DOCTYPE html>
<html>
    <head>
        <!-- <?php require_once('universal.inc') ?> -->
        <title>Edit Event | Love Thy Neighbor Community Food Pantry</title>
        <link rel="stylesheet" href="layoutInfo.css">
    </head>
    <body>
        <?php require_once('header.php') ?>
        <div class="info-form">
            <div class="page-wrapper">
                <div class="info-card">
                    <div class="info-header">
                        <h1>Edit Event</h1>
                    </div>
                    <!-- <main class="date"> -->
                    <?php if ($errors): ?>
                        <div class="error-toast"><?php echo $errors ?></div>
                    <?php endif ?>
                        <!-- <h2>Event Details</h2> -->
                        <form id="new-event-form" method="post">
                            <label for="name">Event Name </label>
                            <input type="hidden" name="id" value="<?php echo $id ?>"/> 
                            <input type="text" id="name" name="name" value="<?php echo $event['name'] ?>" required placeholder="Enter name"> 

                            <label for="name">Date </label>
                            <input type="date" id="date" name="date" value="<?php echo $event['date'] ?>" min="<?php echo date('Y-m-d'); ?>" required>

                            <label for="name">Start Time </label>
                            <input type="text" id="start-time" name="start-time" value="<?php echo time24hto12h($event['startTime']) ?>" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter start time. Ex. 12:00 PM">

                            <label for="name">End Time </label>
                            <input type="text" id="end-time" name="end-time" value="<?php echo time24hto12h($event['endTime']) ?>" pattern="([1-9]|10|11|12):[0-5][0-9] ?([aApP][mM])" required placeholder="Enter end time. Ex. 12:00 PM">

                            <label for="name">Description </label>
                            <input type="text" id="description" name="description" value="<?php echo $event['description'] ?>" required placeholder="Enter description">

                            <label for="name">Location </label>
                            <input type="text" name="location" value="<?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?>">

                            <label for="name">Capacity </label>
                            <input type="number" id="capacity" name="capacity" value="<?php echo $event['capacity'] ?>" placeholder="Enter capacity (e.g. 1-99)">

                            <div class="email-actions">
                                <button type="submit" name="action" value="send" class="submit-btn">Update Event</button>
                                <!--<button type="submit" name="action" value="draft" class="draft-btn">Save Draft</button> -->
                            </div>
                            <div class="email-actions">
                                <a class="submit-btn" href="event.php?id=<?php echo htmlspecialchars($_GET['id']) ?>" style="margin-top: .5rem">Cancel</a>
                            </div>
                        </form>
                        <!-- </main> -->
                    </div>
                </div>
            </div>
        <?php include 'footer.php'; ?>
    </body>
</html>
