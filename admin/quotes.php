<?php
/**
 * Seychelles International Cargo LLC - Advanced Admin Quote Requests Manager
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

// Summary Statistics
$total_count    = $db->query("SELECT COUNT(*) FROM quotes")->fetchColumn();
$pending_count  = $db->query("SELECT COUNT(*) FROM quotes WHERE status = 'Pending'")->fetchColumn();
$quoted_count   = $db->query("SELECT COUNT(*) FROM quotes WHERE status = 'Quoted'")->fetchColumn();
$archived_count = $db->query("SELECT COUNT(*) FROM quotes WHERE status = 'Archived'")->fetchColumn();
?>

<!-- Stat Summary Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div>
      <div class="stat-label">Total Quote Requests</div>
      <div class="stat-val"><?php echo $total_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#EFF6FF; color:#0066FF;">
      <i class="fa-solid fa-calculator"></i>
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
      <div class="stat-label">Quoted / Responded</div>
      <div class="stat-val" style="color:#10B981;"><?php echo $quoted_count; ?></div>
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
    <a href="quotes.php?filter=all" class="btn-sm <?php echo $filter==='all'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-list me-1"></i> All (<?php echo $total_count; ?>)
    </a>
    <a href="quotes.php?filter=pending" class="btn-sm <?php echo $filter==='pending'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-clock me-1"></i> Pending (<?php echo $pending_count; ?>)
    </a>
    <a href="quotes.php?filter=quoted" class="btn-sm <?php echo $filter==='quoted'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-circle-check me-1"></i> Quoted (<?php echo $quoted_count; ?>)
    </a>
    <a href="quotes.php?filter=archived" class="btn-sm <?php echo $filter==='archived'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-box-archive me-1"></i> Archived (<?php echo $archived_count; ?>)
    </a>
  </div>

  <div style="position:relative; width:100%; max-width:320px;">
    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--admin-muted); font-size:0.85rem;"></i>
    <input type="text" id="adminSearchInput" onkeyup="filterAdminTable()" placeholder="Search name, phone, origin, or destination..." style="width:100%; padding:0.55rem 1rem 0.55rem 2.4rem; border-radius:8px; border:1px solid var(--admin-border); outline:none; font-size:0.88rem; background:#FFFFFF;">
  </div>
</div>

<!-- Main Table Panel -->
<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title">
      <i class="fa-solid fa-calculator me-2 text-primary"></i>All Quote Requests (<?php echo count($quotes); ?>)
    </h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table" id="quotesTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Customer Name</th>
          <th>Phone / WhatsApp</th>
          <th>City Origin</th>
          <th>Destination Sector</th>
          <th>Departure Date</th>
          <th>Status</th>
          <th>Submitted Date</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($quotes)): ?>
          <tr>
            <td colspan="9" style="text-align:center; color:var(--admin-muted); padding:3rem;">
              <i class="fa-solid fa-folder-open me-2" style="font-size:1.5rem;"></i><br>No quote requests found for this view.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($quotes as $q): 
            $waPhone = preg_replace('/[^0-9]/', '', $q['contact']);
          ?>
            <tr class="searchable-row">
              <td><strong>#<?php echo $q['id']; ?></strong></td>
              <td>
                <div style="display:flex; align-items:center; gap:0.6rem;">
                  <div style="width:34px; height:34px; border-radius:50%; background:#EFF6FF; color:#0066FF; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem;">
                    <i class="fa-solid fa-user"></i>
                  </div>
                  <div>
                    <strong style="display:block;"><?php echo htmlspecialchars($q['name']); ?></strong>
                  </div>
                </div>
              </td>
              <td>
                <a href="https://wa.me/<?php echo $waPhone; ?>" target="_blank" style="color:#10B981; font-weight:600; text-decoration:none;">
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
                  <i class="fa-solid <?php 
                    if ($q['status']==='Pending') echo 'fa-clock me-1';
                    elseif ($q['status']==='Quoted') echo 'fa-circle-check me-1';
                    else echo 'fa-box-archive me-1';
                  ?>"></i><?php echo htmlspecialchars($q['status']); ?>
                </span>
              </td>
              <td><small style="color:var(--admin-muted);"><?php echo date('M d, Y H:i', strtotime($q['created_at'])); ?></small></td>
              <td style="text-align:right;">
                <div style="display:inline-flex; align-items:center; gap:0.35rem;">
                  <button type="button" class="btn-sm btn-admin-primary" style="padding:0.25rem 0.55rem; font-size:0.75rem;" 
                          onclick='openQuoteModal(<?php echo json_encode([
                            "id" => $q["id"],
                            "name" => $q["name"],
                            "contact" => $q["contact"],
                            "origin" => $q["origin"],
                            "destination" => $q["destination"],
                            "departure_date" => $q["departure_date"] ?: "Flexible",
                            "message" => $q["message"] ?? "",
                            "status" => $q["status"],
                            "created_at" => date("M d, Y H:i", strtotime($q["created_at"]))
                          ]); ?>)'>
                    <i class="fa-solid fa-eye me-1"></i> View
                  </button>

                  <form action="quotes.php" method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                    <select name="status" onchange="this.form.submit()" class="btn-sm btn-admin-secondary" style="outline:none; cursor:pointer;">
                      <option value="Pending" <?php echo $q['status']==='Pending'?'selected':''; ?>>Pending</option>
                      <option value="Quoted" <?php echo $q['status']==='Quoted'?'selected':''; ?>>Quoted</option>
                      <option value="Archived" <?php echo $q['status']==='Archived'?'selected':''; ?>>Archived</option>
                    </select>
                  </form>

                  <form action="quotes.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this quote request?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $q['id']; ?>">
                    <button type="submit" class="btn-sm btn-admin-danger" title="Delete Quote">
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

<!-- Interactive Quote Detail Modal Dialog -->
<div id="quoteModalOverlay" class="admin-modal-overlay">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <div class="admin-modal-title">
        <i class="fa-solid fa-calculator text-accent"></i>
        <span>Quote Request <strong id="modalId">#0</strong></span>
      </div>
      <button type="button" class="admin-modal-close" onclick="closeQuoteModal()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="admin-modal-body">
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
            <span style="color:#64748B; font-size:0.78rem; text-transform:uppercase; font-weight:700; display:block;">Phone / WhatsApp</span>
            <a id="modalContact" href="#" target="_blank" style="color:#10B981; font-weight:600; text-decoration:none;"><i class="fa-brands fa-whatsapp me-1"></i> +971 50 000 0000</a>
          </div>
          <div>
            <span style="color:#64748B; font-size:0.78rem; text-transform:uppercase; font-weight:700; display:block;">Preferred Departure Date</span>
            <span id="modalDeparture" style="font-weight:600; color:#0F172A;"><i class="fa-regular fa-calendar me-1 text-primary"></i> Flexible</span>
          </div>
          <div>
            <span style="color:#64748B; font-size:0.78rem; text-transform:uppercase; font-weight:700; display:block;">City Origin</span>
            <span id="modalOrigin" style="font-weight:600; color:#0F172A;"><i class="fa-solid fa-map-pin me-1 text-accent"></i> Dubai</span>
          </div>
          <div>
            <span style="color:#64748B; font-size:0.78rem; text-transform:uppercase; font-weight:700; display:block;">Destination Sector</span>
            <span id="modalDestination" style="font-weight:600; color:#0066FF;"><i class="fa-solid fa-location-dot me-1"></i> Seychelles</span>
          </div>
        </div>
      </div>

      <!-- Additional Message Details -->
      <div>
        <label style="font-weight:700; font-size:0.85rem; color:#475569; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.5rem; display:block;">
          <i class="fa-solid fa-message me-1 text-primary"></i> Additional Notes / Cargo Description:
        </label>
        <div id="modalMessage" style="background:#FFFFFF; border:1px solid #CBD5E1; border-radius:10px; padding:1.15rem; font-size:0.95rem; line-height:1.65; color:#0F172A; min-height:80px; white-space:pre-wrap;">
          No additional notes provided.
        </div>
      </div>
    </div>

    <div class="admin-modal-footer">
      <div style="display:flex; gap:0.6rem; flex-wrap:wrap;">
        <a id="modalWaBtn" href="#" target="_blank" class="btn-whatsapp">
          <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
        </a>
      </div>

      <div style="display:flex; gap:0.5rem; align-items:center;">
        <form id="modalStatusForm" action="quotes.php" method="POST" style="display:inline-block;">
          <input type="hidden" name="action" value="update_status">
          <input type="hidden" id="modalStatusId" name="id" value="0">
          <select id="modalStatusSelect" name="status" onchange="this.form.submit()" class="btn-sm btn-admin-secondary" style="padding:0.5rem 0.75rem; cursor:pointer;">
            <option value="Pending">Pending</option>
            <option value="Quoted">Quoted</option>
            <option value="Archived">Archived</option>
          </select>
        </form>

        <form id="modalDeleteForm" action="quotes.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this quote request permanently?');">
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
function openQuoteModal(data) {
  document.getElementById('modalId').innerText = '#' + data.id;
  document.getElementById('modalName').innerText = data.name;
  document.getElementById('modalDate').innerHTML = '<i class="fa-regular fa-clock me-1"></i> ' + data.created_at;
  
  const cleanPhone = (data.contact || '').replace(/[^0-9]/g, '');
  const contactLink = document.getElementById('modalContact');
  contactLink.innerText = data.contact;
  if (cleanPhone) {
    contactLink.href = 'https://wa.me/' + cleanPhone;
  } else {
    contactLink.removeAttribute('href');
  }
  
  document.getElementById('modalOrigin').innerText = data.origin || 'Dubai (DXB)';
  document.getElementById('modalDestination').innerText = data.destination;
  document.getElementById('modalDeparture').innerText = data.departure_date;
  document.getElementById('modalMessage').innerText = data.message || 'No additional message attached.';

  // Status badge & select
  const badge = document.getElementById('modalStatusBadge');
  badge.className = 'badge badge-' + data.status.toLowerCase();
  badge.innerText = data.status;

  document.getElementById('modalStatusId').value = data.id;
  document.getElementById('modalStatusSelect').value = data.status;
  document.getElementById('modalDeleteId').value = data.id;

  // Quick Action Buttons
  const waBtn = document.getElementById('modalWaBtn');
  if (cleanPhone) {
    waBtn.style.display = 'inline-flex';
    waBtn.href = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent('Hello ' + data.name + ', regarding your quote request for ' + data.destination + ' at Seychelles International Cargo LLC...');
  } else {
    waBtn.style.display = 'none';
  }

  document.getElementById('quoteModalOverlay').classList.add('open');
}

function closeQuoteModal() {
  document.getElementById('quoteModalOverlay').classList.remove('open');
}

document.getElementById('quoteModalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeQuoteModal();
});

// Real-time Client Search Filter
function filterAdminTable() {
  const query = document.getElementById('adminSearchInput').value.toLowerCase().trim();
  const rows = document.querySelectorAll('#quotesTable .searchable-row');

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
