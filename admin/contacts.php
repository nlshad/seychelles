<?php
/**
 * Seychelles International Cargo LLC - Admin Contact Submissions Manager
 */
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db_connection();

// Handle Actions (Status change / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = intval($_POST['id'] ?? 0);

    if ($id > 0) {
        if ($action === 'update_status') {
            $new_status = $_POST['status'] ?? 'Pending';
            $stmt = $db->prepare("UPDATE contacts SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
        } elseif ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: contacts.php?msg=updated');
        exit;
    }
}

$stmt = $db->query("SELECT * FROM contacts ORDER BY id DESC");
$contacts = $stmt->fetchAll();
?>

<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title"><i class="fa-solid fa-envelope me-2 text-primary"></i>Contact Page Messages (<?php echo count($contacts); ?>)</h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Sender Name</th>
          <th>Email Address</th>
          <th>Phone</th>
          <th style="width:40%;">Message Details</th>
          <th>Status</th>
          <th>Date</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($contacts)): ?>
          <tr>
            <td colspan="8" style="text-align:center; color:var(--admin-muted); padding:3rem;">No contact messages recorded yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($contacts as $c): ?>
            <tr>
              <td>#<?php echo $c['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($c['firstname'] . ' ' . $c['lastname']); ?></strong></td>
              <td><a href="mailto:<?php echo htmlspecialchars($c['email']); ?>"><?php echo htmlspecialchars($c['email']); ?></a></td>
              <td><a href="tel:<?php echo htmlspecialchars($c['phone']); ?>"><?php echo htmlspecialchars($c['phone']); ?></a></td>
              <td style="line-height:1.6; font-size:0.9rem; background:#F8FAFC; border-radius:6px; padding:0.75rem;">
                <?php echo nl2br(htmlspecialchars($c['message'])); ?>
              </td>
              <td>
                <span class="badge badge-<?php echo strtolower($c['status']); ?>">
                  <?php echo htmlspecialchars($c['status']); ?>
                </span>
              </td>
              <td><small style="color:var(--admin-muted);"><?php echo date('M d, Y H:i', strtotime($c['created_at'])); ?></small></td>
              <td style="text-align:right;">
                <form action="contacts.php" method="POST" style="display:inline-block;">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                  <select name="status" onchange="this.form.submit()" class="btn-sm btn-admin-secondary" style="outline:none; cursor:pointer;">
                    <option value="Pending" <?php echo $c['status']==='Pending'?'selected':''; ?>>Pending</option>
                    <option value="Contacted" <?php echo $c['status']==='Contacted'?'selected':''; ?>>Contacted</option>
                    <option value="Archived" <?php echo $c['status']==='Archived'?'selected':''; ?>>Archived</option>
                  </select>
                </form>

                <form action="contacts.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                  <button type="submit" class="btn-sm btn-admin-danger" title="Delete Message">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
