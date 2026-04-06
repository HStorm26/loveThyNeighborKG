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
        
    include_once('database/dbPersons.php');
    include_once('domain/Person.php');
    // Get date?
    if (isset($_SESSION['_id'])) {
        $person = retrieve_person($_SESSION['_id']);
    }
    $notRoot = $person->get_id() != 'vmsroot';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="./css/dashboard.css" rel="stylesheet">
    <link rel="stylesheet" href="header.css">
        <link rel="stylesheet" href="css/dashboard.css">
    <title>Dashboard | Love Thy Neighbor Community Food Pantry Volunteer Management</title>
 
<!--BEGIN TEST, UPLOAD AND NOTIFICATIONS CHANGED-->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelector(".extra-info").style.maxHeight = "0px"; // Ensure proper initialization
        });
        function toggleInfo(event) {
            event.stopPropagation(); // Prevents triggering the main button click
            let info = event.target.nextElementSibling;
            let isVisible = info.style.maxHeight !== "0px";
            info.style.maxHeight = isVisible ? "0px" : "100px";
            event.target.innerText = isVisible ? "↓" : "↑";
        }
    </script>
<!--END TEST-->
</head>

<!-- ONLY SUPER ADMIN WILL SEE THIS -->    <!-- This is not true, admin and super admin can see it (2 = admin, 3 = super admin) ;( -Brooke -->
<?php if ($_SESSION['access_level'] >= 2): ?>
<body>
<?php require 'header.php';?>
  <main class="main-content">

    <section class="welcome-banner">
      <div>
        <h1>Welcome back, Admin</h1>
        <p>Here’s what’s happening in your volunteer program today.</p>
      </div>

      <div class="date-box">
        <i class="fa-solid fa-calendar-days"></i> <?php echo $currentDate; ?>
      </div>
    </section>

    <section class="card-grid">
      <div class="card soft-red">
        <i class="fa-solid fa-calendar-week icon-red"></i>
        <h3>Week-to-Date Hours</h3>
        <p>126</p>
      </div>

      <div class="card soft-yellow">
        <i class="fa-solid fa-calendar-days icon-yellow"></i>
        <h3>Month-to-Date Hours</h3>
        <p>524</p>
      </div>

      <div class="card soft-blue">
        <i class="fa-solid fa-calendar-check icon-blue"></i>
        <h3>Year-to-Date Hours</h3>
        <p>1,560</p>
      </div>

      <div class="card soft-green">
        <i class="fa-solid fa-user-clock icon-green"></i>
        <h3>Newly Inactive Volunteers</h3>
        <p>3</p>
      </div>
    </section>

    <section class="wide-card-grid">
      <div class="card soft-orange">
        <i class="fa-solid fa-users icon-orange"></i>
        <h3>Volunteer Count</h3>
        <p>148</p>
      </div>

      <div class="card soft-maroon">
        <i class="fa-solid fa-user-shield icon-maroon"></i>
        <h3>Admin Count</h3>
        <p>6</p>
      </div>
    </section>

    <section class="dashboard-layout">

      <div class="section-box soft-blue">
        <h2>Top 10 Volunteers</h2>
        <p class="muted">Ranked by total volunteer hours</p>

        <div class="leaderboard">
          <div class="leader-row gold">
            <span class="rank"><i class="fas fa-trophy trophy-icon"></i> 1</span>
            <span class="name">Emily Carter</span>
            <span class="hours">42 hrs</span>
          </div>

          <div class="leader-row silver">
            <span class="rank"><i class="fas fa-trophy trophy-icon"></i> 2</span>
            <span class="name">James Wilson</span>
            <span class="hours">38 hrs</span>
          </div>

          <div class="leader-row bronze">
            <span class="rank"><i class="fas fa-trophy trophy-icon"></i> 3</span>
            <span class="name">Sophia Lee</span>
            <span class="hours">34 hrs</span>
          </div>

          <div class="leader-row">
            <span class="rank">4</span>
            <span class="name">Michael Brown</span>
            <span class="hours">29 hrs</span>
          </div>

          <div class="leader-row">
            <span class="rank">5</span>
            <span class="name">Olivia Harris</span>
            <span class="hours">27 hrs</span>
          </div>

          <div class="leader-row">
            <span class="rank">6</span>
            <span class="name">John Hill</span>
            <span class="hours">25 hrs</span>
          </div>

          <div class="leader-row">
            <span class="rank">7</span>
            <span class="name">Ava Walker</span>
            <span class="hours">24 hrs</span>
          </div>

          <div class="leader-row">
            <span class="rank">8</span>
            <span class="name">Liam Scott</span>
            <span class="hours">22 hrs</span>
          </div>

          <div class="leader-row">
            <span class="rank">9</span>
            <span class="name">Chloe Young</span>
            <span class="hours">20 hrs</span>
          </div>

          <div class="leader-row">
            <span class="rank">10</span>
            <span class="name">Ethan King</span>
            <span class="hours">18 hrs</span>
          </div>
        </div>
      </div>

      <div class="right-column">
        <div class="section-box soft-maroon">
          <h2>Alerts</h2>

          <div class="alert-item">
            <strong>Food Lion Pickup event canceled due to weather</strong><br>
            <span class="status yellow">Canceled</span>
          </div>

          <div class="alert-item">
            <strong>Apple Distribution event is full</strong><br>
            <span class="status red">Capacity Reached</span>
          </div>

          <div class="alert-item">
            <strong>Bill Thomas became a new volunteer</strong><br>
            <span class="status green">Active</span>
          </div>
        </div>

        <div class="section-box soft-orange">
          <h2>Upcoming Events</h2>
          <div class="event-item">
            <strong>Food Pantry Distribution</strong>
            <p class="muted">May 1 • 9:00 AM - 1:00 PM</p>
            <span class="status green">Open</span>
          </div>

          <div class="event-item">
            <strong>Walmart Pickup</strong>
            <p class="muted">May 3 • 10:00 AM - 1:00 PM</p>
            <span class="status green">Open</span>
          </div>
          <div class="event-item">
            <strong>School Food Drive</strong>
            <p class="muted">May 6 • 9:00 AM - 1:00 PM</p>
            <span class="status green">Open</span>
          </div>

        </div>
      </div>

    </section>

  </main>
<?php include 'footer.php'; ?>
</body>
<?php endif ?>

<?php if ($_SESSION['access_level'] < 2): ?>

<body>
<?php require 'header.php'; ?>

<main class="main-content">

    <section class="welcome-banner">
        <div>
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['f_name'] ?? 'Volunteer'); ?>!</h1>
            <p>Here is a quick look at your hours, registered events, schedule, and upcoming opportunities.</p>
        </div>

        <div class="date-box">
            <?php echo date("F j, Y"); ?>
        </div>
    </section>

    <section class="wide-card-grid volunteer-top-grid">
        <div class="card soft-blue">
            <i class="fa-solid fa-clock icon-blue"></i>
            <h3>Total Volunteer Hours</h3>
            <p>42</p>
        </div>

        <div class="card soft-green">
            <i class="fa-solid fa-calendar-check icon-green"></i>
            <h3>Registered Events</h3>
            <p>3</p>
        </div>
    </section>

    <section class="volunteer-bottom-grid">

        <div class="section-box soft-blue">
            <h2>My Scheduled Events</h2>
            <p class="muted">Events you are already signed up for.</p>

            <div class="event-item">
                <strong>Food Pantry Distribution</strong><br>
                <span>April 10, 2026 • 9:00 AM – 12:00 PM</span><br>
                <span class="status green">Registered</span>
            </div>

            <div class="event-item">
                <strong>Clothing Closet Help</strong><br>
                <span>April 16, 2026 • 11:00 AM – 1:00 PM</span><br>
                <span class="status green">Registered</span>
            </div>

            <div class="event-item">
                <strong>Weekend Cleanup</strong><br>
                <span>April 20, 2026 • 8:30 AM – 10:30 AM</span><br>
                <span class="status green">Registered</span>
            </div>
        </div>

        <div class="section-box soft-yellow">
            <h2>Upcoming Events</h2>
            <p class="muted">Available events you can sign up for.</p>

            <div class="event-item">
                <strong>Donation Sorting</strong><br>
                <span>April 12, 2026 • 1:00 PM – 3:00 PM</span><br>
                <span class="status yellow">Open for Signup</span>
            </div>

            <div class="event-item">
                <strong>Community Meal Prep</strong><br>
                <span>April 15, 2026 • 10:00 AM – 1:00 PM</span><br>
                <span class="status yellow">Open for Signup</span>
            </div>

            <div class="event-item">
                <strong>Food Drive Support</strong><br>
                <span>April 18, 2026 • 9:00 AM – 11:00 AM</span><br>
                <span class="status yellow">Open for Signup</span>
            </div>
        </div>

        <div class="section-box soft-maroon">
            <h2>Alerts</h2>
            <p class="muted">Important reminders and updates.</p>

            <div class="alert-item">
                <strong>Reminder:</strong><br>
                Please arrive 10 minutes early for your next event.
            </div>

            <div class="alert-item">
                <strong>New Opportunity:</strong><br>
                A new pantry support shift was posted for next week.
            </div>

            <div class="alert-item">
                <strong>Check-In Notice:</strong><br>
                Remember to use the kiosk when you arrive.
            </div>
        </div>

    </section>

</main>

<?php include 'footer.php'; ?>
</body>

<?php endif; ?>

</html> 