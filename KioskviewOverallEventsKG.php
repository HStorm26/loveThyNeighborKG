<!-- Brooke did this page -->
<!-- Josh modified this page -->
<!-- It is the event hub for the admins -->
<?php
session_start();

ini_set("display_errors", 1);
error_reporting(E_ALL);

$loggedIn = false;
$accessLevel = 0;
$username = null;

if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $accessLevel = $_SESSION['access_level'];
    $username = $_SESSION['_id'];
}

// Require admin privileges
if ($accessLevel < 1) {
    header('Location: login.php');
    die();
}

// Create database connection
$con = mysqli_connect("localhost", "root", "", "neighbordb");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

require_once('database/dbEvents.php');

// Get the events
$theEvents = get_all_events();

/*
 * Count how many unique volunteers are CURRENTLY checked in
 * for a given event. A volunteer counts against capacity only
 * while they have an open dbpersonhours row.
 */
function get_checked_in_count($con, $eventID) {
    $query = "SELECT COUNT(DISTINCT personID) AS checked_in_count
              FROM dbpersonhours
              WHERE eventID = ?
                AND start_time IS NOT NULL
                AND end_time IS NULL";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, "i", $eventID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $count = 0;
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $count = (int)$row['checked_in_count'];
    }

    mysqli_stmt_close($stmt);
    return $count;
}

/*
 * Remaining capacity = total event capacity - current open check-ins
 */
function get_remaining_capacity($con, $eventID, $totalCapacity) {
    $checkedIn = get_checked_in_count($con, $eventID);
    $remaining = (int)$totalCapacity - $checkedIn;
    return max(0, $remaining);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Love Thy Neighbor | View Events</title>
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="layoutInfo.css">
</head>

<body>
<?php include('newnewheader.php'); ?>
<div class="page">

    <div class="main">
        <div class="page-header">
            <h1>View Events</h1>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Location</th>
                        <th>Capacity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($theEvents)): ?>
                    <?php foreach ($theEvents as $theEvent): ?>
                        <?php
                            $eventID = $theEvent->getID();
                            $totalCapacity = (int)$theEvent->getCapacity();
                            $checkedInCount = get_checked_in_count($con, $eventID);
                            $remainingCapacity = get_remaining_capacity($con, $eventID, $totalCapacity);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($theEvent->getName()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getStartDate()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getStartTime()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getEndTime()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getLocation()); ?></td>
                            <td>
                                <?php echo htmlspecialchars((string)$remainingCapacity); ?>
                                / <?php echo htmlspecialchars((string)$totalCapacity); ?>
                                <br>
                                <small><?php echo htmlspecialchars((string)$checkedInCount); ?> currently checked in</small>
                            </td>
                            <td class="actions">
                                <a href="KioskViewCheckinOut.php?id=<?php echo urlencode($eventID); ?>" class="view-btn">
                                    Sign up
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No events found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>

<?php mysqli_close($con); ?>