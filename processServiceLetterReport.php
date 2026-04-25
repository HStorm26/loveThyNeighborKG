<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);
date_default_timezone_set("America/New_York");

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 2) {
    header('Location: login.php');
    die();
}

require_once('database/reports.php');

$target_id = $_GET['target_id'] ?? null;
$sdate = $_GET['sdate'] ?? '';
$edate = $_GET['edate'] ?? '';

if (!$target_id || !$sdate || !$edate) {
    die("Missing required data.");
}

// Data
[$firstName, $lastName] = getVolunteerNameById($target_id);
$fullName = trim($firstName . ' ' . $lastName);

[$totalMinutes, $volunteerDays] = getVolunteerServiceHoursForDateRange($target_id, $sdate, $edate);
$totalHoursDecimal = round($totalMinutes / 60, 1);

$roles = getVolunteerRolesForDateRange($target_id, $sdate, $edate);
$rolesText = !empty($roles) ? implode(' and ', $roles) : 'volunteer activities';

$formattedStartDate = date("F j, Y", strtotime($sdate));
$formattedEndDate = date("F j, Y", strtotime($edate));
$today = date("F j, Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Service Hours Letter</title>

<style>
body {
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #000;
    margin: 0;
}

.letter-page {
    width: 650px;
    margin: 40px auto;
}

.logo {
    display: block;
    width: 120px;
    margin: 0 auto 10px auto;
}

.contact-info {
    text-align: center;
    font-size: 12px;
    margin-bottom: 25px;
}

.date {
    margin-bottom: 20px;
}

p {
    line-height: 1.4;
    margin: 0 0 14px 0;
}

.signature-space {
    height: 80px; /* space to physically sign */
}

.line-below {
    width: 300px;
    border-bottom: 1px solid #000;
    margin-top: 30px;
}
</style>
</head>

<body>

<div class="letter-page">

    <!-- Your correct logo -->
    <img src="images/LoveThyNeighbor_logo1_NoBackground.png" class="logo">

    <div class="contact-info">
        <div>www.kg-ltn.org</div>
        <div>(540) 709-1130</div>
        <div>10250 Kings Hwy, King George, VA</div>
    </div>

    <div class="date"><strong><?php echo $today; ?></strong></div>

    <p><strong>To whom it may concern:</strong></p>

    <p>
        I am writing this letter to confirm that <strong><?php echo htmlspecialchars($fullName); ?></strong>
        completed <strong><?php echo $totalHoursDecimal; ?></strong> hours of volunteer work at Love Thy Neighbor Food Pantry
        during the period from <strong><?php echo $formattedStartDate; ?></strong> to
        <strong><?php echo $formattedEndDate; ?></strong>. They assisted with <?php echo htmlspecialchars($rolesText); ?>.
        They volunteered on <strong><?php echo $volunteerDays; ?></strong> day(s) during this time period.
    </p>

    <p>If you have any additional questions, please reach out.</p>

    <p>Sincerely,</p>

    <!-- Blank space only -->
    <div class="signature-space"></div>

    <div class="line-below"></div>
    <div class="line-below"></div>

</div>

</body>
</html>