<?php
session_cache_expire(30);
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);
date_default_timezone_set("America/New_York");

// Ensure admin authentication
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 2) {
    header('Location: login.php');
    die();
}

// Get current fiscal year
$currentMonth = date("m");
$currentYear = date("Y");
$fiscalYearStart = ($currentMonth >= 10) ? $currentYear : $currentYear - 1;
$fiscalYearEnd = $fiscalYearStart + 1;




    $sdate = null;
    if (isset($_GET['sdate'])) {
        $sdate = $_GET['sdate'];
        $datePattern = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
        $timeStamp = strtotime($sdate);
        if (!preg_match($datePattern, $sdate) || !$timeStamp) {
            header('Location: calendar.php');
            die();
        }
    }
    $edate = null;
    if (isset($_GET['edate'])) {
        $edate = $_GET['edate'];
        $datePattern = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
        $timeStamp = strtotime($edate);
        if (!preg_match($datePattern, $edate) || !$timeStamp) {
            header('Location: calendar.php');
            die();
        }
    }
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Total Hours By Role Report | Love Thy Neighbor Community Food Pantry</title>
    <!--<script src="js/data-filters.js" defer></script>-->
    <!-- <link href="css/base.css" rel="stylesheet"> -->
     <link rel="stylesheet" href="layoutInfo.css">
    <link rel="stylesheet" href="header.css">
    <?php require_once('header.php'); ?>
</head>
<body>
    <?php require_once('database/dbEvents.php');?>
    <?php require_once('database/dbPersons.php');?>
    <div class="page-wrapper">
        <div class="info-card">
        <!-- Title and Info -->
            <div class="info-header">
                <h1>Generate Total Hours By Role Report</h1>
                <p>Use this tool to see the hours spent in each role for a given time range<p>
            </div>

                <form method="POST" action="processTotalHours.php" class="info-form">
                    <!-- time --->
                        
                    <div style="margin-bottom: 1.5rem; margin-top: 1.5rem;">
                        <label for="name">* Start Date </label>
                        <input type="date" id="sdate" name="sdate" <?php if ($sdate) echo 'value="' . $sdate . '"'; ?>  required>
                    </div>

                    <div style="margin-bottom: 1.5rem; margin-top: 1.5rem;">
                        <label for="name">* End Date </label>
                        <input type="date" id="edate" name="edate" <?php if ($edate) echo 'value="' . $edate . '"'; ?>  required>
                    </div>
                    <!-- Format -->
                    <div style="margin-bottom: 1.5rem; margin-top: 1.5rem;">
                        <label for="format" style="font-weight: 600;">File Format</label>
                        <select name="format" id="format">
                            <option value="excel">Excel (.xls)</option>
                            <option value="csv">CSV (.csv)</option>
                        </select>
                    </div>

                    <div class="email-actions">
                        <input type="hidden" value="<?php echo $_SESSION['_id']; ?>" name="admin" id="admin">
                        <input type="hidden" value="<?php echo date("d-M-Y H:i:s e") ?>" name="time" id="time">
                        <button type="submit" name="action" value="send" class="submit-btn">Generate Report</button>
                    </div>
                </form>

            <!-- Return Button -->
            </div>
            <!-- <div style="text-align: center; margin-top: 2rem;">
                <a href="index.php" class="button" style="display: inline-block; text-decoration: none; width: 41%;">Return to Dashboard</a>
            </div> -->
        </div>
    </div>

    </main>
    <?php include 'footer.php'; ?>

   
</body>
</html>

