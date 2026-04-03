<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once('database/dbPersons.php');

$firstName = $_SESSION['f_name'] ?? '';
$lastName = $_SESSION['l_name'] ?? '';
//$userName = trim($firstName . ' ' . $lastName);
$userName = trim($firstName);

if ($userName === '') {
    $userName = 'Guest';
}

if (!isset($pageTitle)) {
    $pageTitle = "LOVE THY NEIGHBOR";
}

if (!isset($currentDate)) {
    $currentDate = date("F j, Y");
}

?>

<div class="header">
    <div class="header-container">

        <!-- LEFT -->
        <div class="header-left">
            <div class="calendar-icon">
                <a href="calendar.php">
                    <img src="images/view-calendar.svg" class="logo">
                </a>
            </div>
            <div class="date"><?php echo $currentDate; ?></div>
        </div>

        <!-- CENTER -->
        <div class="header-center">
            <img src="images/LoveThyNeighbor_logo1_NoBackground.png" class="logo">
            <h1 class="main-title"><?php echo $pageTitle; ?></h1>

            <div class="header-nav">
                <a href="index.php">Dashboard</a>
                <a href="viewOverallUsersKG.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'viewOverallUsersKG.php' ? 'active' : ''; ?>">User</a>
                <a href="viewOverallEventsKG.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'viewAllEvents.php' ? 'active' : ''; ?>">Event</a>
                <a href="createEmail.php">Email</a>
                <a href="viewAllReports.php">Report</a>
                <a href="inbox.php">Notification</a>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="header-right">
            <img src="images/user_icon.png" class="profile-icon">

            <div class="profile-name">
                <?php echo htmlspecialchars($userName); ?>
            </div>

            <div class="dropdown">
                <a href="viewProfile.php">View Profile</a>  
                <a href="editProfile.php">Edit Profile</a>  
                <a href="changePassword.php">Change Password</a>
                <a href="logout.php">Log Out</a>
            </div>
        </div>

    </div>
</div>