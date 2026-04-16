<?php
session_start();
// Added
ini_set("display_errors", 1);
error_reporting(E_ALL);
/*<!-- Brooke did this page -->
<!-- It is the user hub for the admins -->*/
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
include_once('database/dbinfo.php'); 
$con=connect(); 

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}
require_once('database/dbPersons.php');

// Handle AJAX archive requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_id']) && !isset($_POST['selected_users'])) {
    toggleArchiveStatus($_POST['archive_id']);
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

// To be able to search and also to navigate through the pages
$search = $_GET['search'] ?? '';
// for attribute grouping
$search_by = $_GET['search_by'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// Event date filter params
$event_date = $_GET['event_date'] ?? '';
$event_id   = $_GET['event_id']   ?? '';

// Get the number of users that fit the query for pagination
$total_users = getUserCount($search, $search_by, $status, $event_id);
$total_pages = max(1, ceil($total_users / $per_page));

$users = getUsersForViewPage($search, $per_page, $offset, $search_by, $status, $event_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once('database/dbPersons.php'); ?>
    <title>Love Thy Neighbor | View Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="layoutInfo.css">



</head>

<body>
<?php include('header.php'); ?>
<div class="page">

    <!-- Main -->
    <div class="main">

        <div class="page-header">
            <h1>Users</h1>
            <a href="VolunteerRegister.php" class="add-btn">+ Add User</a>
            <!-- <a href="deleteUserSearch.php" class="add-btn">- Delete User</a> -->
        </div>

        <!-- Status Filters + Attribute Selection -->
        <div class="filter-card">
            <form class="filter-form" method="GET" action="viewOverallUsersKG.php">                
                <select name="search_by">
                    <option value="all" <?php echo ($search_by === 'all' ? 'selected' : ''); ?>>All</option>
                    <option value="name" <?php echo ($search_by === 'name' ? 'selected' : ''); ?>>Name</option>
                    <option value="username" <?php echo ($search_by === 'username' ? 'selected' : ''); ?>>Username</option>
                    <option value="email" <?php echo ($search_by === 'email' ? 'selected' : ''); ?>>Email</option>
                    <option value="phone" <?php echo ($search_by === 'phone' ? 'selected' : ''); ?>>Phone</option>
                </select>

                <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">

                <select name="status">
                    <option value="all" <?php echo ($status === 'all' ? 'selected' : ''); ?>>All</option>
                    <option value="active" <?php echo ($status === 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="archived" <?php echo ($status === 'archived' ? 'selected' : ''); ?>>Archived</option>
                </select>

                <input type="hidden" name="event_date" value="<?php echo htmlspecialchars($event_date); ?>">
                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">

                <button type="submit">Filter</button>
            </form>

            <!-- Date/Event Filtering -->
            <form class="filter-form event-filter-form" method="GET" action="viewOverallUsersKG.php" id="date-event-form">
                <div class="event-filter-inner">
                    <input 
                        type="date" 
                        name="event_date" 
                        id="event_date"
                        value="<?php echo htmlspecialchars($_GET['event_date'] ?? ''); ?>"
                        onchange="this.form.submit()"
                    >

                    <?php
                    if (!empty($_GET['event_date'])) {
                        $event_date = $_GET['event_date'];
                        $events_on_date = getEventsByDate($con, $event_date);
                        if (!empty($events_on_date)): ?>
                            <select name="event_id" onchange="this.form.submit()">
                                <option value="">— Select an Event —</option>
                                <?php foreach ($events_on_date as $event): ?>
                                    <option value="<?php echo htmlspecialchars($event['id']); ?>"
                                        <?php echo (($_GET['event_id'] ?? '') == $event['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <span class="no-events-msg">No events found.</span>
                        <?php endif;
                    }
                    ?>
                </div>
                <?php if (!empty($_GET['event_date'])): ?>
                    <a href="viewOverallUsersKG.php" class="add-btn" style="margin-left:8px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div id="hidden-selected-users"></div>

        <form action="composeEmail.php" method="POST">
            <div class="selection-bar">
    <div class="selection-info" id="selection-count">
        0 users selected
    </div>
    <div class="selection-actions">
        <button type="submit" name="email_mode" value="all" class="email-btn">
        <i class="fas fa-users"></i>
        Select All Users
        </button>
    </div>

    <div class="selection-actions">
        <button type="submit" class="email-btn">
            <i class="fas fa-envelope"></i>
            Email Selected
        </button>
    </div>

    <button type="button" onclick="clearSelections()" class="clear-btn">
        Clear Selection
    </button>
</div>
        <!-- Table -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['id'] !== 'vmsroot' && $user['id'] !== 'vmsroot2'): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_users[]" value="<?php echo htmlspecialchars($user['id']); ?>">
                            </td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone_number']); ?></td>
                            <td><?php echo htmlspecialchars($user['type']); ?></td>
                            <td>
                                <?php if (!empty($user['archived']) && $user['archived'] == 1): ?>
                                    <span class="badge archived">Archived</span>
                                <?php else: ?>
                                    <span class="badge active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="viewProfile.php?id=<?php echo urlencode($user['id']); ?>" class="view-btn">View</a>
                                <a href="editProfile.php?id=<?php echo urlencode($user['id']); ?>" class="edit-btn">Edit</a>
                                <!-- screw whoever told me "jUsT wIrE iT uP" when the button is INSIDE A FORM -->
                                <!-- i spent thirty minutes trying to figure out why the hidden form wouldn't work -->
                                <!-- you made me use JS you heathen -->
                                <?php if (!empty($user['archived']) && $user['archived'] == 1): ?>
                                    <a href="#" class="archive-btn" onclick="archiveUser(this, '<?php echo htmlspecialchars($user['id']); ?>'); return false;">Unarchive</a>
                                <?php else: ?>
                                    <a href="#" class="archive-btn" onclick="if(confirm('Are you sure you want to archive this user?')){archiveUser(this, '<?php echo htmlspecialchars($user['id']); ?>');}; return false;">Archive</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No users found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
                
            </table>
            </form>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const STORAGE_KEY = 'selectedUserIds';

    const userCheckboxes = document.querySelectorAll('input[name="selected_users[]"]');
    const selectAll = document.getElementById('select-all');
    const selectionCount = document.getElementById('selection-count');
    const emailForm = document.querySelector('form[action="composeEmail.php"]');
    const hiddenContainer = document.getElementById('hidden-selected-users');

    function getStoredSelections() {
        try {
            return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function saveStoredSelections(ids) {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
    }

    function updateSelectionCount() {
        const selectedIds = getStoredSelections();
        const count = selectedIds.length;

        if (!selectionCount) return;

        if (count === 0) {
            selectionCount.textContent = '0 users selected';
        } else if (count === 1) {
            selectionCount.textContent = '1 user selected';
        } else {
            selectionCount.textContent = count + ' users selected';
        }
    }

    function syncPageCheckboxesWithStorage() {
        const selectedIds = getStoredSelections();

        userCheckboxes.forEach(function (checkbox) {
            checkbox.checked = selectedIds.includes(checkbox.value);
        });

        if (selectAll && userCheckboxes.length > 0) {
            const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
            selectAll.checked = allChecked;
        }
    }

    function handleCheckboxChange(checkbox) {
        let selectedIds = getStoredSelections();

        if (checkbox.checked) {
            if (!selectedIds.includes(checkbox.value)) {
                selectedIds.push(checkbox.value);
            }
        } else {
            selectedIds = selectedIds.filter(id => id !== checkbox.value);
        }

        saveStoredSelections(selectedIds);
        syncPageCheckboxesWithStorage();
        updateSelectionCount();
    }

    userCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            handleCheckboxChange(checkbox);
        });
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            let selectedIds = getStoredSelections();

            userCheckboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;

                if (selectAll.checked) {
                    if (!selectedIds.includes(checkbox.value)) {
                        selectedIds.push(checkbox.value);
                    }
                } else {
                    selectedIds = selectedIds.filter(id => id !== checkbox.value);
                }
            });

            saveStoredSelections(selectedIds);
            syncPageCheckboxesWithStorage();
            updateSelectionCount();
        });
    }

    if (emailForm) {
        emailForm.addEventListener('submit', function () {
            const selectedIds = getStoredSelections();

            if (hiddenContainer) {
                hiddenContainer.innerHTML = '';

                selectedIds.forEach(function (id) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_users[]';
                    input.value = id;
                    hiddenContainer.appendChild(input);
                });
            }
        });
    }

    syncPageCheckboxesWithStorage();
    updateSelectionCount();
});
</script>
<script>
function clearSelections() {
    sessionStorage.removeItem('selectedUserIds');
    location.reload();
}

function archiveUser(button, userId) {
    const formData = new FormData();
    formData.append('archive_id', userId);
    
    fetch('viewOverallUsersKG.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'viewOverallUsersKG.php';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.href = 'viewOverallUsersKG.php';
    });
}
</script>
</body>
</html>