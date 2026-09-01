<?php
/**
 * Seychelles International Cargo LLC - Admin Navigation Header
 */
require_once __DIR__ . '/auth.php';
require_admin_login();

$current_page = basename($_SERVER['PHP_SELF']);

// Fetch Pending Counts for Nav Badges
$admin_nav_db = get_db_connection();
$nav_pending_quotes = 0;
$nav_pending_enquiries = 0;
$nav_pending_contacts = 0;

try {
    $nav_pending_quotes    = $admin_nav_db->query("SELECT COUNT(*) FROM quotes WHERE status = 'Pending'")->fetchColumn() ?: 0;
    $nav_pending_enquiries = $admin_nav_db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Pending'")->fetchColumn() ?: 0;
    $nav_pending_contacts  = $admin_nav_db->query("SELECT COUNT(*) FROM contacts WHERE status = 'Pending'")->fetchColumn() ?: 0;
} catch (Exception $e) {
    // Graceful fallback
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Seychelles International Cargo LLC</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="admin-wrapper">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <a href="index.php" class="sidebar-brand">
        <img src="../images/logo.gif" alt="Seychelles International Cargo LLC" style="max-height:45px; background:#FFFFFF; padding:4px 10px; border-radius:8px;">
      </a>
    </div>

    <nav class="sidebar-nav">
      <a href="index.php" class="sidebar-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span>
      </a>

      <a href="quotes.php" class="sidebar-link <?php echo $current_page === 'quotes.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-calculator"></i> <span>Quote Requests</span>
        <?php if ($nav_pending_quotes > 0): ?>
          <span class="nav-badge-count nav-badge-orange" title="<?php echo $nav_pending_quotes; ?> Pending Quotes"><?php echo $nav_pending_quotes; ?></span>
        <?php endif; ?>
      </a>

      <a href="enquiries.php" class="sidebar-link <?php echo $current_page === 'enquiries.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-pen"></i> <span>Online Enquiries</span>
        <?php if ($nav_pending_enquiries > 0): ?>
          <span class="nav-badge-count nav-badge-orange" title="<?php echo $nav_pending_enquiries; ?> Pending Enquiries"><?php echo $nav_pending_enquiries; ?></span>
        <?php endif; ?>
      </a>

      <a href="contacts.php" class="sidebar-link <?php echo $current_page === 'contacts.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-envelope"></i> <span>Contact Messages</span>
        <?php if ($nav_pending_contacts > 0): ?>
          <span class="nav-badge-count nav-badge-orange" title="<?php echo $nav_pending_contacts; ?> Pending Messages"><?php echo $nav_pending_contacts; ?></span>
        <?php endif; ?>
      </a>

      <a href="cbm_calculator.php" class="sidebar-link <?php echo $current_page === 'cbm_calculator.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-cubes"></i> <span>CBM Calculator</span>
      </a>
      <a href="vessels.php" class="sidebar-link <?php echo $current_page === 'vessels.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-ship"></i> <span>Vessel Schedules</span>
      </a>
      <a href="settings.php" class="sidebar-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-gear"></i> <span>Settings</span>
      </a>
      
      <div style="margin-top:auto; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.08);">
        <a href="../index.html" target="_blank" class="sidebar-link">
          <i class="fa-solid fa-arrow-up-right-from-square"></i> View Website
        </a>
        <a href="logout.php" class="sidebar-link" style="color:#EF4444;">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
      </div>
    </nav>
  </aside>

  <!-- Main Content Wrapper -->
  <main class="admin-main">
    <header class="admin-header">
      <h2 class="admin-header-title">
        <?php 
          if ($current_page === 'index.php') echo 'Dashboard Overview';
          elseif ($current_page === 'quotes.php') echo 'Quote Requests Manager';
          elseif ($current_page === 'enquiries.php') echo 'Online Enquiries';
          elseif ($current_page === 'contacts.php') echo 'Contact Messages';
          elseif ($current_page === 'cbm_calculator.php') echo 'CBM & Volumetric Calculator';
          elseif ($current_page === 'vessels.php') echo 'Vessel Schedules Manager';
          elseif ($current_page === 'settings.php') echo 'Admin Settings';
        ?>
      </h2>

      <div class="admin-user-profile">
        <div class="user-avatar">
          <i class="fa-solid fa-user"></i>
        </div>
        <div>
          <strong style="display:block; font-size:0.9rem;"><?php echo htmlspecialchars(get_logged_admin_username()); ?></strong>
          <span style="font-size:0.75rem; color:var(--admin-muted);">Administrator</span>
        </div>
      </div>
    </header>

    <div class="admin-content">
