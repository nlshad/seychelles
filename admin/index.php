<?php
/**
 * Seychelles International Cargo LLC - Admin Dashboard
 */
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db_connection();

// Fetch Metrics
$total_quotes = $db->query("SELECT COUNT(*) FROM quotes")->fetchColumn();
$pending_quotes = $db->query("SELECT COUNT(*) FROM quotes WHERE status = 'Pending'")->fetchColumn();

$total_enquiries = $db->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$pending_enquiries = $db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Pending'")->fetchColumn();

$total_contacts = $db->query("SELECT COUNT(*) FROM contacts")->fetchColumn();

// Fetch Recent Quotes
$recent_quotes = $db->query("SELECT * FROM quotes ORDER BY id DESC LIMIT 5")->fetchAll();

// Fetch Recent Enquiries
$recent_enquiries = $db->query("SELECT * FROM enquiries ORDER BY id DESC LIMIT 5")->fetchAll();
?>

<!-- Statistics Overview -->
<div class="stats-grid">
  <div class="stat-card">
    <div>
      <div class="stat-label">Total Quote Requests</div>
      <div class="stat-val"><?php echo $total_quotes; ?></div>
      <?php if ($pending_quotes > 0): ?>
        <span class="badge badge-pending" style="margin-top:0.5rem;"><?php echo $pending_quotes; ?> Pending</span>
      <?php endif; ?>
    </div>
    <div class="stat-icon"><i class="fa-solid fa-calculator"></i></div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Online Enquiries</div>
      <div class="stat-val"><?php echo $total_enquiries; ?></div>
      <?php if ($pending_enquiries > 0): ?>
        <span class="badge badge-pending" style="margin-top:0.5rem;"><?php echo $pending_enquiries; ?> New</span>
      <?php endif; ?>
    </div>
    <div class="stat-icon" style="background:#ECFDF5; color:#10B981;"><i class="fa-solid fa-file-pen"></i></div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Contact Messages</div>
      <div class="stat-val"><?php echo $total_contacts; ?></div>
    </div>
    <div class="stat-icon" style="background:#FFF7ED; color:#F59E0B;"><i class="fa-solid fa-envelope"></i></div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Active Cargo Sectors</div>
      <div class="stat-val">9</div>
    </div>
    <div class="stat-icon" style="background:#F5F3FF; color:#8B5CF6;"><i class="fa-solid fa-globe"></i></div>
  </div>
</div>

<!-- Recent Quotes Panel -->
<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Recent Quote Requests</h3>
    <a href="quotes.php" class="btn-sm btn-admin-primary">View All Quotes</a>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Customer Name</th>
          <th>Phone / Contact</th>
          <th>Origin</th>
          <th>Destination</th>
          <th>Departure</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recent_quotes)): ?>
          <tr>
            <td colspan="8" style="text-align:center; color:var(--admin-muted); padding:2rem;">No quote requests recorded yet. Test by submitting a quote on the homepage!</td>
          </tr>
        <?php else: ?>
          <?php foreach ($recent_quotes as $q): ?>
            <tr>
              <td>#<?php echo $q['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($q['name']); ?></strong></td>
              <td><a href="tel:<?php echo htmlspecialchars($q['contact']); ?>"><?php echo htmlspecialchars($q['contact']); ?></a></td>
              <td><?php echo htmlspecialchars($q['origin']); ?></td>
              <td><span class="badge" style="background:#EFF6FF; color:#0066FF;"><?php echo htmlspecialchars($q['destination']); ?></span></td>
              <td><?php echo htmlspecialchars($q['departure_date'] ?: 'Flexible'); ?></td>
              <td>
                <span class="badge badge-<?php echo strtolower($q['status']); ?>">
                  <?php echo htmlspecialchars($q['status']); ?>
                </span>
              </td>
              <td><small style="color:var(--admin-muted);"><?php echo date('M d, Y H:i', strtotime($q['created_at'])); ?></small></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Recent Enquiries Panel -->
<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title"><i class="fa-solid fa-file-lines me-2 text-primary"></i>Recent Customer Enquiries</h3>
    <a href="enquiries.php" class="btn-sm btn-admin-primary">View All Enquiries</a>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Location</th>
          <th>Message Preview</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recent_enquiries)): ?>
          <tr>
            <td colspan="7" style="text-align:center; color:var(--admin-muted); padding:2rem;">No online enquiries recorded yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($recent_enquiries as $e): ?>
            <tr>
              <td>#<?php echo $e['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($e['firstname'] . ' ' . $e['lastname']); ?></strong></td>
              <td><a href="mailto:<?php echo htmlspecialchars($e['email']); ?>"><?php echo htmlspecialchars($e['email']); ?></a></td>
              <td><?php echo htmlspecialchars($e['phone']); ?></td>
              <td><?php echo htmlspecialchars($e['place']); ?></td>
              <td><?php echo htmlspecialchars(mb_strimwidth($e['message'], 0, 45, '...')); ?></td>
              <td><small style="color:var(--admin-muted);"><?php echo date('M d, Y', strtotime($e['created_at'])); ?></small></td>
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
