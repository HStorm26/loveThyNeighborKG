<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_cache_expire(30);
session_start();

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 2) {
    header('Location: login.php');
    die();
}

require_once('database/dbPersons.php');
require_once('database/dbEvents.php');
require_once('database/dbpersonhours.php');

$format = $_POST['format'] ?? 'csv';



$reportData = [];
$reportData = calcTop10();
$time = time();




// CSV EXPORT
if ($format === 'csv') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=top10_report_{$time}.csv");
    header("Pragma: no-cache");
    header("Expires: 0");

    $output = fopen('php://output', 'w');
    fputcsv($output, ["top10_report_{$time}.csv"]);

    // Column Headers
    fputcsv($output, ["First Name", "Last Name", "Hours", "Minutes"]);

    // Data
    foreach ($reportData as $p) {
        fputcsv($output, [
           $p[0],
            $p[1],
            $p[2],
            $p[3]
        ]);
    }
    fclose($output);
    exit();
}

// EXCEL EXPORT
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=top10_report_{$time}.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<html><head><meta charset='UTF-8'></head><body>";
echo "<table border='1' style='border-collapse: collapse; font-family: Arial, sans-serif; text-align: center;'>";

// Report Title
echo "<tr><th colspan='4' style='font-size: 18px; background-color: #004488; color: white; padding: 10px;'>Top Ten Volunteers Report </th></tr>";

// Column Headers
echo "<tr>
        <th style='background-color: #88CCEE; padding: 5px;'>First Name</th>
        <th style='background-color: #AA4499; padding: 5px;'>Last Name</th>
        <th style='background-color: #DDCC77; padding: 5px;'>Hours</th>
        <th style='background-color: #88CCEE; padding: 5px;'>Minutes</th>
      </tr>";

// Data Rows
foreach ($reportData as $p) {
    echo "<tr>
            <td style='background-color: #EAEAEA; padding: 5px; text-align: center;'>$p[0]</td>
            <td style='padding: 5px;'>{$p[1]}</td>
            <td style='padding: 5px;'>{$p[2]}</td>
            <td style='padding: 5px;'>{$p[3]}</td>
          </tr>";
}

echo "</table>";
echo "</body></html>";
exit();

?>
