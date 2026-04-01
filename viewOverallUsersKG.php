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
$con = mysqli_connect("localhost", "root", "", "neighbordb");

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}
require_once('database/dbPersons.php');

// To be able to search and also to navigate through the pages
$search = $_GET['search'] ?? '';
$per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

$users = getUsersForViewPage($search, $per_page, $offset);

// The number of users per page
$per_page = 10;

$page = $_GET['page'] ?? 1;
$page = (int)$page;

if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $per_page;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once('database/dbPersons.php'); ?>
    <title>Love Thy Neighbor | View Users</title>
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="layoutInfo.css">



</head>

<body>
<?php include('newheader.php'); ?>
<div class="page">

    <!-- Main -->
    <div class="main">

        <div class="page-header">
            <h1>Users</h1>
            <a href="VolunteerRegister.php" class="add-btn">+ Add User</a>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form class="filter-form" method="GET" action="viewOverallUsersKG.php">
                <input type="text" name="search" placeholder="Search users...">
                
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
                                <a href="#" class="archive-btn">Archive</a>
                            </td>
                        </tr>
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
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="page-btn">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <a href="?page=<?php echo $i; ?>"
                    class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <a href="?page=<?php echo $page + 1; ?>" class="page-btn">Next</a>
            </div>
        </div>
    </div>
</div>

   

</body>
</html>