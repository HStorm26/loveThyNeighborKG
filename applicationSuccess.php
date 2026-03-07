<?php
    session_cache_expire(30);
    session_start();
    header("refresh:2;url=viewAllApplications.php");
?>

    <!DOCTYPE html>
    <html>
        <head>
            <?php require_once('universal.inc') ?>
            <title>View Application | Love Thy Neighbor Community Food Pantry</title>
        </head>
        <body>
            <?php require_once('header.php') ?>
            <h1>Application Updated!</h1>
        </body>
    </html>