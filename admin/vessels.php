<?php
/**
 * Seychelles International Cargo LLC - Advanced Admin Vessel Schedules Manager
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

// Filter Query
$filter = $_GET['filter'] ?? 'all';
if ($filter === 'open') {
    $stmt = $db->prepare("SELECT * FROM vessel_schedules WHERE status = 'Booking Open' ORDER BY id DESC");
    $stmt->execute();
} elseif ($filter === 'closing') {
    $stmt = $db->prepare("SELECT * FROM vessel_schedules WHERE status = 'Closing Soon' ORDER BY id DESC");
    $stmt->execute();
} elseif ($filter === 'departed') {
    $stmt = $db->prepare("SELECT * FROM vessel_schedules WHERE status = 'Departed' ORDER BY id DESC");
    $stmt->execute();
} else {
    $stmt = $db->query("SELECT * FROM vessel_schedules ORDER BY id DESC");
}
$schedules = $stmt->fetchAll();

// Summary Statistics
$total_count        = $db->query("SELECT COUNT(*) FROM vessel_schedules")->fetchColumn();
$open_count         = $db->query("SELECT COUNT(*) FROM vessel_schedules WHERE status = 'Booking Open'")->fetchColumn();
$closing_count      = $db->query("SELECT COUNT(*) FROM vessel_schedules WHERE status = 'Closing Soon'")->fetchColumn();
$departed_count     = $db->query("SELECT COUNT(*) FROM vessel_schedules WHERE status = 'Departed'")->fetchColumn();

// Flag Mapping Helper
function get_sector_flag_badge($destination) {
    $d = strtolower($destination);
    if (str_contains($d, 'seychelles')) return '🇸🇨';
    if (str_contains($d, 'mauritius')) return '🇲🇺';
    if (str_contains($d, 'zanzibar')) return '🇹🇿';
    if (str_contains($d, 'comoros')) return '🇰🇲';
    if (str_contains($d, 'dar')) return '🇹🇿';
    if (str_contains($d, 'uganda')) return '🇺🇬';
    if (str_contains($d, 'zambia')) return '🇿🇲';
    if (str_contains($d, 'maldives')) return '🇲🇻';
    if (str_contains($d, 'india')) return '🇮🇳';
    if (str_contains($d, 'china')) return '🇨🇳';
    if (str_contains($d, 'mayotte')) return '🇾🇹';
    if (str_contains($d, 'madagascar')) return '🇲🇬';
    return '🚢';
}
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

<!-- Stat Summary Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div>
      <div class="stat-label">Total Sailings</div>
      <div class="stat-val"><?php echo $total_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#EFF6FF; color:#0066FF;">
      <i class="fa-solid fa-ship"></i>
    </div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Booking Open</div>
      <div class="stat-val" style="color:#10B981;"><?php echo $open_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#D1FAE5; color:#10B981;">
      <i class="fa-solid fa-door-open"></i>
    </div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Closing Soon</div>
      <div class="stat-val" style="color:#D97706;"><?php echo $closing_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#FEF3C7; color:#D97706;">
      <i class="fa-solid fa-hourglass-half"></i>
    </div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Departed / In-Transit</div>
      <div class="stat-val" style="color:#64748B;"><?php echo $departed_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#F1F5F9; color:#64748B;">
      <i class="fa-solid fa-anchor"></i>
    </div>
  </div>
</div>

<!-- Controls Bar: Add New Schedule Button, Filter Tabs & Real-time Search -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
  <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
    <button type="button" class="btn-sm btn-admin-primary" onclick="openAddScheduleModal()" style="padding:0.6rem 1.15rem; font-weight:700;">
      <i class="fa-solid fa-plus me-1"></i> Add New Sailing Schedule
    </button>

    <div style="display:inline-flex; gap:0.35rem; margin-left:0.5rem;">
      <a href="vessels.php?filter=all" class="btn-sm <?php echo $filter==='all'?'btn-admin-primary':'btn-admin-secondary'; ?>">
        <i class="fa-solid fa-list me-1"></i> All (<?php echo $total_count; ?>)
      </a>
      <a href="vessels.php?filter=open" class="btn-sm <?php echo $filter==='open'?'btn-admin-primary':'btn-admin-secondary'; ?>">
        <i class="fa-solid fa-door-open me-1"></i> Open (<?php echo $open_count; ?>)
      </a>
      <a href="vessels.php?filter=closing" class="btn-sm <?php echo $filter==='closing'?'btn-admin-primary':'btn-admin-secondary'; ?>">
        <i class="fa-solid fa-hourglass-half me-1"></i> Closing (<?php echo $closing_count; ?>)
      </a>
      <a href="vessels.php?filter=departed" class="btn-sm <?php echo $filter==='departed'?'btn-admin-primary':'btn-admin-secondary'; ?>">
        <i class="fa-solid fa-anchor me-1"></i> Departed (<?php echo $departed_count; ?>)
      </a>
    </div>
  </div>

  <div style="position:relative; width:100%; max-width:320px;">
    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--admin-muted); font-size:0.85rem;"></i>
    <input type="text" id="adminSearchInput" onkeyup="filterAdminTable()" placeholder="Search destination sector..." style="width:100%; padding:0.55rem 1rem 0.55rem 2.4rem; border-radius:8px; border:1px solid var(--admin-border); outline:none; font-size:0.88rem; background:#FFFFFF;">
  </div>
</div>

<!-- Active Schedules Table Card -->
<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title">
      <i class="fa-solid fa-ship me-2 text-primary"></i>Active Sailing Schedules (<?php echo count($schedules); ?>)
    </h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table" id="vesselsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Destination Sector</th>
          <th>Cargo Cut-off</th>
          <th>ETD (Dubai)</th>
          <th>ETA (Destination)</th>
          <th>Background Banner</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($schedules)): ?>
          <tr>
            <td colspan="8" style="text-align:center; color:var(--admin-muted); padding:3rem;">
              <i class="fa-solid fa-folder-open me-2" style="font-size:1.5rem;"></i><br>No sailing schedules found for this view.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($schedules as $s): 
            $flag = get_sector_flag_badge($s['destination']);
          ?>
            <tr class="searchable-row">
              <td><strong>#<?php echo $s['id']; ?></strong></td>
              <td>
                <div style="display:flex; align-items:center; gap:0.5rem;">
                  <span style="font-size:1.25rem; line-height:1;"><?php echo $flag; ?></span>
                  <strong style="font-size:0.95rem; color:#0F172A;"><?php echo htmlspecialchars($s['destination']); ?> Sector</strong>
                </div>
              </td>
              <td>
                <span style="color:#DC2626; font-weight:700; background:#FEF2F2; padding:0.25rem 0.6rem; border-radius:6px; border:1px solid #FCA5A5;">
                  <i class="fa-regular fa-clock me-1"></i><?php echo date('M d, Y', strtotime($s['cutoff_date'])); ?>
                </span>
              </td>
              <td>
                <strong style="color:#0F172A;">
                  <i class="fa-solid fa-ship me-1 text-primary"></i><?php echo date('M d, Y', strtotime($s['etd_date'])); ?>
                </strong>
              </td>
              <td>
                <span style="color:#059669; font-weight:700;">
                  <i class="fa-solid fa-anchor me-1"></i><?php echo date('M d, Y', strtotime($s['eta_date'])); ?>
                </span>
              </td>
              <td>
                <?php if (!empty($s['bg_image'])): ?>
                  <span style="font-size:0.78rem; color:#0066FF; background:#EFF6FF; padding:0.25rem 0.55rem; border-radius:4px; border:1px solid #BFDBFE;" title="<?php echo htmlspecialchars($s['bg_image']); ?>">
                    <i class="fa-solid fa-image me-1"></i><?php echo htmlspecialchars(basename($s['bg_image'])); ?>
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
                  <i class="fa-solid <?php 
                    if ($s['status']==='Booking Open') echo 'fa-door-open me-1';
                    elseif ($s['status']==='Closing Soon') echo 'fa-hourglass-half me-1';
                    else echo 'fa-anchor me-1';
                  ?>"></i><?php echo htmlspecialchars($s['status']); ?>
                </span>
              </td>
              <td style="text-align:right; white-space:nowrap;">
                <div style="display:inline-flex; align-items:center; gap:0.35rem;">
                  <!-- Preview Button -->
                  <button type="button" class="btn-sm btn-admin-primary" style="background:#0F172A; color:#FFFFFF;" onclick='openPreviewModal(<?php echo json_encode($s); ?>)' title="Preview Homepage Slide">
                    <i class="fa-solid fa-eye"></i>
                  </button>

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
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Popup for Adding Schedule -->
<div id="addScheduleModalOverlay" class="admin-modal-overlay">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <div class="admin-modal-title">
        <i class="fa-solid fa-plus text-accent"></i>
        <span>Add Upcoming Sailing Schedule</span>
      </div>
      <button type="button" class="admin-modal-close" onclick="closeAddScheduleModal()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="vessels.php" method="POST">
      <div class="admin-modal-body">
        <input type="hidden" name="action" value="add">

        <div style="margin-bottom:1.25rem;">
          <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Destination Sector *</label>
          <select name="destination" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
            <option value="Seychelles">🇸🇨 Seychelles (Port Victoria)</option>
            <option value="Mauritius">🇲🇺 Mauritius (Port Louis)</option>
            <option value="Zanzibar">🇹🇿 Zanzibar (Malindi Port)</option>
            <option value="Comoros">🇰🇲 Comoros (Moroni Port)</option>
            <option value="Dar Es Salaam">🇹🇿 Dar Es Salaam</option>
            <option value="Uganda">🇺🇬 Uganda (Kampala)</option>
            <option value="Zambia">🇿🇲 Zambia (Lusaka)</option>
            <option value="Maldives">🇲🇻 Maldives (Port of Male)</option>
            <option value="India">🇮🇳 India</option>
            <option value="China">🇨🇳 China</option>
            <option value="Mayotte">🇾🇹 Mayotte</option>
            <option value="Madagascar">🇲🇬 Madagascar</option>
            <option value="Other">Other Sector</option>
          </select>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
          <div>
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Cut-off Date (Cargo Gate Close) *</label>
            <input type="date" name="cutoff_date" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
          </div>
          <div>
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">ETD Date (Dubai Departure) *</label>
            <input type="date" name="etd_date" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
          <div>
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">ETA Date (Destination Arrival) *</label>
            <input type="date" name="eta_date" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
          </div>
          <div>
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Status</label>
            <select name="status" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);">
              <option value="Booking Open">Booking Open</option>
              <option value="Closing Soon">Closing Soon</option>
              <option value="Departed">Departed</option>
            </select>
          </div>
        </div>

        <div>
          <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Background Image URL (Optional)</label>
          <input type="text" name="bg_image" placeholder="e.g. images/backgrounds/chinaseychellescargo.jpg" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);">
        </div>
      </div>

      <div class="admin-modal-footer" style="justify-content:flex-end;">
        <button type="button" class="btn-sm btn-admin-secondary" onclick="closeAddScheduleModal()">Cancel</button>
        <button type="submit" class="btn-sm btn-admin-primary" style="padding:0.65rem 1.5rem;">
          <i class="fa-solid fa-ship me-1"></i> Save Sailing Schedule
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Popup for Editing Schedule -->
<div id="editScheduleModalOverlay" class="admin-modal-overlay">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <div class="admin-modal-title">
        <i class="fa-solid fa-pen-to-square text-accent"></i>
        <span>Edit Sailing Schedule</span>
      </div>
      <button type="button" class="admin-modal-close" onclick="closeEditModal()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="vessels.php" method="POST">
      <div class="admin-modal-body">
        <input type="hidden" name="action" value="edit_schedule">
        <input type="hidden" name="id" id="edit_id">

        <div style="margin-bottom:1.25rem;">
          <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Destination Sector *</label>
          <select name="destination" id="edit_destination" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
            <option value="Seychelles">🇸🇨 Seychelles (Port Victoria)</option>
            <option value="Mauritius">🇲🇺 Mauritius (Port Louis)</option>
            <option value="Zanzibar">🇹🇿 Zanzibar (Malindi Port)</option>
            <option value="Comoros">🇰🇲 Comoros (Moroni Port)</option>
            <option value="Dar Es Salaam">🇹🇿 Dar Es Salaam</option>
            <option value="Uganda">🇺🇬 Uganda (Kampala)</option>
            <option value="Zambia">🇿🇲 Zambia (Lusaka)</option>
            <option value="Maldives">🇲🇻 Maldives (Port of Male)</option>
            <option value="India">🇮🇳 India</option>
            <option value="China">🇨🇳 China</option>
            <option value="Mayotte">🇾🇹 Mayotte</option>
            <option value="Madagascar">🇲🇬 Madagascar</option>
            <option value="Other">Other Sector</option>
          </select>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
          <div>
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Cut-off Date *</label>
            <input type="date" name="cutoff_date" id="edit_cutoff_date" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
          </div>
          <div>
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">ETD Date (Dubai) *</label>
            <input type="date" name="etd_date" id="edit_etd_date" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
          <div>
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">ETA Date (Destination) *</label>
            <input type="date" name="eta_date" id="edit_eta_date" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);" required>
          </div>
          <div>
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Status</label>
            <select name="status" id="edit_status" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);">
              <option value="Booking Open">Booking Open</option>
              <option value="Closing Soon">Closing Soon</option>
              <option value="Departed">Departed</option>
            </select>
          </div>
        </div>

        <div>
          <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem;">Background Image URL (Optional)</label>
          <input type="text" name="bg_image" id="edit_bg_image" placeholder="e.g. images/backgrounds/chinaseychellescargo.jpg" class="form-control" style="width:100%; padding:0.65rem; border:1px solid var(--admin-border);">
        </div>
      </div>

      <div class="admin-modal-footer" style="justify-content:flex-end;">
        <button type="button" class="btn-sm btn-admin-secondary" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn-sm btn-admin-primary" style="padding:0.65rem 1.5rem;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Popup for Live Slide Preview -->
<div id="previewScheduleModalOverlay" class="admin-modal-overlay">
  <div class="admin-modal-card" style="max-width:580px;">
    <div class="admin-modal-header">
      <div class="admin-modal-title">
        <i class="fa-solid fa-eye text-accent"></i>
        <span>Homepage Slide Live Preview</span>
      </div>
      <button type="button" class="admin-modal-close" onclick="closePreviewModal()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="admin-modal-body" style="background:#090D16; color:#FFFFFF; padding:2rem;">
      <div style="font-size:0.78rem; text-transform:uppercase; color:#FF7A00; letter-spacing:1px; font-weight:700; margin-bottom:0.4rem;">
        Upcoming Sailing Schedule
      </div>
      <h3 id="prevTitle" style="font-size:1.8rem; font-weight:800; color:#FFFFFF; margin-bottom:1rem;">
        Seychelles Cargo Sailing
      </h3>
      
      <!-- Floating Glass Preview Card -->
      <div style="background:rgba(15, 23, 42, 0.92); border:1px solid rgba(255,122,0,0.3); border-radius:16px; padding:1.5rem; box-shadow:0 10px 30px rgba(0,0,0,0.5);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; padding-bottom:0.75rem; border-bottom:1px solid rgba(255,255,255,0.1);">
          <span style="font-size:0.75rem; color:#94A3B8; font-weight:700; text-transform:uppercase;">SAILING DETAILS</span>
          <span id="prevStatus" class="badge badge-quoted">Booking Open</span>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; font-size:0.9rem;">
          <span style="color:#94A3B8;"><i class="fa-regular fa-clock me-1 text-danger"></i> Cargo Cut-off Date:</span>
          <strong id="prevCutoff" style="color:#EF4444;">Oct 15, 2026</strong>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; font-size:0.9rem;">
          <span style="color:#94A3B8;"><i class="fa-solid fa-ship me-1 text-primary"></i> ETD Dubai:</span>
          <strong id="prevEtd" style="color:#FFFFFF;">Oct 18, 2026</strong>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; font-size:0.9rem;">
          <span style="color:#94A3B8;"><i class="fa-solid fa-anchor me-1 text-success"></i> ETA Destination:</span>
          <strong id="prevEta" style="color:#34D399;">Oct 25, 2026</strong>
        </div>

        <button type="button" class="btn btn-primary btn-block" style="background:#FF7A00; border:none; padding:0.65rem; font-size:0.9rem;">
          Reserve Space <i class="fa-solid fa-arrow-right ms-1"></i>
        </button>
      </div>
    </div>

    <div class="admin-modal-footer" style="justify-content:flex-end;">
      <button type="button" class="btn-sm btn-admin-secondary" onclick="closePreviewModal()">Close Preview</button>
    </div>
  </div>
</div>

<script>
function openAddScheduleModal() {
  document.getElementById('addScheduleModalOverlay').classList.add('open');
}
function closeAddScheduleModal() {
  document.getElementById('addScheduleModalOverlay').classList.remove('open');
}

function editSchedule(data) {
  document.getElementById('edit_id').value = data.id;
  document.getElementById('edit_destination').value = data.destination;
  document.getElementById('edit_cutoff_date').value = data.cutoff_date;
  document.getElementById('edit_etd_date').value = data.etd_date;
  document.getElementById('edit_eta_date').value = data.eta_date;
  document.getElementById('edit_bg_image').value = data.bg_image || '';
  document.getElementById('edit_status').value = data.status;

  document.getElementById('editScheduleModalOverlay').classList.add('open');
}
function closeEditModal() {
  document.getElementById('editScheduleModalOverlay').classList.remove('open');
}

function openPreviewModal(data) {
  document.getElementById('prevTitle').innerText = data.destination + ' Cargo Sailing';
  document.getElementById('prevCutoff').innerText = data.cutoff_date;
  document.getElementById('prevEtd').innerText = data.etd_date;
  document.getElementById('prevEta').innerText = data.eta_date;
  
  const statusBadge = document.getElementById('prevStatus');
  statusBadge.className = 'badge badge-' + (data.status === 'Booking Open' ? 'quoted' : (data.status === 'Closing Soon' ? 'pending' : 'archived'));
  statusBadge.innerText = data.status;

  document.getElementById('previewScheduleModalOverlay').classList.add('open');
}
function closePreviewModal() {
  document.getElementById('previewScheduleModalOverlay').classList.remove('open');
}

document.querySelectorAll('.admin-modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('open');
    }
  });
});

// Real-time Client Search Filter
function filterAdminTable() {
  const query = document.getElementById('adminSearchInput').value.toLowerCase().trim();
  const rows = document.querySelectorAll('#vesselsTable .searchable-row');

  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    if (text.includes(query)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}
</script>

<?php
require_once __DIR__ . '/includes/admin_footer.php';
?>
