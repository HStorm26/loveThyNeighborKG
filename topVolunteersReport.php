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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Top Volunteer Report | Love Thy Neighbor Community Food Pantry</title>
    <!--<script src="js/data-filters.js" defer></script>-->
    <link href="css/base.css" rel="stylesheet">
    <?php require_once('header.php'); ?>
</head>
<body>
    <?php require_once('database/dbEvents.php');?>
    <?php require_once('database/dbPersons.php');?>

    <!-- Hero Section with Title -->
        <div class="center-header">
            <h1 style="color:black;">Generate Top Volunteers Report</h1>
        </div>
                <!-- Info Section -->
        <section class="section-box">
            <p style="margin-top: 1rem;text-align:center;">
                Use this tool to generate a report showing volunteers who participated during a selected date range, along with the number of events each volunteer participated in.
            </p>
        </section>

    <main>

        <div class="main-content-box">
            <!--<div class="text-center">
                <p style="font-size: 18px; color: #c2c2c2ff; margin-top: 0.5rem; margin-bottom: 0.5rem;">Fiscal Year: <?= $fiscalYearStart ?> - <?= $fiscalYearEnd ?></p>
            </div>-->

            <form method="POST" action="processTotalHours.php"><!--make processUniqueVolunteerReport.php?-->
                <!-- time -->
                <div style="margin-bottom: 1.5rem; margin-top: 1.5rem;">
                    
                    <div class="Start date">
                        <label for="name">* Start Date </label>
                        <input type="date" id="sdate" name="sdate" <?php if ($sdate) echo 'value="' . $sdate . '"'; ?>  required>
                        </div>
                        <div class="End date">
                        <label for="name">* End Date </label>
                        <input type="date" id="edate" name="edate" <?php if ($edate) echo 'value="' . $edate . '"'; ?> required>
                        </div>
                </div>
                 <!-- Format -->
                <div style="margin-bottom: 1.5rem; margin-top: 1.5rem;">
                    <label for="format" style="font-weight: 600;">File Format</label>
                    <select name="format" id="format">
                        <option value="excel">Excel (.xls)</option>
                        <option value="csv">CSV (.csv)</option>
                    </select>
                </div>

                <div style="text-align: center; margin-top: 2rem;">
                    <input type="hidden" value="<?php echo $_SESSION['_id']; ?>" name="admin" id="admin">
                    <input type="hidden" value="<?php echo date("d-M-Y H:i:s e") ?>" name="time" id="time">
                    <input type="submit" value="Generate Report" class="button generate-btn">
                </div>
            </form>

        <!-- Return Button -->
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="button" style="display: inline-block; text-decoration: none; width: 41%;">Return to Dashboard</a>
        </div>

    </main>

   
</body>
</html>

