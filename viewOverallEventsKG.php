<?php
session_start();
// Added
ini_set("display_errors", 1);
error_reporting(E_ALL);
include_once("./database/dbinfo.php");

$loggedIn = false;
$accessLevel = 0;
$username = null;

if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $accessLevel = $_SESSION['access_level'];
    $username = $_SESSION['_id']; // Username is stored here
}

// Require admin privileges
if ($accessLevel < 1) {
    header('Location: login.php');
    die();
}
//End of added

// Create database connection HERE (so everything in this file can use it)
$con = connect();

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}
require_once('database/dbEvents.php');

// Get the events
$theEvents = get_all_events();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once('database/dbEvents.php'); ?>
    <title>Love Thy Neighbor | View Events</title>
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="layoutInfo.css">

</head>

<body>
<?php include('newheader.php'); ?>
<div class="page">

    <!-- Main -->
    <div class="main">

        <div class="page-header">
            <h1>View Events</h1>
            <a href="addEvent.php" class="add-btn">+ Create Event</a>
            <a href="calendar.php" class="add-btn">View Calendar</a>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form class="filter-form" method="GET" action="viewOverallEventsKG.php">
                <input type="text" name="search" placeholder="Search events..">
                
                <select name="status">
                    <option>Active</option>
                    <option>Archived</option>
                    <option>All</option>
                </select>

                <button type="submit">Filter</button>
            </form>
        </div>


        <!-- Table -->
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
                        <!-- add status later -Brooke -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($theEvents)): ?>
                    <?php foreach ($theEvents as $theEvent): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($theEvent->getName()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getStartDate()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getStartTime()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getEndTime()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getLocation()); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getCapacity()); ?></td>
                            <td class="actions">
                                <a href="event.php?id=<?php echo urlencode($theEvent->getID()); ?>" class="view-btn">View</a>
                                <a href="viewEventSignUps.php?id=<?php echo urlencode($theEvent->getID()); ?>" class="edit-btn">Attendance</a>
                                <a href="editEvent.php?id=<?php echo urlencode($theEvent->getID()); ?>" class="edit-btn">Edit</a>
                                <a href="#" class="archive-btn">Archive</a>
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