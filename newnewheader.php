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
            <div class="date"><?php echo $currentDate; ?></div>
        </div>

        <!-- CENTER -->
        <div class="header-center">
            <img src="images/LoveThyNeighbor_logo1_NoBackground.png" class="logo">
            <h1 class="main-title"><?php echo $pageTitle; ?></h1>
            </div>
        </div>

        </div>

    </div>
</div>