<?php
    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();

    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    $isAdmin = false;
    if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
        header('Location: login.php');
        die();
    }
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        // 0 = not logged in, 1 = standard user, 2 = manager (Admin), 3 super admin (TBI)
        $accessLevel = $_SESSION['access_level'];
        $isAdmin = $accessLevel >= 2;
        $userID = $_SESSION['_id'];
    } else {
        header('Location: login.php');
        die();
    }
    if ($isAdmin && isset($_GET['id'])) {
        require_once('include/input-validation.php');
        $args = sanitize($_GET);
        $id = strtolower($args['id']);
    } else {
        $id = $userID;
    }
    require_once('database/dbPersons.php');

    $user = retrieve_person($id);
    // $verified_ids = get_verified_ids($user->get_id());

    if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_hours'])) {
        $con = connect();
        $newHours = floatval($_POST['new_hours']);
        $safeID = mysqli_real_escape_string($con, $id);
        $update = mysqli_query($con, "
            UPDATE dbpersons 
            SET total_hours_volunteered = $newHours 
            WHERE id = '$safeID'
        ");
        if ($update) {
            $user = retrieve_person($id);
            echo '
            <div id="success-message" style="position:fixed;top:1rem;left:50%;transform:translateX(-50%);z-index:9999;background:#166534;color:#fff;padding:1rem 2rem;border-radius:0.5rem;font-size:1.1rem;">
              Hours updated successfully!
            </div>
            ';
        } else {
            echo '<div style="position:fixed;top:1rem;left:50%;transform:translateX(-50%);z-index:9999;background:#991b1b;color:#fff;padding:1rem 2rem;border-radius:0.5rem;font-size:1.1rem;">Failed to update hours.</div>';
        }
    }

    $viewingOwnProfile = $id == $userID;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['url'])) {
            if (!update_profile_pic($id, $_POST['url'])) {
                header('Location: viewProfile.php?id='.$id.'&picsuccess=False');
            } else {
                header('Location: viewProfile.php?id='.$id.'&picsuccess=True');
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Love Thy Neighbor | Profile Page</title>
  <link rel="stylesheet" href="css/viewProfile.css">
</head>
<body>
  <?php
    require_once('header.php');
    require_once('include/output.php');
  ?>

<?php if ($id == 'vmsroot'): ?>
  <main class="view-profile-wrap">
    <div class="toast-error" style="position:static;transform:none;margin-bottom:1rem;">The root user does not have a profile.</div>
  </main>
  <?php die() ?>
<?php elseif (!$user): ?>
  <main class="view-profile-wrap">
    <div class="toast-error" style="position:static;transform:none;margin-bottom:1rem;">User does not exist.</div>
  </main>
  <?php die() ?>
<?php endif ?>

<?php if (isset($_GET['editSuccess'])): ?>
  <div class="toast-success">Profile updated successfully!</div>
<?php endif ?>
<?php if (isset($_GET['rscSuccess'])): ?>
  <div class="toast-success">User role/status updated successfully!</div>
<?php endif ?>

<main class="view-profile-wrap">

  <h2 class="view-profile-title">
    <?php if ($viewingOwnProfile): ?>
      My Profile
    <?php else: ?>
      Viewing <?php echo htmlspecialchars($user->get_first_name() . ' ' . $user->get_last_name()); ?>
    <?php endif ?>
  </h2>

  <!-- Top action buttons -->
  <div class="profile-actions">
    <a href="editProfile.php<?php if ($id != $userID) echo '?id=' . urlencode($id); ?>" class="btn-primary">Edit Profile</a>
    <a href="index.php" class="btn-secondary">Return to Dashboard</a>
  </div>

  <!-- Radio inputs must be siblings of .tab-card to use ~ selector -->
  <input class="tab-radio" type="radio" name="profile-tab" id="tab-personal" checked>
  <input class="tab-radio" type="radio" name="profile-tab" id="tab-contact">
  <input class="tab-radio" type="radio" name="profile-tab" id="tab-notifs">

  <div class="tab-card">
    <!-- Tab bar -->
    <div class="tab-bar">
      <label class="tab-label" for="tab-personal">Personal Information</label>
      <label class="tab-label" for="tab-contact">Contact Information</label>
      <label class="tab-label" for="tab-notifs">Email Preferences</label>
    </div>

    <!-- Personal Information panel -->
    <div class="tab-panel" id="panel-personal">
      <div class="field-row">
        <span class="field-label">Username</span>
        <span class="field-value"><?php echo htmlspecialchars($user->get_id()); ?></span>
      </div>
      <div class="field-row">
        <span class="field-label">Name</span>
        <span class="field-value"><?php echo htmlspecialchars($user->get_first_name() . ' ' . $user->get_last_name()); ?></span>
      </div>
      <div class="field-row">
        <span class="field-label">Date of Birth</span>
        <span class="field-value"><?php echo $user->get_birthday() ? date('m/d/Y', strtotime($user->get_birthday())) : 'N/A'; ?></span>
      </div>
      <div class="field-row">
        <span class="field-label">Address</span>
        <span class="field-value"><?php echo htmlspecialchars($user->get_street_address() . ', ' . $user->get_city() . ', ' . $user->get_state() . ' ' . $user->get_zip_code()); ?></span>
      </div>
      <div class="field-row">
        <span class="field-label">T-Shirt Size</span>
        <span class="field-value"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $user->get_t_shirt_size()))); ?></span>
      </div>
    </div>

    <!-- Contact Information panel -->
    <div class="tab-panel" id="panel-contact">
      <div class="field-row">
        <span class="field-label">Email</span>
        <span class="field-value">
          <a href="mailto:<?php echo htmlspecialchars($user->get_email()); ?>"><?php echo htmlspecialchars($user->get_email()); ?></a>
        </span>
      </div>
      <div class="field-row">
        <span class="field-label">Phone Number</span>
        <span class="field-value">
          <a href="tel:<?php echo htmlspecialchars($user->get_phone1()); ?>"><?php echo formatPhoneNumber($user->get_phone1()); ?></a>
        </span>
      </div>
      <div class="field-row">
        <span class="field-label">Emergency Contact Name</span>
        <span class="field-value">
          <?php echo $user->get_emergency_contact_first_name() ? htmlspecialchars($user->get_emergency_contact_first_name()) : 'N/A'; ?>
        </span>
      </div>
      <div class="field-row">
        <span class="field-label">Emergency Contact Relation</span>
        <span class="field-value">
          <?php echo $user->get_emergency_contact_relation() ? htmlspecialchars($user->get_emergency_contact_relation()) : 'N/A'; ?>
        </span>
      </div>
      <div class="field-row">
        <span class="field-label">Emergency Contact Phone</span>
        <span class="field-value">
          <?php if ($user->get_emergency_contact_phone()): ?>
            <a href="tel:<?php echo htmlspecialchars($user->get_emergency_contact_phone()); ?>"><?php echo formatPhoneNumber($user->get_emergency_contact_phone()); ?></a>
          <?php else: ?>
            N/A
          <?php endif ?>
        </span>
      </div>
    </div>

    <!-- Email Preferences panel -->
    <div class="tab-panel" id="panel-notifs">
      <div class="field-row">
        <span class="field-label">Email</span>
        <span class="field-value"><?php echo htmlspecialchars($user->get_email()); ?></span>
      </div>
      <div class="field-row">
        <span class="field-label">Receive Emails?</span>
        <span class="field-value"><?php echo $user->get_email_prefs() ? 'Yes' : 'No'; ?></span>
      </div>
    </div>
  </div>
</main>
</body>
</html>