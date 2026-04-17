<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
    if (isset($_SESSION['change-password'])) {
        header('Location: changePassword.php');
    } else {
        header('Location: login.php');
    }
    die();
}

require_once __DIR__ . '/database/dbinfo.php';
require_once __DIR__ . '/database/dbPersons.php';

$con = connect();
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

$currentDate = date('F j, Y');
$message = '';
$searchValue = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
$searchQuery = '';

if ($searchValue !== '') {
    $escapedSearch = mysqli_real_escape_string($con, $searchValue);
    $searchTerm = "%$escapedSearch%";
    $searchQuery = " AND (first_name LIKE '$searchTerm' OR last_name LIKE '$searchTerm' OR email LIKE '$searchTerm' OR id LIKE '$searchTerm')";
}

$perPage = 25;
$activePage = isset($_GET['active_page']) ? max(1, intval($_GET['active_page'])) : 1;
$archivedPage = isset($_GET['archived_page']) ? max(1, intval($_GET['archived_page'])) : 1;
$activeOffset = ($activePage - 1) * $perPage;
$archivedOffset = ($archivedPage - 1) * $perPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $archiveAction = isset($_POST['archive_action']) ? trim($_POST['archive_action']) : '';

    if ($archiveAction === 'archive_all') {
        $oneYearAgo = date('Y-m-d H:i:s', strtotime('-1 year'));

        $idsToArchive = [];
        $archiveListQuery = "
            SELECT id
            FROM dbpersons
            WHERE (status = 'Active' OR status IS NULL)
            $searchQuery
            AND id NOT IN (
                SELECT DISTINCT personID
                FROM dbpersonhours
                WHERE COALESCE(end_time, start_time) >= '$oneYearAgo'
            )
        ";

        $archiveListResult = mysqli_query($con, $archiveListQuery);

        if ($archiveListResult) {
            while ($row = mysqli_fetch_assoc($archiveListResult)) {
                $idsToArchive[] = $row['id'];
            }
            mysqli_free_result($archiveListResult);

            $archivedCount = 0;
            foreach ($idsToArchive as $personId) {
                if (archive_volunteer($personId, 'Inactive')) {
                    $archivedCount++;
                }
            }

            $message = $archivedCount > 0
                ? "Archived $archivedCount user(s) with no activity in the past year."
                : 'No active users matched the criteria.';
        } else {
            $message = 'Unable to archive users. Please try again.';
        }
    } elseif ($archiveAction === 'unarchive_all') {
        $idsToRestore = [];
        $restoreListQuery = "SELECT id FROM dbpersons WHERE status = 'Inactive'$searchQuery";
        $restoreListResult = mysqli_query($con, $restoreListQuery);

        if ($restoreListResult) {
            while ($row = mysqli_fetch_assoc($restoreListResult)) {
                $idsToRestore[] = $row['id'];
            }
            mysqli_free_result($restoreListResult);

            $restoredCount = 0;
            foreach ($idsToRestore as $personId) {
                if (archive_volunteer($personId, 'Active')) {
                    $restoredCount++;
                }
            }

            $message = $restoredCount > 0 ? "Unarchived $restoredCount user(s)." : 'No archived users matched the request.';
        } else {
            $message = 'Unable to unarchive users. Please try again.';
        }
    } elseif (isset($_POST['archive_id'])) {
        $archiveId = trim($_POST['archive_id']);
        $archiveAction = isset($_POST['archive_action']) ? trim($_POST['archive_action']) : '';

        if ($archiveId !== '') {
            if ($archiveAction === 'check_year') {
                $stmt = $con->prepare("SELECT MAX(COALESCE(end_time, start_time)) AS last_time FROM dbpersonhours WHERE personID = ?");
                if ($stmt) {
                    $stmt->bind_param('s', $archiveId);
                    $stmt->execute();
                    $stmt->bind_result($lastTime);
                    $stmt->fetch();
                    $stmt->close();

                    $oneYearAgo = date('Y-m-d H:i:s', strtotime('-1 year'));
                    if (empty($lastTime) || $lastTime < $oneYearAgo) {
                        if (archive_volunteer($archiveId, 'Inactive')) {
                            $message = 'User had no volunteer activity in the past year and was archived.';
                        } else {
                            $message = 'Unable to archive the user after checking volunteer history.';
                        }
                    } else {
                        $message = 'User has volunteered within the past year and was not archived.';
                    }
                } else {
                    $message = 'Unable to verify volunteer history. Please try again.';
                }
            } else {
                if (archive_volunteer($archiveId)) {
                    $message = 'User status switched successfully.';
                } else {
                    $message = 'Unable to update the user status. Please try again.';
                }
            }
        }
    }
}

$activeCount = 0;
$countQuery = "SELECT COUNT(*) AS cnt FROM dbpersons WHERE (status = 'Active' OR status IS NULL)$searchQuery";
$countResult = mysqli_query($con, $countQuery);
if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $activeCount = intval($countRow['cnt']);
    mysqli_free_result($countResult);
}

$activePageCount = max(1, ceil($activeCount / $perPage));
if ($activePage > $activePageCount) {
    $activePage = $activePageCount;
    $activeOffset = ($activePage - 1) * $perPage;
}

$archivePeople = [];
$query = "SELECT id, first_name, last_name, email, phone_number, `type`
          FROM dbpersons
          WHERE (status = 'Active' OR status IS NULL)$searchQuery
          ORDER BY last_name ASC, first_name ASC
          LIMIT $perPage OFFSET $activeOffset";
$result = mysqli_query($con, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $archivePeople[] = $row;
    }
    mysqli_free_result($result);
}

$inactiveCount = 0;
$countQuery = "SELECT COUNT(*) AS cnt FROM dbpersons WHERE status = 'Inactive'$searchQuery";
$countResult = mysqli_query($con, $countQuery);
if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $inactiveCount = intval($countRow['cnt']);
    mysqli_free_result($countResult);
}

$archivedPageCount = max(1, ceil($inactiveCount / $perPage));
if ($archivedPage > $archivedPageCount) {
    $archivedPage = $archivedPageCount;
    $archivedOffset = ($archivedPage - 1) * $perPage;
}

$archivedPeople = [];
$query = "SELECT id, first_name, last_name, email, phone_number, `type`
          FROM dbpersons
          WHERE status = 'Inactive'$searchQuery
          ORDER BY last_name ASC, first_name ASC
          LIMIT $perPage OFFSET $archivedOffset";
$result = mysqli_query($con, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $archivedPeople[] = $row;
    }
    mysqli_free_result($result);
}

$totalCount = $activeCount + $inactiveCount;

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive Portal | Love Thy Neighbor Community Food Pantry Volunteer Management</title>

    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="css/dashboard.css">

    <style>
        .archive-page .archive-section {
            margin-top: 24px;
        }

        .archive-page .table-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            padding: 24px;
            margin-bottom: 24px;
        }

        .archive-page .table-card h2 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .archive-page .table-card .muted {
            margin-bottom: 18px;
            color: #666;
        }

        .archive-page .message-box {
            margin: 16px 0 24px 0;
            padding: 14px 16px;
            border-radius: 14px;
            background: #e8f8e8;
            border: 1px solid #b8e2b8;
            color: #1a5d30;
            font-weight: 600;
        }

        .archive-page .table-wrap {
            overflow-x: auto;
        }

        .archive-page table {
            width: 100%;
            border-collapse: collapse;
        }

        .archive-page th,
        .archive-page td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid #e9e9e9;
        }

        .archive-page th {
            font-weight: 700;
            color: #333;
            background: #fafafa;
        }

        .archive-page tr:last-child td {
            border-bottom: none;
        }

        .archive-page .no-data {
            padding: 24px;
            text-align: center;
            color: #666;
        }

        .archive-page .action-btn {
            padding: 8px 14px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-family: inherit;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }

        .archive-page .action-btn:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        .archive-page .archive-page-action-form {
            min-width: 0;
            max-width: 100%;
        }

        .archive-page .action-btn {
            white-space: normal;
            word-break: break-word;
            min-width: 0;
            max-width: 100%;
            padding: 10px 16px;
        }

        .archive-page .archive-btn {
            background: #f8d7da;
            color: #842029;
        }

        .archive-page .unarchive-btn {
            background: #d1e7dd;
            color: #0f5132;
        }

        .archive-page .status-pill {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .archive-page .status-active {
            background: #e7f5ff;
            color: #0b5ed7;
        }

        .archive-page .status-inactive {
            background: #f1f3f5;
            color: #495057;
        }

        .archive-page .pagination {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .archive-page .pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid #ddd;
            background: #fff;
            color: #333;
            text-decoration: none;
            font-weight: 600;
        }

        .archive-page .search-section {
            margin-top: 28px;
            margin-bottom: 24px;
        }

        .archive-page .search-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .archive-page .search-form input[type="search"] {
            flex: 1 1 320px;
            min-width: 220px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid #d8d8d8;
            font-size: 1rem;
        }

        .archive-page .search-form button {
            padding: 12px 18px;
            border-radius: 14px;
            border: none;
            background: #0b5ed7;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .archive-page .search-form button:hover {
            background: #094bb5;
        }

        .archive-page .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-top: 28px;
        }

        .archive-page .card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 14px;
            min-height: 160px;
        }

        .archive-page .card h3 {
            margin: 0;
        }

        .archive-page .card p {
            margin: 0;
            font-size: 1.65rem;
            font-weight: 700;
        }

        .archive-page .card i {
            font-size: 1.5rem;
        }

        .archive-page .pagination a.active {
            background: #f0f0f0;
            border-color: #bbb;
        }

        .archive-page .pagination a:hover {
            background: #f8f8f8;
        }
    </style>
</head>

<body class="archive-page">
<?php require_once 'header.php'; ?>

<main class="main-content">

    <section class="welcome-banner">
        <div>
            <h1>Archive People Portal</h1>
            <p>Manage active and inactive users from the admin archive dashboard.</p>
        </div>

        <div class="date-box">
            <i class="fa-solid fa-calendar-days"></i> <?php echo htmlspecialchars($currentDate); ?>
        </div>
    </section>

    <section class="card-grid">
        <div class="card soft-blue">
            <i class="fa-solid fa-users icon-blue"></i>
            <h3>Total Users</h3>
            <p><?php echo htmlspecialchars((string)$totalCount); ?></p>
        </div>

        <div class="card soft-green">
            <i class="fa-solid fa-user-check icon-green"></i>
            <h3>Active Users</h3>
            <p><?php echo htmlspecialchars((string)$activeCount); ?></p>
        </div>

        <div class="card soft-yellow">
            <i class="fa-solid fa-user-clock icon-yellow"></i>
            <h3>Inactive Users</h3>
            <p><?php echo htmlspecialchars((string)$inactiveCount); ?></p>
        </div>
    </section>

    <?php if ($message): ?>
        <div class="message-box"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <section class="search-section">
        <form method="get" class="search-form">
            <label for="search" class="sr-only">Search users</label>
            <input id="search" type="search" name="search" value="<?php echo htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by name, email, or username">
            <button type="submit">Search</button>
        </form>
    </section>

    <section class="archive-section">
        <div class="table-card">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div>
                    <h2>Active Users</h2>
                    <p class="muted">Use this table to archive currently active volunteers and admins.</p>
                </div>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="archive-page-action-form" style="margin:0;">
                    <input type="hidden" name="archive_action" value="archive_all">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="action-btn archive-btn" onclick="return confirm('Archive all active users that have not volunteered in the past year?');">Archive All Users Who Haven't Volunteered in Over a Year</button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
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
                                    <td><span class="status-pill status-active">Active</span></td>
                                    <td>
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="margin:0;">
                                            <input type="hidden" name="archive_id" value="<?php echo htmlspecialchars($person['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="archive_action" value="check_year">
                                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button
                                                type="submit"
                                                class="action-btn archive-btn"
                                                onclick="return confirm('Check volunteer activity for the past year and archive if none is found?');">
                                                Archive
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="no-data" colspan="6">No active users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($activePageCount > 1): ?>
                <div class="pagination">
                    <?php if ($activePage > 1): ?>
                        <a href="?active_page=<?php echo $activePage - 1; ?>&archived_page=<?php echo $archivedPage; ?><?php echo $searchValue !== '' ? '&search=' . urlencode($searchValue) : ''; ?>">Prev</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $activePageCount; $i++): ?>
                        <a href="?active_page=<?php echo $i; ?>&archived_page=<?php echo $archivedPage; ?><?php echo $searchValue !== '' ? '&search=' . urlencode($searchValue) : ''; ?>" class="<?php echo $i === $activePage ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($activePage < $activePageCount): ?>
                        <a href="?active_page=<?php echo $activePage + 1; ?>&archived_page=<?php echo $archivedPage; ?><?php echo $searchValue !== '' ? '&search=' . urlencode($searchValue) : ''; ?>">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-card">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div>
                    <h2>Archived Users</h2>
                    <p class="muted">Use this table to restore users who are currently inactive.</p>
                </div>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="margin:0;">
                    <input type="hidden" name="archive_action" value="unarchive_all">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="action-btn unarchive-btn" onclick="return confirm('Unarchive all archived users?');">Unarchive All</button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($archivedPeople)): ?>
                            <?php foreach ($archivedPeople as $person): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($person['email']); ?></td>
                                    <td><?php echo htmlspecialchars($person['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars($person['type']); ?></td>
                                    <td><span class="status-pill status-inactive">Inactive</span></td>
                                    <td>
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" style="margin:0;">
                                            <input type="hidden" name="archive_id" value="<?php echo htmlspecialchars($person['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="archive_action" value="toggle">
                                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button
                                                type="submit"
                                                class="action-btn unarchive-btn"
                                                onclick="return confirm('Unarchive this user?');">
                                                Unarchive
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="no-data" colspan="6">No archived users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($archivedPageCount > 1): ?>
                <div class="pagination">
                    <?php if ($archivedPage > 1): ?>
                        <a href="?active_page=<?php echo $activePage; ?>&archived_page=<?php echo $archivedPage - 1; ?><?php echo $searchValue !== '' ? '&search=' . urlencode($searchValue) : ''; ?>">Prev</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $archivedPageCount; $i++): ?>
                        <a href="?active_page=<?php echo $activePage; ?>&archived_page=<?php echo $i; ?><?php echo $searchValue !== '' ? '&search=' . urlencode($searchValue) : ''; ?>" class="<?php echo $i === $archivedPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($archivedPage < $archivedPageCount): ?>
                        <a href="?active_page=<?php echo $activePage; ?>&archived_page=<?php echo $archivedPage + 1; ?><?php echo $searchValue !== '' ? '&search=' . urlencode($searchValue) : ''; ?>">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php include 'footer.php'; ?>
</body>
</html>