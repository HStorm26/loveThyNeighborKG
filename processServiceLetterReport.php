<?php
session_cache_expire(30);
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);
date_default_timezone_set("America/New_York");

// Admin
$admin_id = $_SESSION['_id'] ?? null;

// Target user + selected date range
$target_id = $_GET['target_id'] ?? null;
$sdate = $_GET['sdate'] ?? '';
$edate = $_GET['edate'] ?? '';

// Require login
if (!$admin_id) {
    header('Location: login.php');
    die();
}

if (!$target_id) {
    die("No user specified for report.");
}

if (empty($sdate) || empty($edate)) {
    die("Start date and end date are required.");
}

require_once('database/reports.php');

// Convert date range for report functions
$startDateTime = $sdate . " 00:00:00";
$endDateTime = date("Y-m-d H:i:s", strtotime($edate . " +1 day"));

// Get volunteer name
$nameParts = getVolunteerNameById($target_id);
$first_name = $nameParts[0] ?? '';
$last_name = $nameParts[1] ?? '';
$fullName = trim($first_name . ' ' . $last_name);

// Get service totals
$serviceData = getVolunteerServiceHoursForDateRange($target_id, $startDateTime, $endDateTime);
$total_minutes = (int)($serviceData[0] ?? 0);
$volunteer_days = (int)($serviceData[1] ?? 0);

// Get distinct roles
$roles = getVolunteerRolesForDateRange($target_id, $startDateTime, $endDateTime);

// Convert minutes to decimal hours
$total_hours_decimal = round($total_minutes / 60, 1);

// Format role list for sentence
$roleText = '';
$roleCount = count($roles);

if ($roleCount === 1) {
    $roleText = $roles[0];
} elseif ($roleCount === 2) {
    $roleText = $roles[0] . ' and ' . $roles[1];
} elseif ($roleCount > 2) {
    $lastRole = array_pop($roles);
    $roleText = implode(', ', $roles) . ', and ' . $lastRole;
}

// Pretty display dates
$startDisplay = date("F j, Y", strtotime($sdate));
$endDisplay = date("F j, Y", strtotime($edate));
$todayDisplay = date("F j, Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Letter | Love Thy Neighbor Community Food Pantry</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .report-page {
            width: 8.5in;
            min-height: 11in;
            margin: 0 auto;
            padding: 0.7in 0.7in 0.9in 0.7in;
            box-sizing: border-box;
            background: #fff;
        }

        .logo-wrap {
            text-align: center;
            margin-bottom: 0.25in;
        }

        .logo-wrap img {
            max-width: 180px;
            height: auto;
        }

        .contact-block {
            text-align: center;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 0.35in;
        }

        .letter-date {
            margin-bottom: 0.25in;
            font-size: 14px;
        }

        .letter-body {
            font-size: 15px;
            line-height: 1.8;
        }

        .letter-body p {
            margin: 0 0 0.2in 0;
        }

        .signature-block {
            margin-top: 0.8in;
            font-size: 15px;
        }

        .signature-line {
            margin-top: 0.5in;
            width: 280px;
            border-bottom: 1px solid #000;
        }

        .name-line,
        .position-line {
            margin-top: 0.35in;
            width: 280px;
            border-bottom: 1px solid #000;
            position: relative;
        }

        .line-row {
    display: flex;
    align-items: center;
    margin-top: 0.35in;
}

.line-label {
    width: 80px; /* space for text */
    font-size: 14px;
}

.line {
    flex: 1;
    border-bottom: 1px solid #000;
}

        .actions {
            width: 8.5in;
            margin: 1rem auto 2rem auto;
            text-align: center;
        }

        .actions a {
            display: inline-block;
            text-decoration: none;
            padding: 10px 18px;
            border: 1px solid #000;
            color: #000;
            background: #f5f5f5;
            margin: 0 8px;
        }

        @media print {
            .actions {
                display: none;
            }

            .report-page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <div class="report-page">
        <div class="logo-wrap">
            <img src="images/LoveThyNeighbor_logo1_NoBackground.png" alt="Love Thy Neighbor logo">
        </div>

        <div class="contact-block">
            www.kg-ltn.org<br>
            (540) 709-1130<br>
            10250 Kings Highway, King George, VA
        </div>

        <div class="letter-date">
            <?php echo htmlspecialchars($todayDisplay); ?>
        </div>

        <div class="letter-body">
            <p>To whom it may concern:</p>

            <p>
                I am writing this letter to confirm that
                <strong><?php echo htmlspecialchars($fullName); ?></strong>
                completed <strong><?php echo htmlspecialchars((string)$total_hours_decimal); ?> hours</strong>
                of volunteer work at Love Thy Neighbor Food Pantry during the period from
                <strong><?php echo htmlspecialchars($startDisplay); ?></strong>
                to
                <strong><?php echo htmlspecialchars($endDisplay); ?></strong>.
            </p>

            <p>
                <?php if (!empty($roleText)): ?>
                    They assisted with <?php echo htmlspecialchars($roleText); ?>.
                <?php else: ?>
                    They volunteered with Love Thy Neighbor Food Pantry during this period.
                <?php endif; ?>
            </p>

            <p>
                They volunteered on <strong><?php echo htmlspecialchars((string)$volunteer_days); ?></strong> day(s) during this time period.
            </p>

            <p>
                If you have any additional questions, please reach out.
            </p>

            <p>Sincerely,</p>
        </div>

        <div class="signature-block">
    <div class="signature-line"></div>

        <div class="line-row">
            <span class="line-label">Name:</span>
            <div class="line"></div>
        </div>

        <div class="line-row">
            <span class="line-label">Position:</span>
            <div class="line"></div>
        </div>
    </div>
    </div>

    <div class="actions">
        <a href="serviceLetterReport.php">Back to Report Options</a>
        <a href="index.php">Return to Dashboard</a>
    </div>

</body>
</html>
