<?php
/**
 * Seychelles International Cargo LLC - Admin Online Enquiries Manager
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
            $stmt = $db->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
        } elseif ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM enquiries WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: enquiries.php?msg=updated');
        exit;
    }
}

$stmt = $db->query("SELECT * FROM enquiries ORDER BY id DESC");
$enquiries = $stmt->fetchAll();
?>

<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title"><i class="fa-solid fa-file-pen me-2 text-primary"></i>Online Cargo Enquiries (<?php echo count($enquiries); ?>)</h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Customer Name</th>
          <th>Contact Info</th>
          <th>Destination / Place</th>
          <th style="width:40%;">Enquiry Message</th>
          <th>Status</th>
          <th>Date</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($enquiries)): ?>
          <tr>
            <td colspan="8" style="text-align:center; color:var(--admin-muted); padding:3rem;">No online enquiries recorded yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($enquiries as $e): ?>
            <tr>
              <td>#<?php echo $e['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($e['firstname'] . ' ' . $e['lastname']); ?></strong></td>
              <td>
                <div><i class="fa-regular fa-envelope me-1 text-primary"></i><a href="mailto:<?php echo htmlspecialchars($e['email']); ?>"><?php echo htmlspecialchars($e['email']); ?></a></div>
                <div style="margin-top:0.25rem;"><i class="fa-solid fa-phone me-1 text-primary"></i><a href="tel:<?php echo htmlspecialchars($e['phone']); ?>"><?php echo htmlspecialchars($e['phone']); ?></a></div>
              </td>
              <td><?php echo htmlspecialchars($e['place']); ?></td>
              <td style="line-height:1.6; font-size:0.9rem; background:#F8FAFC; border-radius:6px; padding:0.75rem;">
                <?php echo nl2br(htmlspecialchars($e['message'])); ?>
              </td>
              <td>
                <span class="badge badge-<?php echo strtolower($e['status']); ?>">
                  <?php echo htmlspecialchars($e['status']); ?>
                </span>
              </td>
              <td><small style="color:var(--admin-muted);"><?php echo date('M d, Y H:i', strtotime($e['created_at'])); ?></small></td>
              <td style="text-align:right;">
                <form action="enquiries.php" method="POST" style="display:inline-block;">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                  <select name="status" onchange="this.form.submit()" class="btn-sm btn-admin-secondary" style="outline:none; cursor:pointer;">
                    <option value="Pending" <?php echo $e['status']==='Pending'?'selected':''; ?>>Pending</option>
                    <option value="Contacted" <?php echo $e['status']==='Contacted'?'selected':''; ?>>Contacted</option>
                    <option value="Archived" <?php echo $e['status']==='Archived'?'selected':''; ?>>Archived</option>
                  </select>
                </form>

                <form action="enquiries.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this enquiry?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                  <button type="submit" class="btn-sm btn-admin-danger" title="Delete Enquiry">
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
