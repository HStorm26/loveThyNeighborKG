<?php
session_start();
include 'database/dbpersonhours.php';

if (!isset($_SESSION['_id'])) {
    die("Error: User not logged in.");
}

$person_id = $_POST['user_id'] ?? $_POST['userid'] ?? $_SESSION['_id'] ?? null;
$eventid   = isset($_POST['eventid']) ? (int)$_POST['eventid'] : 0;
$roleid    = isset($_POST['roleid']) ? (int)$_POST['roleid'] : 0;

if (!$person_id) {
    die("Error: Missing userid.");
}
if (!$eventid) {
    die("Error: Missing eventid.");
}
if (!$roleid) {
    die("Error: Missing roleid.");
}

function hasOpenCheckIn($eventid, $person_id, $roleid) {
    $con = connect();

    $query = "SELECT 1
              FROM dbpersonhours
              WHERE eventID = ?
                AND personID = ?
                AND roleID = ?
                AND start_time IS NOT NULL
                AND end_time IS NULL
              LIMIT 1";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    $stmt->bind_param("isi", $eventid, $person_id, $roleid);
    $stmt->execute();
    $result = $stmt->get_result();
    $open = $result && $result->num_rows > 0;

    $stmt->close();
    mysqli_close($con);
    return $open;
}

function forceCheckIn($eventid, $person_id, $roleid) {
    $con = connect();

    $query = "UPDATE dbpersonhours
              SET start_time = NOW(), end_time = NULL
              WHERE eventID = ? AND personID = ? AND roleID = ?";
    $stmt = $con->prepare($query);
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    $stmt->bind_param("isi", $eventid, $person_id, $roleid);
    $stmt->execute();
    $updated = $stmt->affected_rows;
    $stmt->close();

    if ($updated === 0) {
        $insert = "INSERT INTO dbpersonhours (personID, eventID, roleID, start_time, end_time)
                   VALUES (?, ?, ?, NOW(), NULL)";
        $stmt2 = $con->prepare($insert);
        if (!$stmt2) {
            mysqli_close($con);
            return false;
        }

        $stmt2->bind_param("sii", $person_id, $eventid, $roleid);
        $ok = $stmt2->execute();
        $stmt2->close();
        mysqli_close($con);
        return $ok;
    }

    mysqli_close($con);
    return true;
}

function forceCheckOut($eventid, $person_id, $roleid) {
    $con = connect();

    $query = "UPDATE dbpersonhours
              SET end_time = NOW()
              WHERE eventID = ?
                AND personID = ?
                AND roleID = ?
                AND start_time IS NOT NULL
                AND end_time IS NULL
              ORDER BY start_time DESC
              LIMIT 1";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        mysqli_close($con);
        return false;
    }

    $stmt->bind_param("isi", $eventid, $person_id, $roleid);
    $stmt->execute();
    $ok = $stmt->affected_rows > 0;

    $stmt->close();
    mysqli_close($con);
    return $ok;
}

function showSuccessMessage($message, $redirectUrl = null) {
    echo '<html><head><style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Quicksand, sans-serif;
        }
        .success-container { text-align: center; }
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #007bff;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: popIn 0.5s ease-in-out;
        }
        .checkmark:after {
            content: "✔";
            color: white;
            font-size: 40px;
            font-weight: bold;
        }
        .message {
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
            opacity: 0;
            animation: fadeIn 0.8s forwards 0.5s;
        }
        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style></head><body>';

    echo '<div class="success-container">
            <div class="checkmark"></div>
            <div class="message">' . htmlspecialchars($message) . '</div>
          </div>';

    if ($redirectUrl) {
        echo '<script>
            setTimeout(function() {
                window.location.href = "' . $redirectUrl . '";
            }, 1500);
        </script>';
    }

    echo '</body></html>';
    exit;
}

$existingOpenCheckIn = hasOpenCheckIn($eventid, $person_id, $roleid);

if ($existingOpenCheckIn) {
    $ok = forceCheckOut($eventid, $person_id, $roleid);
    $message = $ok ? "Checked out successfully!" : "Check-out failed.";
} else {
    $ok = forceCheckIn($eventid, $person_id, $roleid);
    $message = $ok ? "Checked in successfully!" : "Check-in failed.";
}

$redirectUrl = "KioskviewOverallEventsKG.php?id=" . urlencode((string)$eventid) .
               "&roleid=" . urlencode((string)$roleid) .
               "&q=" . urlencode($person_id) .
               "&msg=" . urlencode($message);

showSuccessMessage($message, $redirectUrl);
?>