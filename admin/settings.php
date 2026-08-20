<?php
/**
 * Seychelles International Cargo LLC - Admin Settings Page
 */
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db_connection();
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass     = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
            $error = 'Please fill in all password fields.';
        } elseif ($new_pass !== $confirm_pass) {
            $error = 'New password and confirmation password do not match.';
        } elseif (strlen($new_pass) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } else {
            $user_id = $_SESSION['admin_user_id'];
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($user && password_verify($current_pass, $user['password_hash'])) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $update = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $update->execute([$new_hash, $user_id]);
                $msg = 'Password changed successfully!';
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
}
?>

<div style="max-width: 650px;">
  <?php if (!empty($msg)): ?>
    <div style="background:#D1FAE5; border:1px solid #10B981; color:#065F46; padding:0.85rem 1.25rem; border-radius:8px; margin-bottom:1.5rem;">
      <i class="fa-solid fa-circle-check me-1"></i> <?php echo htmlspecialchars($msg); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div style="background:#FEE2E2; border:1px solid #EF4444; color:#991B1B; padding:0.85rem 1.25rem; border-radius:8px; margin-bottom:1.5rem;">
      <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <!-- Security & Password Card -->
  <div class="panel-card" style="padding: 2rem;">
    <h3 class="panel-title" style="margin-bottom:0.5rem;"><i class="fa-solid fa-lock me-2 text-primary"></i>Change Admin Password</h3>
    <p style="color:var(--admin-muted); font-size:0.9rem; margin-bottom:1.5rem;">Update your administrator account access credentials.</p>

    <form action="settings.php" method="POST">
      <input type="hidden" name="action" value="change_password">

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.5rem;">Current Password *</label>
        <input type="password" name="current_password" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.75rem; font-size:0.95rem; border:1px solid var(--admin-border);" required>
      </div>

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.5rem;">New Password *</label>
        <input type="password" name="new_password" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.75rem; font-size:0.95rem; border:1px solid var(--admin-border);" required>
      </div>

      <div style="margin-bottom:1.5rem;">
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.5rem;">Confirm New Password *</label>
        <input type="password" name="confirm_password" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.75rem; font-size:0.95rem; border:1px solid var(--admin-border);" required>
      </div>

      <button type="submit" class="btn-sm btn-admin-primary" style="padding:0.75rem 1.5rem; font-size:0.95rem;">
        <i class="fa-solid fa-key me-1"></i> Update Password
      </button>
    </form>
  </div>
</div>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
