<?php
    session_start();
    // Admin
    $admin_id = $_SESSION['_id'] ?? null;
    // Target user
    $target_id = $_GET['target_id'] ?? null;

    if (!$target_id) {
        die("No user specified for report.");
    }
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Under Development</title>
        <style>
            body { 
                font-family: system-ui, sans-serif; 
                display: flex; 
                height: 75vh; 
                align-items: center; 
                justify-content: center; 
                text-align: center;
                color: #444;
            }
        </style>
    </head>
    <body>
        <div>
            <h1>processServiceLetterReport.php is still being developed.</h1>

            <p><strong>Admin ID:</strong> <?php echo htmlspecialchars($admin_id); ?></p>
            <p><strong>Target User ID:</strong> <?php echo htmlspecialchars($target_id); ?></p>
        </div>
    </body>
</html>
