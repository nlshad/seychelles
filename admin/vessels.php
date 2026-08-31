<?php
/**
 * Seychelles International Cargo LLC - Admin Vessel Schedules Manager
 */
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db_connection();
$msg = '';
$error = '';

// Handle Create / Update / Edit / Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $destination = sanitize_input($_POST['destination'] ?? '');
        $etd_date    = sanitize_input($_POST['etd_date'] ?? '');
        $eta_date    = sanitize_input($_POST['eta_date'] ?? '');
        $cutoff_date = sanitize_input($_POST['cutoff_date'] ?? '');
        $bg_image    = sanitize_input($_POST['bg_image'] ?? '');
        $status      = sanitize_input($_POST['status'] ?? 'Booking Open');
        $vessel_name = $destination . ' Cargo Sailing';
        $voyage_no   = 'N/A';

        if (empty($destination) || empty($etd_date) || empty($cutoff_date)) {
            $error = 'Please fill in Destination Sector, Cut-off Date, and ETD Date.';
        } else {
            $stmt = $db->prepare("INSERT INTO vessel_schedules (vessel_name, voyage_no, destination, etd_date, eta_date, cutoff_date, bg_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$vessel_name, $voyage_no, $destination, $etd_date, $eta_date, $cutoff_date, $bg_image, $status]);
            $msg = 'Vessel schedule added successfully!';
        }
    } elseif ($action === 'edit_schedule') {
        $id          = intval($_POST['id'] ?? 0);
        $destination = sanitize_input($_POST['destination'] ?? '');
        $etd_date    = sanitize_input($_POST['etd_date'] ?? '');
        $eta_date    = sanitize_input($_POST['eta_date'] ?? '');
        $cutoff_date = sanitize_input($_POST['cutoff_date'] ?? '');
        $bg_image    = sanitize_input($_POST['bg_image'] ?? '');
        $status      = sanitize_input($_POST['status'] ?? 'Booking Open');
        $vessel_name = $destination . ' Cargo Sailing';

        if ($id > 0 && !empty($destination) && !empty($etd_date)) {
            $stmt = $db->prepare("UPDATE vessel_schedules SET vessel_name = ?, destination = ?, etd_date = ?, eta_date = ?, cutoff_date = ?, bg_image = ?, status = ? WHERE id = ?");
            $stmt->execute([$vessel_name, $destination, $etd_date, $eta_date, $cutoff_date, $bg_image, $status, $id]);
            $msg = 'Vessel schedule updated successfully!';
        } else {
            $error = 'Failed to update schedule. Please check required fields.';
        }
    } elseif ($action === 'update_status') {
        $id = intval($_POST['id'] ?? 0);
        $new_status = $_POST['status'] ?? 'Booking Open';
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE vessel_schedules SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            $msg = 'Schedule status updated!';
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM vessel_schedules WHERE id = ?");
            $stmt->execute([$id]);
            $msg = 'Vessel schedule deleted!';
        }
    }
}

// Fetch all vessel schedules
$schedules = $db->query("SELECT * FROM vessel_schedules ORDER BY id DESC")->fetchAll();
?>

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

<!-- Add New Vessel Schedule Card -->
<div class="panel-card" style="padding: 1.75rem; margin-bottom: 2rem;">
  <h3 class="panel-title" style="margin-bottom: 1.25rem;">
    <i class="fa-solid fa-plus me-2 text-primary"></i>Add Upcoming Sailing Schedule
  </h3>
  
  <form action="vessels.php" method="POST">
    <input type="hidden" name="action" value="add">
    
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1.25rem; margin-bottom:1.5rem;">
      <div>
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Destination Sector *</label>
        <select name="destination" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border); outline:none;" required>
          <option value="Seychelles">🇸🇨 Seychelles (Port Victoria)</option>
          <option value="Mauritius">🇲🇺 Mauritius (Port Louis)</option>
          <option value="Zanzibar">🇹🇿 Zanzibar (Malindi Port)</option>
          <option value="Comoros">🇰🇲 Comoros (Moroni Port)</option>
          <option value="Dar Es Salaam">🇹🇿 Dar Es Salaam</option>
          <option value="Uganda">🇺🇬 Uganda (Kampala)</option>
          <option value="Zambia">🇿🇲 Zambia (Lusaka)</option>
          <option value="Maldives">🇲🇻 Maldives (Port of Male)</option>
          <option value="India">🇮🇳 India</option>
          <option value="Other">Other Sector</option>
        </select>
      </div>

      <div>
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Cut-off Date (Cargo Gate Close) *</label>
        <input type="date" name="cutoff_date" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
      </div>

      <div>
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">ETD Date (Departure Dubai) *</label>
        <input type="date" name="etd_date" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
      </div>

      <div>
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">ETA Date (Destination Arrival) *</label>
        <input type="date" name="eta_date" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
      </div>

      <div>
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Background Image URL (Optional)</label>
        <input type="text" name="bg_image" placeholder="e.g. images/backgrounds/chinaseychellescargo.jpg" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);">
      </div>

      <div>
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Status</label>
        <select name="status" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border); outline:none;">
          <option value="Booking Open">Booking Open</option>
          <option value="Closing Soon">Closing Soon</option>
          <option value="Departed">Departed</option>
        </select>
      </div>
    </div>

    <button type="submit" class="btn-sm btn-admin-primary" style="padding:0.75rem 1.5rem;">
      <i class="fa-solid fa-ship me-1"></i> Save Sailing Schedule
    </button>
  </form>
</div>

<!-- Active Schedules Table Card -->
<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title"><i class="fa-solid fa-ship me-2 text-primary"></i>Active Sailing Schedules (<?php echo count($schedules); ?>)</h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Destination Sector</th>
          <th>Cut-off Date</th>
          <th>ETD (Dubai)</th>
          <th>ETA (Destination)</th>
          <th>Background Image</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($schedules)): ?>
          <tr>
            <td colspan="8" style="text-align:center; color:var(--admin-muted); padding:3rem;">No sailing schedules added yet. Use the form above to add your first schedule!</td>
          </tr>
        <?php else: ?>
          <?php foreach ($schedules as $s): ?>
            <tr>
              <td>#<?php echo $s['id']; ?></td>
              <td>
                <span class="badge" style="background:#EFF6FF; color:#0066FF; font-size:0.85rem;">
                  <i class="fa-solid fa-location-dot me-1"></i><?php echo htmlspecialchars($s['destination']); ?> Sector
                </span>
              </td>
              <td><span style="color:#DC2626; font-weight:600;"><?php echo date('M d, Y', strtotime($s['cutoff_date'])); ?></span></td>
              <td><strong><?php echo date('M d, Y', strtotime($s['etd_date'])); ?></strong></td>
              <td><span style="color:#059669; font-weight:600;"><?php echo date('M d, Y', strtotime($s['eta_date'])); ?></span></td>
              <td>
                <?php if (!empty($s['bg_image'])): ?>
                  <span style="font-size:0.78rem; color:var(--admin-muted); font-family:monospace;" title="<?php echo htmlspecialchars($s['bg_image']); ?>">
                    <i class="fa-solid fa-image me-1 text-primary"></i><?php echo htmlspecialchars(basename($s['bg_image'])); ?>
                  </span>
                <?php else: ?>
                  <span style="font-size:0.75rem; color:var(--admin-muted); font-style:italic;">Default</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-<?php 
                  if ($s['status']==='Booking Open') echo 'quoted';
                  elseif ($s['status']==='Closing Soon') echo 'pending';
                  else echo 'archived';
                ?>">
                  <?php echo htmlspecialchars($s['status']); ?>
                </span>
              </td>
              <td style="text-align:right; white-space:nowrap;">
                <!-- Edit Button -->
                <button type="button" class="btn-sm btn-admin-primary" onclick='editSchedule(<?php echo json_encode($s); ?>)' title="Edit Schedule">
                  <i class="fa-solid fa-pen-to-square"></i> Edit
                </button>

                <!-- Status Selector -->
                <form action="vessels.php" method="POST" style="display:inline-block;">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                  <select name="status" onchange="this.form.submit()" class="btn-sm btn-admin-secondary" style="outline:none; cursor:pointer;">
                    <option value="Booking Open" <?php echo $s['status']==='Booking Open'?'selected':''; ?>>Booking Open</option>
                    <option value="Closing Soon" <?php echo $s['status']==='Closing Soon'?'selected':''; ?>>Closing Soon</option>
                    <option value="Departed" <?php echo $s['status']==='Departed'?'selected':''; ?>>Departed</option>
                  </select>
                </form>

                <!-- Delete Button -->
                <form action="vessels.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                  <button type="submit" class="btn-sm btn-admin-danger" title="Delete Schedule">
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

<!-- Modal Popup for Editing Schedule -->
<div id="editScheduleModal" style="display:none; position:fixed; inset:0; background:rgba(10,25,47,0.7); backdrop-filter:blur(6px); z-index:2000; align-items:center; justify-content:center; padding:1.5rem;">
  <div style="background:#FFFFFF; border-radius:12px; max-width:550px; width:100%; padding:2rem; box-shadow:0 20px 40px rgba(0,0,0,0.3); position:relative;">
    <button type="button" onclick="closeEditModal()" style="position:absolute; top:1.25rem; right:1.25rem; background:none; border:none; font-size:1.25rem; cursor:pointer; color:var(--admin-muted);">
      <i class="fa-solid fa-xmark"></i>
    </button>
    
    <h3 style="font-family:'Outfit', sans-serif; font-size:1.3rem; margin-bottom:1.5rem;">
      <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Sailing Schedule
    </h3>

    <form action="vessels.php" method="POST">
      <input type="hidden" name="action" value="edit_schedule">
      <input type="hidden" name="id" id="edit_id">

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Destination Sector *</label>
        <select name="destination" id="edit_destination" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border); outline:none;" required>
          <option value="Seychelles">🇸🇨 Seychelles (Port Victoria)</option>
          <option value="Mauritius">🇲🇺 Mauritius (Port Louis)</option>
          <option value="Zanzibar">🇹🇿 Zanzibar (Malindi Port)</option>
          <option value="Comoros">🇰🇲 Comoros (Moroni Port)</option>
          <option value="Dar Es Salaam">🇹🇿 Dar Es Salaam</option>
          <option value="Uganda">🇺🇬 Uganda (Kampala)</option>
          <option value="Zambia">🇿🇲 Zambia (Lusaka)</option>
          <option value="Maldives">🇲🇻 Maldives (Port of Male)</option>
          <option value="India">🇮🇳 India</option>
          <option value="Other">Other Sector</option>
        </select>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
        <div>
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Cut-off Date *</label>
          <input type="date" name="cutoff_date" id="edit_cutoff_date" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
        </div>
        <div>
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">ETD Date (Dubai) *</label>
          <input type="date" name="etd_date" id="edit_etd_date" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
        <div>
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">ETA Date (Destination) *</label>
          <input type="date" name="eta_date" id="edit_eta_date" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
        </div>
        <div>
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Status</label>
          <select name="status" id="edit_status" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border); outline:none;">
            <option value="Booking Open">Booking Open</option>
            <option value="Closing Soon">Closing Soon</option>
            <option value="Departed">Departed</option>
          </select>
        </div>
      </div>

      <div style="margin-bottom:1.5rem;">
        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:0.4rem;">Background Image URL (Optional)</label>
        <input type="text" name="bg_image" id="edit_bg_image" placeholder="e.g. images/backgrounds/chinaseychellescargo.jpg" class="btn-sm btn-admin-secondary" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);">
      </div>

      <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
        <button type="button" class="btn-sm btn-admin-secondary" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn-sm btn-admin-primary" style="padding:0.65rem 1.5rem;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function editSchedule(schedule) {
  document.getElementById('edit_id').value = schedule.id;
  document.getElementById('edit_destination').value = schedule.destination;
  document.getElementById('edit_cutoff_date').value = schedule.cutoff_date;
  document.getElementById('edit_etd_date').value = schedule.etd_date;
  document.getElementById('edit_eta_date').value = schedule.eta_date;
  document.getElementById('edit_bg_image').value = schedule.bg_image || '';
  document.getElementById('edit_status').value = schedule.status;

  const modal = document.getElementById('editScheduleModal');
  modal.style.display = 'flex';
}

function closeEditModal() {
  document.getElementById('editScheduleModal').style.display = 'none';
}
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
