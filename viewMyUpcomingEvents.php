<?php
session_cache_expire(30);
session_start();  // Start the session

// Check if the user is logged in
if (!isset($_SESSION['_id']) || empty($_SESSION['_id'])) {
    header('Location: login.php');
    exit();
}

// Check for appropriate access level
if ($_SESSION['access_level'] < 1) {
    header('Location: index.php');
    exit();
}

require_once('include/input-validation.php');
require_once('database/dbEvents.php');
require_once('database/dbPersons.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['_id'];

$sort = $_GET['sort'] ?? 'asc';
$sortDirection = ($sort === 'desc') ? 'DESC' : 'ASC';
$nextSort = ($sort === 'asc') ? 'desc' : 'asc';

// Handle cancellation of events
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'] ?? null;

    if (!$event_id) {
        echo "Event ID is missing.";
        die();
    }

    if ($event_id) {
        $event_name = fetch_event_name($event_id);

        if (remove_user_from_event($event_id, $user_id)) {
            require_once('database/dbMessages.php');
            send_system_message("vmsroot", "$user_id cancelled their sign up for $event_name", "$user_id cancelled their sign up for $event_name");
            send_system_message($user_id, "Your sign up for $event_name has been cancelled.", "$user_id cancelled their sign up for $event_name");
            $cancel_success = "Successfully canceled your registration for event: $event_name.";
        } else {
            $cancel_error = "Failed to cancel registration for event $event_id.";
        }
    }
}

// Fetch events the user is signed up for
function fetch_user_signups($user_id, $sortDirection) {
    $connection = connect();

    $query = "
        SELECT 
            e.id,
            e.name,
            e.date,
            e.startTime,
            e.endTime,
            e.location,
            GROUP_CONCAT(DISTINCT r.role ORDER BY r.role SEPARATOR ', ') AS roles
        FROM dbevents e
        INNER JOIN dbeventpersons ep ON e.id = ep.eventID
        LEFT JOIN dbroleevents re ON e.id = re.eventID
        LEFT JOIN dbroles r ON re.roleID = r.role_id
        WHERE ep.userID = '$user_id' AND ep.attended = 0
        GROUP BY e.id, e.name, e.date, e.startTime, e.endTime, e.location
        ORDER BY e.date $sortDirection
    ";

    $result = mysqli_query($connection, $query);

    if (!$result) {
        die('Query failed: ' . mysqli_error($connection));
    }

    $events = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }

    mysqli_close($connection);
    return $events;
}

// Fetch event name by ID
function fetch_event_name($event_id) {
    $connection = connect();
    $query = "SELECT name FROM dbevents WHERE id = '$event_id'";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        die('Query failed: ' . mysqli_error($connection));
    }

    $event = mysqli_fetch_assoc($result);
    mysqli_close($connection);

    return $event['name'] ?? 'Unknown Event';
}


$upcoming_events = fetch_user_signups($user_id, $sortDirection);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Upcoming Events</title>
    <link rel="stylesheet" href="layoutInfo.css" />
</head>
<body>
<?php include('header.php'); ?>
<div class="page">

    <!-- Main -->
    <div class="main">
        <div class="page-header">
            <h1>View Events</h1>
        </div>    
        <!-- Table -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>My Role(s)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($upcoming_events)): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                        <tr>
                            <td>
                                <a href="event.php?id=<?php echo htmlspecialchars($event['id']); ?>" class="event-link">
                                    <?php echo htmlspecialchars($event['name']); ?>
                                </a>
                            </td>

                            <td><?php echo htmlspecialchars($event['date']); ?></td>

                            <td>
                                <?php
                                    $start = !empty($event['startTime']) ? date('g:i A', strtotime($event['startTime'])) : null;
                                    $end = !empty($event['endTime']) ? date('g:i A', strtotime($event['endTime'])) : null;

                                    if ($start && $end) {
                                        echo htmlspecialchars($start . ' - ' . $end);
                                    } elseif ($start) {
                                        echo htmlspecialchars($start);
                                    } else {
                                        echo 'TBD';
                                    }
                                ?>
                            </td>

                            <td>
                                <?php echo !empty($event['location']) ? htmlspecialchars($event['location']) : 'TBD'; ?>
                            </td>

                            <td>
                                <?php
                                    if (!empty($event['roles'])) {
                                        $roles = explode(', ', $event['roles']);
                                        foreach ($roles as $role) {
                                            echo '<span class="role-pill">' . htmlspecialchars($role) . '</span>';
                                        }
                                    } else {
                                        echo 'No role listed';
                                    }
                                ?>
                            </td>

                            <td>
                                <span class="status-badge">
                                    Signed Up
                                </span>
                            </td>

                            <td class="actions">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                    <button type="submit" class="cancel-btn" onclick="return confirm('Are you sure you want to cancel this event?');">
                                        Cancel
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                            <?php else: ?>
                            <tr class="empty-state-row">
                                <td colspan="7">No recent signed-up events found.</td>
                            </tr>
                    <?php endif; ?>
            </tbody>
                
            </table>
        </div>

    </div>
</div>
<?php include 'footer.php'; ?>

</body>
</html>