<!-- Brooke did this page -->
<!-- It is the user hub for the admins -->
<?php
session_start();
// Added
ini_set("display_errors", 1);
error_reporting(E_ALL);

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

// To be able to search and also to navigate through the pages
$search = $_GET['search'] ?? '';
// for attribute grouping
$search_by = $_GET['search_by'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// Get the number of users that fit the query for pagination
$total_users = getUserCount($search, $search_by, $status);
$total_pages = max(1, ceil($total_users / $per_page));

$users = getUsersForViewPage($search, $per_page, $offset, $search_by, $status);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once('database/dbPersons.php'); ?>
    <title>Love Thy Neighbor | View Users</title>
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
            <a href="deleteUserSearch.php" class="add-btn">- Delete User</a>
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

                <button type="submit">Filter</button>
            </form>

            <!-- Date Filtering -->
            <!-- <form class="filter-form" method="GET" action="viewOverallUsersKG.php">
                <input type="text" name="search" placeholder="Search users...">
                
                <select name="status">
                    <option>All</option>
                    <option>Active</option>
                    <option>Archived</option>
                </select>

                <button type="submit">Filter</button>
            </form> -->
        </div>

        <!-- Table -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
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
                                <a href="#" class="archive-btn">Archive</a> <!-- When you wire this up, make sure it goes through 
                                                                            a POST request with a CSRF token, not a simple GET ?archive=id. 
                                                                            Otherwise anyone can trick an admin into archiving users 
                                                                            via a crafted link.-->
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
        </div>

        <div class="pagination-container">
            <div class="pagination">
                <!-- previous button -->
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>" class="page-btn">Previous</a>
                <?php endif; ?>

                <?php
                $window = 2;
                // ALWAYS show first page
                ?>
                <a href="?page=1&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>"
                class="page-btn <?php echo ($page == 1) ? 'active' : ''; ?>">1</a>

                <?php
                // LEFT ELLIPSIS RAH
                if ($page > $window + 2): ?>
                    <span class="page-btn">...</span>
                <?php endif; ?>

                <?php
                // middle pages
                $start = max(2, $page - $window);
                $end = min($total_pages - 1, $page + $window);

                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>"
                    class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php
                // RIGHT ELLIPSIS RAH
                if ($page < $total_pages - ($window + 1)): ?>
                    <span class="page-btn">...</span>
                <?php endif; ?>

                <?php
                // ALWAYS show last page [ assuming if more than 1 page]
                if ($total_pages > 1): ?>
                    <a href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>"
                    class="page-btn <?php echo ($page == $total_pages) ? 'active' : ''; ?>">
                    <?php echo $total_pages; ?>
                    </a>
                <?php endif; ?>

                <!-- next button -->
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo $search_by; ?>&status=<?php echo $status; ?>" class="page-btn">Next</a>
                <?php endif; ?>   
            </div>
        </div>
    </div>
</div>

</body>
</html>