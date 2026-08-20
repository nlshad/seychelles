<?php
/**
 * Seychelles International Cargo LLC - Admin Quote Requests Manager
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
            $stmt = $db->prepare("UPDATE quotes SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
        } elseif ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM quotes WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: quotes.php?msg=updated');
        exit;
    }
}

// Filter Query
$filter = $_GET['filter'] ?? 'all';
if ($filter === 'pending') {
    $stmt = $db->prepare("SELECT * FROM quotes WHERE status = 'Pending' ORDER BY id DESC");
    $stmt->execute();
} elseif ($filter === 'quoted') {
    $stmt = $db->prepare("SELECT * FROM quotes WHERE status = 'Quoted' ORDER BY id DESC");
    $stmt->execute();
} elseif ($filter === 'archived') {
    $stmt = $db->prepare("SELECT * FROM quotes WHERE status = 'Archived' ORDER BY id DESC");
    $stmt->execute();
} else {
    $stmt = $db->query("SELECT * FROM quotes ORDER BY id DESC");
}
$quotes = $stmt->fetchAll();
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
  <div style="display:flex; gap:0.5rem;">
    <a href="quotes.php?filter=all" class="btn-sm <?php echo $filter==='all'?'btn-admin-primary':'btn-admin-secondary'; ?>">All Quotes</a>
    <a href="quotes.php?filter=pending" class="btn-sm <?php echo $filter==='pending'?'btn-admin-primary':'btn-admin-secondary'; ?>">Pending</a>
    <a href="quotes.php?filter=quoted" class="btn-sm <?php echo $filter==='quoted'?'btn-admin-primary':'btn-admin-secondary'; ?>">Quoted</a>
    <a href="quotes.php?filter=archived" class="btn-sm <?php echo $filter==='archived'?'btn-admin-primary':'btn-admin-secondary'; ?>">Archived</a>
  </div>
</div>

<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title"><i class="fa-solid fa-calculator me-2 text-primary"></i>All Quote Requests (<?php echo count($quotes); ?>)</h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Customer Name</th>
          <th>Phone / WhatsApp</th>
          <th>City Origin</th>
          <th>Destination Sector</th>
          <th>Departure Date</th>
          <th>Status</th>
          <th>Date Submitted</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($quotes)): ?>
          <tr>
            <td colspan="9" style="text-align:center; color:var(--admin-muted); padding:3rem;">No quotes found for this filter.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($quotes as $q): ?>
            <tr>
              <td>#<?php echo $q['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($q['name']); ?></strong></td>
              <td>
                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $q['contact']); ?>" target="_blank" style="color:#10B981; font-weight:600; text-decoration:none;">
                  <i class="fa-brands fa-whatsapp me-1"></i><?php echo htmlspecialchars($q['contact']); ?>
                </a>
              </td>
              <td><?php echo htmlspecialchars($q['origin']); ?></td>
              <td>
                <span class="badge" style="background:#EFF6FF; color:#0066FF;">
                  <i class="fa-solid fa-location-dot me-1"></i><?php echo htmlspecialchars($q['destination']); ?>
                </span>
              </td>
              <td><?php echo htmlspecialchars($q['departure_date'] ?: 'Flexible'); ?></td>
              <td>
                <span class="badge badge-<?php echo strtolower($q['status']); ?>">
                  <?php echo htmlspecialchars($q['status']); ?>
                </span>
              </td>
              <td><small style="color:var(--admin-muted);"><?php echo date('M d, Y H:i', strtotime($q['created_at'])); ?></small></td>
              <td style="text-align:right;">
                <form action="quotes.php" method="POST" style="display:inline-block;">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                  <select name="status" onchange="this.form.submit()" class="btn-sm btn-admin-secondary" style="outline:none; cursor:pointer;">
                    <option value="Pending" <?php echo $q['status']==='Pending'?'selected':''; ?>>Pending</option>
                    <option value="Quoted" <?php echo $q['status']==='Quoted'?'selected':''; ?>>Quoted</option>
                    <option value="Archived" <?php echo $q['status']==='Archived'?'selected':''; ?>>Archived</option>
                  </select>
                </form>

                <form action="quotes.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this quote request?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                  <button type="submit" class="btn-sm btn-admin-danger" title="Delete Quote">
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
