<?php
/**
 * Seychelles International Cargo LLC - Advanced Admin Contact Messages Manager
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

// Filter Query
$filter = $_GET['filter'] ?? 'all';
if ($filter === 'pending') {
    $stmt = $db->prepare("SELECT * FROM contacts WHERE status = 'Pending' ORDER BY id DESC");
    $stmt->execute();
} elseif ($filter === 'contacted') {
    $stmt = $db->prepare("SELECT * FROM contacts WHERE status = 'Contacted' ORDER BY id DESC");
    $stmt->execute();
} elseif ($filter === 'archived') {
    $stmt = $db->prepare("SELECT * FROM contacts WHERE status = 'Archived' ORDER BY id DESC");
    $stmt->execute();
} else {
    $stmt = $db->query("SELECT * FROM contacts ORDER BY id DESC");
}
$contacts = $stmt->fetchAll();

// Summary Statistics
$total_count     = $db->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$pending_count   = $db->query("SELECT COUNT(*) FROM contacts WHERE status = 'Pending'")->fetchColumn();
$contacted_count = $db->query("SELECT COUNT(*) FROM contacts WHERE status = 'Contacted'")->fetchColumn();
$archived_count  = $db->query("SELECT COUNT(*) FROM contacts WHERE status = 'Archived'")->fetchColumn();
?>

<!-- Stat Summary Grid -->
<div class="stats-grid">
  <div class="stat-card">
    <div>
      <div class="stat-label">Total Messages</div>
      <div class="stat-val"><?php echo $total_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#EFF6FF; color:#0066FF;">
      <i class="fa-solid fa-envelope"></i>
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
      <div class="stat-label">Contacted / Replied</div>
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
    <a href="contacts.php?filter=all" class="btn-sm <?php echo $filter==='all'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-list me-1"></i> All (<?php echo $total_count; ?>)
    </a>
    <a href="contacts.php?filter=pending" class="btn-sm <?php echo $filter==='pending'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-clock me-1"></i> Pending (<?php echo $pending_count; ?>)
    </a>
    <a href="contacts.php?filter=contacted" class="btn-sm <?php echo $filter==='contacted'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-circle-check me-1"></i> Contacted (<?php echo $contacted_count; ?>)
    </a>
    <a href="contacts.php?filter=archived" class="btn-sm <?php echo $filter==='archived'?'btn-admin-primary':'btn-admin-secondary'; ?>">
      <i class="fa-solid fa-box-archive me-1"></i> Archived (<?php echo $archived_count; ?>)
    </a>
  </div>

  <div style="position:relative; width:100%; max-width:320px;">
    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--admin-muted); font-size:0.85rem;"></i>
    <input type="text" id="adminSearchInput" onkeyup="filterAdminTable()" placeholder="Search sender, email, or phone..." style="width:100%; padding:0.55rem 1rem 0.55rem 2.4rem; border-radius:8px; border:1px solid var(--admin-border); outline:none; font-size:0.88rem; background:#FFFFFF;">
  </div>
</div>

<!-- Main Table Panel -->
<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title">
      <i class="fa-solid fa-envelope me-2 text-primary"></i>Contact Page Messages (<?php echo count($contacts); ?>)
    </h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table" id="contactsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Sender Name</th>
          <th>Contact Info</th>
          <th style="width:38%;">Message Preview</th>
          <th>Status</th>
          <th>Submitted Date</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($contacts)): ?>
          <tr>
            <td colspan="7" style="text-align:center; color:var(--admin-muted); padding:3rem;">
              <i class="fa-solid fa-folder-open me-2" style="font-size:1.5rem;"></i><br>No contact page messages found for this view.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($contacts as $c): 
            $fullName = trim($c['firstname'] . ' ' . $c['lastname']);
            $snippet = mb_strimwidth($c['message'], 0, 65, '...');
            $waPhone = preg_replace('/[^0-9]/', '', $c['phone']);
          ?>
            <tr class="searchable-row">
              <td><strong>#<?php echo $c['id']; ?></strong></td>
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
                  <a href="mailto:<?php echo htmlspecialchars($c['email']); ?>" style="color:#0066FF; text-decoration:none; font-weight:500;">
                    <i class="fa-regular fa-envelope me-1"></i><?php echo htmlspecialchars($c['email']); ?>
                  </a>
                </div>
                <div style="margin-top:0.2rem;">
                  <?php if (!empty($c['phone'])): ?>
                    <a href="tel:<?php echo htmlspecialchars($c['phone']); ?>" style="color:var(--admin-text); text-decoration:none;">
                      <i class="fa-solid fa-phone me-1 text-primary"></i><?php echo htmlspecialchars($c['phone']); ?>
                    </a>
                  <?php else: ?>
                    <span style="color:var(--admin-muted); font-size:0.8rem;">No Phone Provided</span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div style="display:flex; align-items:center; justify-content:space-between; gap:0.5rem; background:#F8FAFC; border:1px solid #E2E8F0; padding:0.45rem 0.75rem; border-radius:6px;">
                  <span style="font-size:0.85rem; color:#475569; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:250px;" title="<?php echo htmlspecialchars($c['message']); ?>">
                    <?php echo htmlspecialchars($snippet); ?>
                  </span>
                  <button type="button" class="btn-sm btn-admin-primary" style="padding:0.25rem 0.55rem; font-size:0.75rem; flex-shrink:0;" 
                          onclick='openContactModal(<?php echo json_encode([
                            "id" => $c["id"],
                            "name" => $fullName,
                            "email" => $c["email"],
                            "phone" => $c["phone"],
                            "message" => $c["message"],
                            "status" => $c["status"],
                            "created_at" => date("M d, Y H:i", strtotime($c["created_at"]))
                          ]); ?>)'>
                    <i class="fa-solid fa-eye me-1"></i> View
                  </button>
                </div>
              </td>
              <td>
                <span class="badge badge-<?php echo strtolower($c['status']); ?>">
                  <i class="fa-solid <?php 
                    if ($c['status']==='Pending') echo 'fa-clock me-1';
                    elseif ($c['status']==='Contacted') echo 'fa-circle-check me-1';
                    else echo 'fa-box-archive me-1';
                  ?>"></i><?php echo htmlspecialchars($c['status']); ?>
                </span>
              </td>
              <td><small style="color:var(--admin-muted);"><?php echo date('M d, Y H:i', strtotime($c['created_at'])); ?></small></td>
              <td style="text-align:right;">
                <div style="display:inline-flex; align-items:center; gap:0.35rem;">
                  <?php if (!empty($waPhone)): ?>
                    <a href="https://wa.me/<?php echo $waPhone; ?>?text=<?php echo urlencode("Hello " . $fullName . ", thank you for contacting Seychelles International Cargo LLC..."); ?>" 
                       target="_blank" class="btn-sm" style="background:#D1FAE5; color:#059669;" title="Chat on WhatsApp">
                      <i class="fa-brands fa-whatsapp"></i>
                    </a>
                  <?php endif; ?>

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
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Interactive Contact Message Detail Modal Dialog -->
<div id="contactModalOverlay" class="admin-modal-overlay">
  <div class="admin-modal-card">
    <div class="admin-modal-header">
      <div class="admin-modal-title">
        <i class="fa-solid fa-envelope-open-text text-accent"></i>
        <span>Contact Message <strong id="modalId">#0</strong></span>
      </div>
      <button type="button" class="admin-modal-close" onclick="closeContactModal()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="admin-modal-body">
      <!-- Sender Information Card -->
      <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:1.25rem; margin-bottom:1.25rem;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.85rem; flex-wrap:wrap; gap:0.5rem;">
          <div>
            <h4 id="modalName" style="font-size:1.15rem; font-weight:700; color:#0F172A; margin-bottom:0.25rem;">Sender Name</h4>
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
        </div>
      </div>

      <!-- Full Formatted Message Body -->
      <div>
        <label style="font-weight:700; font-size:0.85rem; color:#475569; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.5rem; display:block;">
          <i class="fa-solid fa-message me-1 text-primary"></i> Full Message Content:
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
        <form id="modalStatusForm" action="contacts.php" method="POST" style="display:inline-block;">
          <input type="hidden" name="action" value="update_status">
          <input type="hidden" id="modalStatusId" name="id" value="0">
          <select id="modalStatusSelect" name="status" onchange="this.form.submit()" class="btn-sm btn-admin-secondary" style="padding:0.5rem 0.75rem; cursor:pointer;">
            <option value="Pending">Pending</option>
            <option value="Contacted">Contacted</option>
            <option value="Archived">Archived</option>
          </select>
        </form>

        <form id="modalDeleteForm" action="contacts.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this contact message permanently?');">
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
function openContactModal(data) {
  document.getElementById('modalId').innerText = '#' + data.id;
  document.getElementById('modalName').innerText = data.name;
  document.getElementById('modalDate').innerHTML = '<i class="fa-regular fa-clock me-1"></i> ' + data.created_at;
  
  document.getElementById('modalEmail').innerText = data.email;
  document.getElementById('modalEmail').href = 'mailto:' + data.email;
  
  if (data.phone) {
    document.getElementById('modalPhone').innerText = data.phone;
    document.getElementById('modalPhone').href = 'tel:' + data.phone;
  } else {
    document.getElementById('modalPhone').innerText = 'Not Provided';
    document.getElementById('modalPhone').removeAttribute('href');
  }

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
    waBtn.href = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent('Hello ' + data.name + ', thank you for contacting Seychelles International Cargo LLC...');
  } else {
    waBtn.style.display = 'none';
  }

  const emailBtn = document.getElementById('modalEmailBtn');
  emailBtn.href = 'mailto:' + data.email + '?subject=' + encodeURIComponent('Re: Seychelles International Cargo Message #' + data.id);

  document.getElementById('contactModalOverlay').classList.add('open');
}

function closeContactModal() {
  document.getElementById('contactModalOverlay').classList.remove('open');
}

document.getElementById('contactModalOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeContactModal();
});

// Real-time Client Search Filter
function filterAdminTable() {
  const query = document.getElementById('adminSearchInput').value.toLowerCase().trim();
  const rows = document.querySelectorAll('#contactsTable .searchable-row');

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
