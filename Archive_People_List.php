<?php
require_once __DIR__ . '/database/dbinfo.php';
require_once __DIR__ . '/database/dbPersons.php';

$con = connect();
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

$archivePeople = [];
$query = "SELECT id, first_name, last_name, email, phone_number, `type`, archived
          FROM dbpersons
          WHERE archived = 0 OR archived IS NULL
          ORDER BY last_name ASC, first_name ASC";
$result = mysqli_query($con, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $archivePeople[] = $row;
    }
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Users</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background: #f5f5f5; }
        .no-data { padding: 24px; text-align: center; color: #555; }
    </style>
</head>
<body>
    <h1>Active Users</h1>
    <p>Showing unarchived users from the volunteer database.</p>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($archivePeople)): ?>
                <?php foreach ($archivePeople as $person): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($person['email']); ?></td>
                        <td><?php echo htmlspecialchars($person['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($person['type']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td class="no-data" colspan="4">No unarchived users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
    