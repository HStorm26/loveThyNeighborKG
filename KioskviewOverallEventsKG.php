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

// Create database connection here so everything in this file can use it
$con = mysqli_connect("localhost", "root", "", "neighbordb");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

require_once('database/dbEvents.php');

function countCurrentCheckedInForEvent($con, $eventID) {
    $query = "
        SELECT COUNT(DISTINCT personID) AS checked_in_count
        FROM dbpersonhours
        WHERE eventID = ?
          AND start_time IS NOT NULL
          AND end_time IS NULL
    ";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, "i", $eventID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $count = 0;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row && isset($row['checked_in_count'])) {
            $count = (int)$row['checked_in_count'];
        }
    }

    mysqli_stmt_close($stmt);
    return $count;
}

// Get only today's events
$today = date('Y-n-j');
$allEvents = get_all_events();
$theEvents = array();

foreach ($allEvents as $theEvent) {
    $rawEventDate = $theEvent->getStartDate();
    $eventTimestamp = strtotime($rawEventDate);

    if ($eventTimestamp !== false) {
        $normalizedEventDate = date('Y-n-j', $eventTimestamp);

        if ($normalizedEventDate === $today) {
            $theEvents[] = $theEvent;
        }
    }
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
                            $checkedInCount = countCurrentCheckedInForEvent($con, (int)$theEvent->getID());
                            $totalCapacity = (int)$theEvent->getCapacity();
                            $remainingCapacity = $totalCapacity - $checkedInCount;

                            if ($remainingCapacity < 0) {
                                $remainingCapacity = 0;
                            }

                            $capacityDisplay = $remainingCapacity . '/' . $totalCapacity;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($theEvent->getName()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getStartDate()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getStartTime()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getEndTime()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getLocation()); ?></td>
                            <td><?php echo htmlspecialchars($capacityDisplay); ?></td>
                            <td class="actions">
                                <a href="KioskViewCheckinOut.php?id=<?php echo urlencode($theEvent->getID()); ?>" class="view-btn">Sign up</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No events found for today.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>