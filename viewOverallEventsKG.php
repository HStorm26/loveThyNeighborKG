<!-- Brooke did this page -->
<!-- It is the event hub for the admins -->
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



if(isset($_GET['archive'])){
    //using <a> kinda requires this funky lil redirect thing so if you reload the page a million times it doesnt spam the db
    $id = (int) $_GET['archive'];
    if(is_archived($id)){
        unarchive_event($id);
    }
    else{
        archive_event($id);
    }
    header("Location: ./viewOverallEventsKG.php");
    exit();
}
// To be able to search and also to navigate through the pages
$search = $_GET['search'] ?? '';
// for attribute grouping
$search_by = $_GET['search_by'] ?? 'all';

$event_date = $_GET['event_date'] ?? '';
$event_id = $_GET['event_id'] ?? '';
$status = $_GET['status'] ?? 'all';

$allowed_statuses = ['all', 'active', 'archived'];
if (!in_array($status, $allowed_statuses, true)) {
    $status = 'all';
}

if ($event_id !== '' && !ctype_digit($event_id)) {
    $event_id = '';
}
$per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));

$total_events = getEventCount($search);
$total_pages = max(1, ceil($total_events / $per_page));

if ($page > $total_pages) {
    $page = 1;
}

$offset = ($page - 1) * $per_page;

$theEvents = getEventsForViewPage($search, $per_page, $offset);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once('database/dbEvents.php'); ?>
    <title>Love Thy Neighbor | View Events</title>
    <link rel="stylesheet" href="layoutInfo.css">

</head>

<body>
<?php include('header.php'); ?>
<div class="page">

    <!-- Main -->
    <div class="main">

        <div class="page-header">
            <h1>View Events</h1>
            <a href="addEvent.php" class="add-btn">+ Create Event</a>
            <?php if(isset($_SESSION['toggleArchive'])){ echo '<p> hi!!! </p>';} ?>
            <a href="calendar.php" class="add-btn">View Calendar</a>
        </div>

        <div class="filter-card">
            <form class="filter-form" method="GET" action="viewOverallEventsKG.php">                
                <select name="search_by">
                    <option value="all" <?php echo ($search_by === 'all' ? 'selected' : ''); ?>>All</option>
                    <option value="name" <?php echo ($search_by === 'name' ? 'selected' : ''); ?>>Name</option>
                    <option value="username" <?php echo ($search_by === 'username' ? 'selected' : ''); ?>>Username</option>
                    <option value="email" <?php echo ($search_by === 'email' ? 'selected' : ''); ?>>Email</option>
                    <option value="phone" <?php echo ($search_by === 'phone' ? 'selected' : ''); ?>>Phone</option>
                </select>

                <input type="text" name="search" placeholder="Search ..." value="<?php echo htmlspecialchars($search); ?>">


                <button type="submit">Filter</button>
            </form>
        

        <!-- Date/Event Filtering -->
            <form class="filter-form event-filter-form" method="GET" action="viewOverallEventsKG.php" id="date-event-form">
                <div class="event-filter-inner">
                    <input 
                        type="date" 
                        name="event_date" 
                        id="event_date"
                        value="<?php echo htmlspecialchars($event_date); ?>"
                        onchange="this.form.submit()"
                    >

                    <?php
                    $events_on_date = [];
                    if ($event_date !== '') {
                        $events_on_date = getEventsByDate($con, $event_date, $status);
                    }

                    if ($event_date !== '') {
                        if (!empty($events_on_date)): ?>
                            <select name="event_id" onchange="this.form.submit()">
                                <option value="">— Select an Event —</option>
                                <?php foreach ($events_on_date as $event): ?>
                                    <option value="<?php echo htmlspecialchars((string)$event['id']); ?>"
                                        <?php echo ($event_id !== '' && $event_id == $event['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <span class="no-events-msg">No events found.</span>
                        <?php endif;
                    }
                    ?>

                    <select name="status" onchange="this.form.submit()">
                        <option value="all" <?php echo ($status === 'all') ? 'selected' : ''; ?>>All</option>
                        <option value="active" <?php echo ($status === 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="archived" <?php echo ($status === 'archived') ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>

                <?php if ($event_date !== '' || $event_id !== '' || $status !== 'all'): ?>
                    <a href="viewOverallEventsKG.php" class="add-btn" style="margin-left:8px;">Clear</a>
                <?php endif; ?>
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
                        <th>Status</th>
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
                            <td><?php echo htmlspecialchars($theEvent->getLocation() ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($theEvent->getCapacity()); ?></td>
                            <td>
                                <?php if ($theEvent->getArchived() == 1): ?>
                                    <span class="badge archived">Archived</span>
                                <?php else: ?>
                                    <span class="badge active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="event.php?id=<?php echo urlencode($theEvent->getID()); ?>" class="view-btn">View</a>
                                <a href="viewEventSignUps.php?id=<?php echo urlencode($theEvent->getID()); ?>" class="edit-btn">Attendance</a>
                                <a href="editEvent.php?id=<?php echo urlencode($theEvent->getID()); ?>" class="edit-btn">Edit</a>
                                <?php if (is_archived($theEvent->getID())): ?>
                                    <a href="./viewOverallEventsKG.php?archive=<?php echo $theEvent->getID(); ?>" class="archive-btn">Unarchive</a>
                                <?php else: ?>
                                    <a href="./viewOverallEventsKG.php?archive=<?php echo $theEvent->getID(); ?>" class="archive-btn">Archive</a>
                                <?php endif; ?> 
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

        <div class="pagination-container">
            <div class="pagination">
                <!-- previous button -->
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>&event_date=<?php echo urlencode($event_date); ?>&event_id=<?php echo urlencode($event_id); ?>" 
                    class="page-btn">Previous</a>
                <?php endif; ?>

                <!-- ALWAYS show first page -->
                <?php $window = 2; ?>
                    <a href="?page=1&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>&event_date=<?php echo urlencode($event_date); ?>&event_id=<?php echo urlencode($event_id); ?>"
                    class="page-btn <?php echo ($page == 1) ? 'active' : ''; ?>">1</a>

                <!-- LEFT ELLIPSIS RAH -->
                <?php if ($page > $window + 2): ?>
                    <span class="page-btn">...</span>
                <?php endif; ?>

                <!-- middle pages -->
                <?php 
                $start = max(2, $page - $window);
                $end = min($total_pages - 1, $page + $window);

                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>&event_date=<?php echo urlencode($event_date); ?>&event_id=<?php echo urlencode($event_id); ?>"
                    class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <!-- RIGHT ELLIPSIS RAH -->
                <?php if ($page < $total_pages - ($window + 1)): ?>
                    <span class="page-btn">...</span>
                <?php endif; ?>

                <!-- ALWAYS show last page [ assuming if more than 1 page] -->
                <?php if ($total_pages > 1): ?>
                    <a href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>&event_date=<?php echo urlencode($event_date); ?>&event_id=<?php echo urlencode($event_id); ?>"
                    class="page-btn <?php echo ($page == $total_pages) ? 'active' : ''; ?>">
                    <?php echo $total_pages; ?>
                    </a>
                <?php endif; ?>

                <!-- next button -->
                <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>&event_date=<?php echo urlencode($event_date); ?>&event_id=<?php echo urlencode($event_id); ?>" class="page-btn">Next</a>
                <?php endif; ?>   
            </div>
        </div>

    </div>

</div>
<?php include 'footer.php'; ?>
</body>
</html>