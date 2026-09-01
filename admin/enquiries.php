<?php
/**
 * Seychelles International Cargo LLC - Advanced Admin Online Enquiries Manager
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

// Filter Query
$filter = $_GET['filter'] ?? 'all';
if ($filter === 'pending') {
    $stmt = $db->prepare("SELECT * FROM enquiries WHERE status = 'Pending' ORDER BY id DESC");
    $stmt->execute();
} elseif ($filter === 'contacted') {
    $stmt = $db->prepare("SELECT * FROM enquiries WHERE status = 'Contacted' ORDER BY id DESC");
    $stmt->execute();
} elseif ($filter === 'archived') {
    $stmt = $db->prepare("SELECT * FROM enquiries WHERE status = 'Archived' ORDER BY id DESC");
    $stmt->execute();
} else {
    $stmt = $db->query("SELECT * FROM enquiries ORDER BY id DESC");
}
$enquiries = $stmt->fetchAll();

// Summary Statistics
$total_count     = $db->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$pending_count   = $db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Pending'")->fetchColumn();
$contacted_count = $db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Contacted'")->fetchColumn();
$archived_count  = $db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Archived'")->fetchColumn();
?>

<!-- Stat Summary Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div>
      <div class="stat-label">Total Enquiries</div>
      <div class="stat-val"><?php echo $total_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#EFF6FF; color:#0066FF;">
      <i class="fa-solid fa-file-pen"></i>
    </div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Pending Action</div>
      <div class="stat-val" style="color:#D97706;"><?php echo $pending_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#FEF3C7; color:#D97706;">
      <i class="fa-solid fa-clock"></i>
    </div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Contacted / Resolved</div>
      <div class="stat-val" style="color:#10B981;"><?php echo $contacted_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#D1FAE5; color:#10B981;">
      <i class="fa-solid fa-circle-check"></i>
    </div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Archived</div>
      <div class="stat-val" style="color:#64748B;"><?php echo $archived_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#F1F5F9; color:#64748B;">
      <i class="fa-solid fa-box-archive"></i>
    </div>
  </div>
</div>

<!-- Controls Bar: Filter Tabs & Real-time Search -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
  <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
    <a href="enquiries.php?filter=all" class="btn-sm <?php echo $filter==='all'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-list me-1"></i> All (<?php echo $total_count; ?>)
    </a>
    <a href="enquiries.php?filter=pending" class="btn-sm <?php echo $filter==='pending'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-clock me-1"></i> Pending (<?php echo $pending_count; ?>)
    </a>
    <a href="enquiries.php?filter=contacted" class="btn-sm <?php echo $filter==='contacted'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-circle-check me-1"></i> Contacted (<?php echo $contacted_count; ?>)
    </a>
    <a href="enquiries.php?filter=archived" class="btn-sm <?php echo $filter==='archived'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-box-archive me-1"></i> Archived (<?php echo $archived_count; ?>)
    </a>
  </div>

  <div style="position:relative; width:100%; max-width:320px;">
    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--admin-muted); font-size:0.85rem;"></i>
    <input type="text" id="adminSearchInput" onkeyup="filterAdminTable()" placeholder="Search name, email, phone, or destination..." style="width:100%; padding:0.55rem 1rem 0.55rem 2.4rem; border-radius:8px; border:1px solid var(--admin-border); outline:none; font-size:0.88rem; background:#FFFFFF;">
  </div>
</div>

<!-- Main Table Panel -->
<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title">
      <i class="fa-solid fa-file-pen me-2 text-primary"></i>Online Cargo Enquiries (<?php echo count($enquiries); ?>)
    </h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table" id="enquiriesTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Customer Name</th>
          <th>Contact Info</th>
          <th>Destination / Place</th>
          <th style="width:32%;">Message Preview</th>
          <th>Status</th>
          <th>Submitted Date</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($enquiries)): ?>
          <tr>
            <td colspan="8" style="text-align:center; color:var(--admin-muted); padding:3rem;">
              <i class="fa-solid fa-folder-open me-2" style="font-size:1.5rem;"></i><br>No online enquiries found for this view.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($enquiries as $e): 
            $fullName = trim($e['firstname'] . ' ' . $e['lastname']);
            $snippet = mb_strimwidth($e['message'], 0, 65, '...');
            $waPhone = preg_replace('/[^0-9]/', '', $e['phone']);
          ?>
            <tr class="searchable-row">
              <td><strong>#<?php echo $e['id']; ?></strong></td>
              <td>
                <div style="display:flex; align-items:center; gap:0.6rem;">
                  <div style="width:34px; height:34px; border-radius:50%; background:#EFF6FF; color:#0066FF; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem;">
                    <i class="fa-solid fa-user"></i>
                  </div>
                  <div>
                    <strong style="display:block;"><?php echo htmlspecialchars($fullName); ?></strong>
                  </div>
                </div>
              </td>
              <td>
                <div>
                  <a href="mailto:<?php echo htmlspecialchars($e['email']); ?>" style="color:#0066FF; text-decoration:none; font-weight:500;">
                    <i class="fa-regular fa-envelope me-1"></i><?php echo htmlspecialchars($e['email']); ?>
                  </a>
                </div>
                <div style="margin-top:0.2rem;">
                  <a href="tel:<?php echo htmlspecialchars($e['phone']); ?>" style="color:var(--admin-text); text-decoration:none;">
                    <i class="fa-solid fa-phone me-1 text-primary"></i><?php echo htmlspecialchars($e['phone']); ?>
                  </a>
                </div>
              </td>
              <td>
                <span class="badge" style="background:#F1F5F9; color:#0F172A; border:1px solid #E2E8F0;">
                  <i class="fa-solid fa-location-dot me-1 text-accent"></i><?php echo htmlspecialchars($e['place']); ?>
                </span>
              </td>
              <td>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:0.5rem; background:#F8FAFC; border:1px solid #E2E8F0; padding:0.45rem 0.75rem; border-radius:6px;">
                  <span style="font-size:0.85rem; color:#475569; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:220px;" title="<?php echo htmlspecialchars($e['message']); ?>">
                    <?php echo htmlspecialchars($snippet); ?>
                  </span>
                  <button type="button" class="btn-sm btn-admin-primary" style="padding:0.25rem 0.55rem; font-size:0.75rem; flex-shrink:0;" 
                          onclick='openEnquiryModal(<?php echo json_encode([
                            "id" => $e["id"],
                            "name" => $fullName,
                            "email" => $e["email"],
                            "phone" => $e["phone"],
                            "place" => $e["place"],
                            "message" => $e["message"],
                            "status" => $e["status"],
                            "created_at" => date("M d, Y H:i", strtotime($e["created_at"]))
                          ]); ?>)'>
                    <i class="fa-solid fa-eye me-1"></i> View
                  </button>
                </div>
              </td>
              <td>
                <span class="badge badge-<?php echo strtolower($e['status']); ?>">
                  <i class="fa-solid <?php 
                    if ($e['status']==='Pending') echo 'fa-clock me-1';
                    elseif ($e['status']==='Contacted') echo 'fa-circle-check me-1';
                    else echo 'fa-box-archive me-1';
                  ?>"></i><?php echo htmlspecialchars($e['status']); ?>
                </span>
              </td>
              <td><small style="color:var(--admin-muted);"><?php echo date('M d, Y H:i', strtotime($e['created_at'])); ?></small></td>
              <td style="text-align:right;">
                <div style="display:inline-flex; align-items:center; gap:0.35rem;">
                  <?php if (!empty($waPhone)): ?>
                    <a href="https://wa.me/<?php echo $waPhone; ?>?text=<?php echo urlencode("Hello " . $fullName . ", regarding your online enquiry at Seychelles International Cargo LLC..."); ?>" 
                       target="_blank" class="btn-sm" style="background:#D1FAE5; color:#059669;" title="Chat on WhatsApp">
                      <i class="fa-brands fa-whatsapp"></i>
                    </a>
                  <?php endif; ?>

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
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Interactive Enquiry Detail Modal Dialog -->
<div id="enquiryModalOverlay" class="admin-modal-overlay">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <div class="admin-modal-title">
        <i class="fa-solid fa-file-circle-check text-accent"></i>
        <span>Enquiry Details <strong id="modalId">#0</strong></span>
      </div>
      <button type="button" class="admin-modal-close" onclick="closeEnquiryModal()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="admin-modal-body">
      <!-- Customer Information Card -->
      <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:1.25rem; margin-bottom:1.25rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.85rem; flex-wrap:wrap; gap:0.5rem;">
          <div>
            <h4 id="modalName" style="font-size:1.15rem; font-weight:700; color:#0F172A; margin-bottom:0.25rem;">Customer Name</h4>
            <span id="modalDate" style="font-size:0.8rem; color:#64748B;"><i class="fa-regular fa-clock me-1"></i> Submitted Date</span>
          </div>
          <span id="modalStatusBadge" class="badge badge-pending">Pending</span>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; font-size:0.88rem; color:#334155; pt:0.5rem; border-top:1px solid #E2E8F0;">
          <div>
            <span style="color:#64748B; font-size:0.78rem; text-transform:uppercase; font-weight:700; display:block;">Email Address</span>
            <a id="modalEmail" href="#" style="color:#0066FF; font-weight:600; text-decoration:none;"><i class="fa-regular fa-envelope me-1"></i> email@example.com</a>
          </div>
          <div>
            <span style="color:#64748B; font-size:0.78rem; text-transform:uppercase; font-weight:700; display:block;">Phone Number</span>
            <a id="modalPhone" href="#" style="color:#0066FF; font-weight:600; text-decoration:none;"><i class="fa-solid fa-phone me-1"></i> +971 50 000 0000</a>
          </div>
          <div style="grid-column: 1 / -1; margin-top:0.25rem;">
            <span style="color:#64748B; font-size:0.78rem; text-transform:uppercase; font-weight:700; display:block;">Destination / Place</span>
            <span id="modalPlace" style="font-weight:600; color:#0F172A;"><i class="fa-solid fa-location-dot me-1 text-primary"></i> Seychelles</span>
          </div>
        </div>
      </div>

      <!-- Full Formatted Message Body -->
      <div>
        <label style="font-weight:700; font-size:0.85rem; color:#475569; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.5rem; display:block;">
          <i class="fa-solid fa-message me-1 text-primary"></i> Full Message Details:
        </label>
        <div id="modalMessage" style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; padding:1.15rem; font-size:0.95rem; line-height:1.65; color:#0F172A; min-height:100px; white-space:pre-wrap;">
          Full message content will load here...
        </div>
      </div>
    </div>

    <div class="admin-modal-footer">
      <div style="display:flex; gap:0.6rem; flex-wrap:wrap;">
        <a id="modalWaBtn" href="#" target="_blank" class="btn-whatsapp">
          <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
        </a>
        <a id="modalEmailBtn" href="#" class="btn-email-reply">
          <i class="fa-regular fa-envelope"></i> Email Reply
        </a>
      </div>

      <div style="display:flex; gap:0.5rem; align-items:center;">
        <form id="modalStatusForm" action="enquiries.php" method="POST" style="display:inline-block;">
          <input type="hidden" name="action" value="update_status">
          <input type="hidden" id="modalStatusId" name="id" value="0">
          <select id="modalStatusSelect" name="status" onchange="this.form.submit()" class="btn-sm btn-admin-secondary" style="padding:0.5rem 0.75rem; cursor:pointer;">
            <option value="Pending">Pending</option>
            <option value="Contacted">Contacted</option>
            <option value="Archived">Archived</option>
          </select>
        </form>

        <form id="modalDeleteForm" action="enquiries.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this enquiry permanently?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" id="modalDeleteId" name="id" value="0">
          <button type="submit" class="btn-sm btn-admin-danger" style="padding:0.5rem 0.75rem;">
            <i class="fa-solid fa-trash-can me-1"></i> Delete
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function openEnquiryModal(data) {
  document.getElementById('modalId').innerText = '#' + data.id;
  document.getElementById('modalName').innerText = data.name;
  document.getElementById('modalDate').innerHTML = '<i class="fa-regular fa-clock me-1"></i> ' + data.created_at;
  
  document.getElementById('modalEmail').innerText = data.email;
  document.getElementById('modalEmail').href = 'mailto:' + data.email;
  
  document.getElementById('modalPhone').innerText = data.phone;
  document.getElementById('modalPhone').href = 'tel:' + data.phone;
  
  document.getElementById('modalPlace').innerHTML = '<i class="fa-solid fa-location-dot me-1 text-primary"></i> ' + (data.place || 'General Sector');
  document.getElementById('modalMessage').innerText = data.message;

  // Status badge & select
  const badge = document.getElementById('modalStatusBadge');
  badge.className = 'badge badge-' + data.status.toLowerCase();
  badge.innerText = data.status;

  document.getElementById('modalStatusId').value = data.id;
  document.getElementById('modalStatusSelect').value = data.status;
  document.getElementById('modalDeleteId').value = data.id;

  // Quick Action Buttons
  const cleanPhone = (data.phone || '').replace(/[^0-9]/g, '');
  const waBtn = document.getElementById('modalWaBtn');
  if (cleanPhone) {
    waBtn.style.display = 'inline-flex';
    waBtn.href = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent('Hello ' + data.name + ', regarding your enquiry at Seychelles International Cargo LLC...');
  } else {
    waBtn.style.display = 'none';
  }

  const emailBtn = document.getElementById('modalEmailBtn');
  emailBtn.href = 'mailto:' + data.email + '?subject=' + encodeURIComponent('Re: Seychelles International Cargo Enquiry #' + data.id);

  document.getElementById('enquiryModalOverlay').classList.add('open');
}

function closeEnquiryModal() {
  document.getElementById('enquiryModalOverlay').classList.remove('open');
}

document.getElementById('enquiryModalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeEnquiryModal();
});

// Real-time Client Search Filter
function filterAdminTable() {
  const query = document.getElementById('adminSearchInput').value.toLowerCase().trim();
  const rows = document.querySelectorAll('#enquiriesTable .searchable-row');

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
