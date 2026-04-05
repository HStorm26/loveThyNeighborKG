<?php
session_cache_expire(30);
session_start();

include 'database/dbPersons.php';
include 'database/dbpersonhours.php';

$loggedIn = false;
$accessLevel = 0;
$userID = null;

$eventid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$roleid  = isset($_GET['roleid']) ? (int)$_GET['roleid'] : 1;

if (isset($_SESSION['_id'])) {
    $loggedIn = true;
    $accessLevel = $_SESSION['access_level'] ?? 0;
    $userID = $_SESSION['_id'];
}

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 2) {
    header('Location: KioskLogin.php');
    die();
}

if ($eventid <= 0) {
    die("Error: Missing event id.");
}

function hasOpenCheckInStatus($eventid, $personid, $roleid) {
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

    $stmt->bind_param("isi", $eventid, $personid, $roleid);
    $stmt->execute();
    $result = $stmt->get_result();
    $isOpen = $result && $result->num_rows > 0;

    $stmt->close();
    mysqli_close($con);
    return $isOpen;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
    header('Content-Type: application/json');

    $query = trim($_GET['query'] ?? '');
    $ajaxEventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $ajaxRoleId  = isset($_GET['roleid']) ? (int)$_GET['roleid'] : 1;

    if ($query === '' || $ajaxEventId <= 0) {
        echo json_encode([]);
        exit;
    }

    $usernames = searchUsers($query);
    $payload = [];

    foreach ($usernames as $username) {
        $payload[] = [
            'username' => $username,
            'open_checkin' => hasOpenCheckInStatus($ajaxEventId, $username, $ajaxRoleId)
        ];
    }

    echo json_encode($payload);
    exit;
}

$flashMessage = $_GET['msg'] ?? '';
$initialQuery = $_GET['q'] ?? '';

include 'infoBox.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Volunteer Check-In</title>
    <link href="css/normal_tw.css" rel="stylesheet">
</head>
<body>

<header class="hero-header">
    <div class="center-header">
        <h1>Volunteer Check-In</h1>
    </div>
</header>

<main>
    <div class="main-content-box w-[80%] p-8">
        <div class="text-center mb-8">
            <h2>Find Your Account to Check In/Out</h2>
            <p class="sub-text">Start typing your username below.</p>
        </div>

        <?php if ($flashMessage !== ''): ?>
            <div style="margin-bottom: 16px; padding: 12px; border-radius: 8px; background: #eaf6ea; color: #1f5f1f; font-weight: 600;">
                <?php echo htmlspecialchars($flashMessage); ?>
            </div>
        <?php endif; ?>

        <div class="space-y-6">
            <input
                type="text"
                id="search-box"
                placeholder="Search by Username..."
                class="form-input w-full"
                value="<?php echo htmlspecialchars($initialQuery); ?>"
            >

            <div class="overflow-x-auto">
                <table class="w-full" id="results-table">
                    <thead class="bg-[#C9AB81] text-black">
                        <tr>
                            <th class="text-left p-2">Username</th>
                            <th class="text-left p-2">Status</th>
                            <th class="text-left p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody id="search-results"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="blue-div"></div>
        <p class="info-text">
            Use this tool to find your volunteer account and securely check in or out.
        </p>
    </div>

    <div class="info-section">
        <p class="text-center text-gray-700">
            Don’t have an account?
            <a id="create-account-link" href="QuickVolunteerRegister.php" class="text-[rgb(203,37,26)] font-semibold hover:underline">Sign Up Now</a>
        </p>
    </div>
</main>

<script>
document.addEventListener('click', function (e) {
    const link = e.target.closest('a');
    if (!link) return;

    // Let the create-account link work normally
    if (link.id === 'create-account-link') {
        return;
    }

    e.preventDefault();
    const userConfirmed = confirm('Only an admin should perform this action. Are you sure you want to continue?');
    if (userConfirmed) {
        window.location.href = 'logoutForm.php';
    }
});

window.addEventListener('popstate', function () {
    const userConfirmed = confirm('Only an admin should perform this action. Are you sure you want to continue?');
    if (userConfirmed) {
        window.location.href = 'logoutForm.php';
    } else {
        history.pushState(null, '', location.href);
    }
});

window.history.pushState(null, '', window.location.href);

function runSearch(query) {
    const resultsList = document.getElementById("search-results");

    if (query.length < 1) {
        resultsList.innerHTML = "";
        return;
    }

    fetch(`KioskviewCheckInOut.php?ajax=search&query=${encodeURIComponent(query)}&id=<?php echo $eventid; ?>&roleid=<?php echo $roleid; ?>`)
        .then(response => response.json())
        .then(data => {
            resultsList.innerHTML = "";

            data.forEach(item => {
                const username = item.username;
                const openCheckin = !!item.open_checkin;

                const row = document.createElement("tr");

                const usernameCell = document.createElement("td");
                usernameCell.className = "p-2";
                usernameCell.textContent = username;

                const statusCell = document.createElement("td");
                statusCell.className = "p-2";
                statusCell.textContent = openCheckin ? "Checked In" : "Not Checked In";

                const actionCell = document.createElement("td");
                actionCell.className = "p-2";

                const form = document.createElement("form");
                form.method = "POST";
                form.action = "processCheckIn.php";

                const userInput = document.createElement("input");
                userInput.type = "hidden";
                userInput.name = "user_id";
                userInput.value = username;

                const eventInput = document.createElement("input");
                eventInput.type = "hidden";
                eventInput.name = "eventid";
                eventInput.value = "<?php echo $eventid; ?>";

                const roleInput = document.createElement("input");
                roleInput.type = "hidden";
                roleInput.name = "roleid";
                roleInput.value = "<?php echo $roleid; ?>";

                const button = document.createElement("button");
                button.type = "submit";
                button.className = "blue-button";
                button.textContent = openCheckin ? "Check Out" : "Check In";

                form.appendChild(userInput);
                form.appendChild(eventInput);
                form.appendChild(roleInput);
                form.appendChild(button);

                actionCell.appendChild(form);
                row.appendChild(usernameCell);
                row.appendChild(statusCell);
                row.appendChild(actionCell);

                resultsList.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching results:', error));
}

document.getElementById("search-box").addEventListener("input", function () {
    runSearch(this.value.trim());
});

window.addEventListener("load", function () {
    const existingValue = document.getElementById("search-box").value.trim();
    if (existingValue.length > 0) {
        runSearch(existingValue);
    }
});
</script>

</body>
</html>