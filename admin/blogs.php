<?php
/**
 * Seychelles International Cargo LLC - Advanced Admin Blog Manager
 */
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db_connection();
$msg = '';
$error = '';

// Helper function to create clean slugs
function create_url_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'blog-post-' . time() : $text;
}

// Calculate reading time based on content word count
function calculate_reading_time($text) {
    $words = str_word_count(strip_tags($text));
    $minutes = ceil($words / 200);
    return ($minutes < 1 ? 1 : $minutes) . ' min read';
}

// Handle Image Uploads
function handle_blog_image_upload($file_key, $prefix) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/blogs/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $tmp_name = $_FILES[$file_key]['tmp_name'];
        $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($ext, $allowed)) {
            $new_name = $prefix . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $target_path = $upload_dir . $new_name;
            if (move_uploaded_file($tmp_name, $target_path)) {
                return 'uploads/blogs/' . $new_name;
            }
        }
    }
    return null;
}

// Handle Actions (Add / Edit / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title            = sanitize_input($_POST['title'] ?? '');
        $raw_slug         = sanitize_input($_POST['slug'] ?? '');
        $slug             = !empty($raw_slug) ? create_url_slug($raw_slug) : create_url_slug($title);
        $category         = sanitize_input($_POST['category'] ?? 'Sea Freight');
        $meta_title       = sanitize_input($_POST['meta_title'] ?? $title);
        $meta_description = sanitize_input($_POST['meta_description'] ?? '');
        $meta_keywords    = sanitize_input($_POST['meta_keywords'] ?? '');
        $excerpt          = sanitize_input($_POST['excerpt'] ?? '');
        $content          = $_POST['content'] ?? '';
        $status           = sanitize_input($_POST['status'] ?? 'Published');
        $author           = sanitize_input($_POST['author'] ?? 'Seychelles Cargo Team');
        $read_time        = calculate_reading_time($content);

        // Feature Image
        $uploaded_feature = handle_blog_image_upload('feature_image_file', 'feature');
        $feature_image    = $uploaded_feature ?: sanitize_input($_POST['feature_image_url'] ?? '');

        // Banner Image
        $uploaded_banner  = handle_blog_image_upload('banner_image_file', 'banner');
        $banner_image     = $uploaded_banner ?: sanitize_input($_POST['banner_image_url'] ?? '');

        if (empty($title) || empty($content)) {
            $error = 'Please fill in both Article Title and Article Content.';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO blogs (title, slug, category, read_time, meta_title, meta_description, meta_keywords, feature_image, banner_image, excerpt, content, status, author) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $category, $read_time, $meta_title, $meta_description, $meta_keywords, $feature_image, $banner_image, $excerpt, $content, $status, $author]);
                $msg = 'Blog article published successfully!';
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), 'UNIQUE')) {
                    $error = 'A blog with this URL slug already exists. Please enter a unique slug.';
                } else {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'edit') {
        $id               = intval($_POST['id'] ?? 0);
        $title            = sanitize_input($_POST['title'] ?? '');
        $raw_slug         = sanitize_input($_POST['slug'] ?? '');
        $slug             = !empty($raw_slug) ? create_url_slug($raw_slug) : create_url_slug($title);
        $category         = sanitize_input($_POST['category'] ?? 'Sea Freight');
        $meta_title       = sanitize_input($_POST['meta_title'] ?? $title);
        $meta_description = sanitize_input($_POST['meta_description'] ?? '');
        $meta_keywords    = sanitize_input($_POST['meta_keywords'] ?? '');
        $excerpt          = sanitize_input($_POST['excerpt'] ?? '');
        $content          = $_POST['content'] ?? '';
        $status           = sanitize_input($_POST['status'] ?? 'Published');
        $author           = sanitize_input($_POST['author'] ?? 'Seychelles Cargo Team');
        $read_time        = calculate_reading_time($content);

        $existing_feature = sanitize_input($_POST['existing_feature'] ?? '');
        $existing_banner  = sanitize_input($_POST['existing_banner'] ?? '');

        $uploaded_feature = handle_blog_image_upload('feature_image_file', 'feature');
        $feature_image    = $uploaded_feature ?: (sanitize_input($_POST['feature_image_url'] ?? '') ?: $existing_feature);

        $uploaded_banner  = handle_blog_image_upload('banner_image_file', 'banner');
        $banner_image     = $uploaded_banner ?: (sanitize_input($_POST['banner_image_url'] ?? '') ?: $existing_banner);

        if ($id > 0 && !empty($title) && !empty($content)) {
            try {
                $stmt = $db->prepare("UPDATE blogs SET title = ?, slug = ?, category = ?, read_time = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, feature_image = ?, banner_image = ?, excerpt = ?, content = ?, status = ?, author = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$title, $slug, $category, $read_time, $meta_title, $meta_description, $meta_keywords, $feature_image, $banner_image, $excerpt, $content, $status, $author, $id]);
                $msg = 'Blog article updated successfully!';
            } catch (PDOException $e) {
                $error = 'Error updating article: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM blogs WHERE id = ?");
            $stmt->execute([$id]);
            $msg = 'Blog article deleted!';
        }
    }
}

// Fetch Blog Articles
$filter = $_GET['filter'] ?? 'all';
if ($filter === 'published') {
    $stmt = $db->prepare("SELECT * FROM blogs WHERE status = 'Published' ORDER BY id DESC");
    $stmt->execute();
} elseif ($filter === 'draft') {
    $stmt = $db->prepare("SELECT * FROM blogs WHERE status = 'Draft' ORDER BY id DESC");
    $stmt->execute();
} else {
    $stmt = $db->query("SELECT * FROM blogs ORDER BY id DESC");
}
$blogs = $stmt->fetchAll();

// Statistics
$total_count     = $db->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
$published_count = $db->query("SELECT COUNT(*) FROM blogs WHERE status = 'Published'")->fetchColumn();
$draft_count     = $db->query("SELECT COUNT(*) FROM blogs WHERE status = 'Draft'")->fetchColumn();
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

<!-- Summary Stat Cards -->
<div class="stats-grid">
  <div class="stat-card">
    <div>
      <div class="stat-label">Total Articles</div>
      <div class="stat-val"><?php echo $total_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#EFF6FF; color:#0066FF;">
      <i class="fa-solid fa-newspaper"></i>
    </div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Published Articles</div>
      <div class="stat-val" style="color:#10B981;"><?php echo $published_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#D1FAE5; color:#10B981;">
      <i class="fa-solid fa-globe"></i>
    </div>
  </div>

  <div class="stat-card">
    <div>
      <div class="stat-label">Drafts</div>
      <div class="stat-val" style="color:#D97706;"><?php echo $draft_count; ?></div>
    </div>
    <div class="stat-icon" style="background:#FEF3C7; color:#D97706;">
      <i class="fa-solid fa-file-pen"></i>
    </div>
  </div>
</div>

<!-- Controls Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
  <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
    <button type="button" class="btn-sm btn-admin-primary" onclick="openAddBlogModal()" style="padding:0.6rem 1.25rem; font-weight:700;">
      <i class="fa-solid fa-plus me-1"></i> Write New Blog Article
    </button>

    <div style="display:inline-flex; gap:0.35rem; margin-left:0.5rem;">
      <a href="blogs.php?filter=all" class="btn-sm <?php echo $filter==='all'?'btn-admin-primary':'btn-admin-secondary'; ?>">
        <i class="fa-solid fa-list me-1"></i> All (<?php echo $total_count; ?>)
      </a>
      <a href="blogs.php?filter=published" class="btn-sm <?php echo $filter==='published'?'btn-admin-primary':'btn-admin-secondary'; ?>">
        <i class="fa-solid fa-globe me-1"></i> Published (<?php echo $published_count; ?>)
      </a>
      <a href="blogs.php?filter=draft" class="btn-sm <?php echo $filter==='draft'?'btn-admin-primary':'btn-admin-secondary'; ?>">
        <i class="fa-solid fa-file-pen me-1"></i> Drafts (<?php echo $draft_count; ?>)
      </a>
    </div>
  </div>

  <div style="position:relative; width:100%; max-width:320px;">
    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--admin-muted); font-size:0.85rem;"></i>
    <input type="text" id="adminSearchInput" onkeyup="filterAdminTable()" placeholder="Search title, category, or slug..." style="width:100%; padding:0.55rem 1rem 0.55rem 2.4rem; border-radius:8px; border:1px solid var(--admin-border); outline:none; font-size:0.88rem; background:#FFFFFF;">
  </div>
</div>

<!-- Blog Articles Table -->
<div class="panel-card">
  <div class="panel-header">
    <h3 class="panel-title">
      <i class="fa-solid fa-newspaper me-2 text-primary"></i>All Blog Articles (<?php echo count($blogs); ?>)
    </h3>
  </div>
  <div class="table-responsive">
    <table class="admin-table" id="blogsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Preview Image</th>
          <th>Article Title & Slug</th>
          <th>Category</th>
          <th>Reading Time</th>
          <th>Status</th>
          <th>Date</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($blogs)): ?>
          <tr>
            <td colspan="8" style="text-align:center; color:var(--admin-muted); padding:3rem;">
              <i class="fa-solid fa-folder-open me-2" style="font-size:1.5rem;"></i><br>No blog articles found. Click <strong>"Write New Blog Article"</strong> above to publish your first post!
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($blogs as $b): 
            $imgSrc = !empty($b['feature_image']) ? (str_starts_with($b['feature_image'], 'http') ? $b['feature_image'] : '../' . $b['feature_image']) : '../images/backgrounds/chinaseychellescargo.jpg';
          ?>
            <tr class="searchable-row">
              <td><strong>#<?php echo $b['id']; ?></strong></td>
              <td>
                <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Preview" style="width:52px; height:38px; object-fit:cover; border-radius:6px; border:1px solid #CBD5E1; background:#0F172A;">
              </td>
              <td>
                <strong style="display:block; color:#0F172A; font-size:0.95rem;"><?php echo htmlspecialchars($b['title']); ?></strong>
                <a href="../blogs/<?php echo htmlspecialchars($b['slug']); ?>" target="_blank" style="color:#0066FF; font-size:0.78rem; text-decoration:none; font-family:monospace;">
                  <i class="fa-solid fa-link me-1"></i>/blogs/<?php echo htmlspecialchars($b['slug']); ?>
                </a>
              </td>
              <td>
                <span class="badge" style="background:#EFF6FF; color:#0066FF;">
                  <i class="fa-solid fa-tag me-1"></i><?php echo htmlspecialchars($b['category'] ?: 'Sea Freight'); ?>
                </span>
              </td>
              <td><small><i class="fa-regular fa-clock me-1"></i><?php echo htmlspecialchars($b['read_time'] ?: '5 min read'); ?></small></td>
              <td>
                <span class="badge badge-<?php echo strtolower($b['status']) === 'published' ? 'quoted' : 'pending'; ?>">
                  <i class="fa-solid <?php echo strtolower($b['status']) === 'published' ? 'fa-globe me-1' : 'fa-file-pen me-1'; ?>"></i>
                  <?php echo htmlspecialchars($b['status']); ?>
                </span>
              </td>
              <td><small style="color:var(--admin-muted);"><?php echo date('M d, Y', strtotime($b['created_at'])); ?></small></td>
              <td style="text-align:right; white-space:nowrap;">
                <div style="display:inline-flex; align-items:center; gap:0.35rem;">
                  <!-- View Live Article -->
                  <a href="../blogs/<?php echo htmlspecialchars($b['slug']); ?>" target="_blank" class="btn-sm btn-admin-primary" style="background:#0F172A; color:#FFFFFF;" title="View Live Page">
                    <i class="fa-solid fa-eye"></i>
                  </a>

                  <!-- Edit Article Button -->
                  <button type="button" class="btn-sm btn-admin-primary" onclick='openEditBlogModal(<?php echo json_encode($b); ?>)' title="Edit Article">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                  </button>

                  <!-- Delete Form -->
                  <form action="blogs.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this blog article permanently?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                    <button type="submit" class="btn-sm btn-admin-danger" title="Delete Article">
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

<!-- Modal Popup for Writing New Blog Article -->
<div id="addBlogModalOverlay" class="admin-modal-overlay">
  <div class="admin-modal-card" style="max-width:880px;">
    <div class="admin-modal-header">
      <div class="admin-modal-title">
        <i class="fa-solid fa-newspaper text-accent"></i>
        <span>Publish New Blog Article</span>
      </div>
      <button type="button" class="admin-modal-close" onclick="closeAddBlogModal()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="blogs.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">

      <div class="admin-modal-body" style="max-height:75vh; overflow-y:auto; padding:1.5rem;">
        
        <!-- Basic Info -->
        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:1.25rem; margin-bottom:1.25rem;">
          <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Article Title *</label>
            <input type="text" name="title" id="add_title" onkeyup="autoGenerateSlug(this.value, 'add_slug')" placeholder="e.g. Dubai to Seychelles Shipping: Jebel Ali Cargo Guide" class="form-control" style="width:100%; font-size:1.05rem; font-weight:600; padding:0.65rem;" required>
          </div>

          <div style="display:grid; grid-template-columns:2fr 1.2fr 1fr 1fr; gap:1rem;">
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">URL Slug (/blogs/slug) *</label>
              <input type="text" name="slug" id="add_slug" placeholder="dubai-to-seychelles-shipping-guide" class="form-control" style="width:100%; font-family:monospace; font-size:0.88rem; padding:0.6rem;">
            </div>
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Category *</label>
              <select name="category" class="form-control" style="width:100%; padding:0.6rem;">
                <option value="Seychelles Sector">Seychelles Sector</option>
                <option value="Sea Freight">Sea Freight</option>
                <option value="LCL Consolidation">LCL Consolidation</option>
                <option value="Air Freight">Air Freight</option>
                <option value="Customs & Clearance">Customs & Clearance</option>
                <option value="Door to Door">Door to Door</option>
              </select>
            </div>
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Author</label>
              <input type="text" name="author" value="Seychelles Cargo Team" class="form-control" style="width:100%; padding:0.6rem;">
            </div>
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Publish Status</label>
              <select name="status" class="form-control" style="width:100%; padding:0.6rem;">
                <option value="Published">Published</option>
                <option value="Draft">Draft</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Feature & Banner Images -->
        <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:10px; padding:1.25rem; margin-bottom:1.25rem;">
          <h4 style="font-size:0.95rem; color:#0066FF; font-weight:700; margin-bottom:1rem;">
            <i class="fa-solid fa-image me-1"></i>Feature Image & Social Media Preview Images
          </h4>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#1E293B; margin-bottom:0.35rem;">
                1. Feature / Social Preview Image (Upload File)
              </label>
              <input type="file" name="feature_image_file" accept="image/*" class="form-control" style="width:100%; padding:0.5rem; background:#FFFFFF; font-size:0.85rem;">
              <small style="color:#64748B; display:block; margin-top:0.3rem;">Or paste existing image URL below:</small>
              <input type="text" name="feature_image_url" placeholder="e.g. images/backgrounds/chinaseychellescargo.jpg" class="form-control" style="width:100%; font-size:0.82rem; margin-top:0.3rem;">
            </div>

            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#1E293B; margin-bottom:0.35rem;">
                2. Header Banner Image (Upload File)
              </label>
              <input type="file" name="banner_image_file" accept="image/*" class="form-control" style="width:100%; padding:0.5rem; background:#FFFFFF; font-size:0.85rem;">
              <small style="color:#64748B; display:block; margin-top:0.3rem;">Or paste existing banner URL below:</small>
              <input type="text" name="banner_image_url" placeholder="e.g. images/backgrounds/chinaseychellescargo.jpg" class="form-control" style="width:100%; font-size:0.82rem; margin-top:0.3rem;">
            </div>
          </div>
        </div>

        <!-- SEO Metadata -->
        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:1.25rem; margin-bottom:1.25rem;">
          <h4 style="font-size:0.95rem; color:#334155; font-weight:700; margin-bottom:1rem;">
            <i class="fa-solid fa-magnifying-glass me-1 text-primary"></i>SEO Search Engine Metadata
          </h4>

          <div style="margin-bottom:0.85rem;">
            <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.3rem;">SEO Meta Title</label>
            <input type="text" name="meta_title" placeholder="SEO Title for Google Search..." class="form-control" style="width:100%; padding:0.6rem;">
          </div>

          <div style="margin-bottom:0.85rem;">
            <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.3rem;">SEO Meta Description</label>
            <textarea name="meta_description" rows="2" placeholder="Summary snippet for search engine results..." class="form-control" style="width:100%; padding:0.6rem; font-size:0.88rem;"></textarea>
          </div>

          <div>
            <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.3rem;">SEO Meta Keywords (Comma separated)</label>
            <input type="text" name="meta_keywords" placeholder="Ship cargo Jebel Ali, Port Victoria sea freight, LCL Seychelles..." class="form-control" style="width:100%; padding:0.6rem; font-size:0.85rem;">
          </div>
        </div>

        <!-- Excerpt & Content -->
        <div>
          <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Article Summary / Excerpt</label>
            <textarea name="excerpt" rows="2" placeholder="Short executive summary for blog cards..." class="form-control" style="width:100%; padding:0.65rem;"></textarea>
          </div>

          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.35rem;">
              <label style="font-size:0.85rem; font-weight:700; color:#334155;">Full Article Content *</label>
              <!-- Formatting Helper Toolbar -->
              <div style="display:flex; gap:0.25rem;">
                <button type="button" class="btn-sm btn-admin-secondary" onclick="insertTag('add_content', '<h2>', '</h2>')" title="H2 Heading"><i class="fa-solid fa-heading"></i>2</button>
                <button type="button" class="btn-sm btn-admin-secondary" onclick="insertTag('add_content', '<h3>', '3</h2>')" title="H3 Heading"><i class="fa-solid fa-heading"></i>3</button>
                <button type="button" class="btn-sm btn-admin-secondary" onclick="insertTag('add_content', '<strong>', '自由')" title="Bold"><i class="fa-solid fa-bold"></i></button>
                <button type="button" class="btn-sm btn-admin-secondary" onclick="insertTag('add_content', '<p>', '</p>')" title="Paragraph"><i class="fa-solid fa-paragraph"></i></button>
                <button type="button" class="btn-sm btn-admin-secondary" onclick="insertTag('add_content', '<ul>\n  <li>', '</li>\n</ul>')" title="Bullet List"><i class="fa-solid fa-list-ul"></i></button>
              </div>
            </div>
            <textarea name="content" id="add_content" rows="12" placeholder="Write or paste your article HTML content here..." class="form-control" style="width:100%; padding:0.75rem; font-family:var(--font-body); font-size:0.95rem; line-height:1.6;" required></textarea>
          </div>
        </div>

      </div>

      <div class="admin-modal-footer" style="justify-content:flex-end;">
        <button type="button" class="btn-sm btn-admin-secondary" onclick="closeAddBlogModal()">Cancel</button>
        <button type="submit" class="btn-sm btn-admin-primary" style="padding:0.65rem 1.5rem;">
          <i class="fa-solid fa-paper-plane me-1"></i> Publish Article
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Popup for Editing Blog Article -->
<div id="editBlogModalOverlay" class="admin-modal-overlay">
  <div class="admin-modal-card" style="max-width:880px;">
    <div class="admin-modal-header">
      <div class="admin-modal-title">
        <i class="fa-solid fa-pen-to-square text-accent"></i>
        <span>Edit Blog Article <strong id="edit_modal_id">#0</strong></span>
      </div>
      <button type="button" class="admin-modal-close" onclick="closeEditBlogModal()">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form action="blogs.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="edit_id">
      <input type="hidden" name="existing_feature" id="edit_existing_feature">
      <input type="hidden" name="existing_banner" id="edit_existing_banner">

      <div class="admin-modal-body" style="max-height:75vh; overflow-y:auto; padding:1.5rem;">
        
        <!-- Basic Info -->
        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:1.25rem; margin-bottom:1.25rem;">
          <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Article Title *</label>
            <input type="text" name="title" id="edit_title" class="form-control" style="width:100%; font-size:1.05rem; font-weight:600; padding:0.65rem;" required>
          </div>

          <div style="display:grid; grid-template-columns:2fr 1.2fr 1fr 1fr; gap:1rem;">
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">URL Slug (/blogs/slug) *</label>
              <input type="text" name="slug" id="edit_slug" class="form-control" style="width:100%; font-family:monospace; font-size:0.88rem; padding:0.6rem;" required>
            </div>
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Category *</label>
              <select name="category" id="edit_category" class="form-control" style="width:100%; padding:0.6rem;">
                <option value="Seychelles Sector">Seychelles Sector</option>
                <option value="Sea Freight">Sea Freight</option>
                <option value="LCL Consolidation">LCL Consolidation</option>
                <option value="Air Freight">Air Freight</option>
                <option value="Customs & Clearance">Customs & Clearance</option>
                <option value="Door to Door">Door to Door</option>
              </select>
            </div>
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Author</label>
              <input type="text" name="author" id="edit_author" class="form-control" style="width:100%; padding:0.6rem;">
            </div>
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Publish Status</label>
              <select name="status" id="edit_status" class="form-control" style="width:100%; padding:0.6rem;">
                <option value="Published">Published</option>
                <option value="Draft">Draft</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Images -->
        <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:10px; padding:1.25rem; margin-bottom:1.25rem;">
          <h4 style="font-size:0.95rem; color:#0066FF; font-weight:700; margin-bottom:1rem;">
            <i class="fa-solid fa-image me-1"></i>Feature Image & Social Media Preview Images
          </h4>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#1E293B; margin-bottom:0.35rem;">
                Replace Feature / Social Image (Upload File)
              </label>
              <input type="file" name="feature_image_file" accept="image/*" class="form-control" style="width:100%; padding:0.5rem; background:#FFFFFF; font-size:0.85rem;">
              <small style="color:#64748B; display:block; margin-top:0.3rem;">Or update Image URL:</small>
              <input type="text" name="feature_image_url" id="edit_feature_image_url" class="form-control" style="width:100%; font-size:0.82rem; margin-top:0.3rem;">
            </div>

            <div>
              <label style="display:block; font-size:0.82rem; font-weight:700; color:#1E293B; margin-bottom:0.35rem;">
                Replace Banner Image (Upload File)
              </label>
              <input type="file" name="banner_image_file" accept="image/*" class="form-control" style="width:100%; padding:0.5rem; background:#FFFFFF; font-size:0.85rem;">
              <small style="color:#64748B; display:block; margin-top:0.3rem;">Or update Banner URL:</small>
              <input type="text" name="banner_image_url" id="edit_banner_image_url" class="form-control" style="width:100%; font-size:0.82rem; margin-top:0.3rem;">
            </div>
          </div>
        </div>

        <!-- SEO Metadata -->
        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:10px; padding:1.25rem; margin-bottom:1.25rem;">
          <h4 style="font-size:0.95rem; color:#334155; font-weight:700; margin-bottom:1rem;">
            <i class="fa-solid fa-magnifying-glass me-1 text-primary"></i>SEO Search Engine Metadata
          </h4>

          <div style="margin-bottom:0.85rem;">
            <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.3rem;">SEO Meta Title</label>
            <input type="text" name="meta_title" id="edit_meta_title" class="form-control" style="width:100%; padding:0.6rem;">
          </div>

          <div style="margin-bottom:0.85rem;">
            <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.3rem;">SEO Meta Description</label>
            <textarea name="meta_description" id="edit_meta_description" rows="2" class="form-control" style="width:100%; padding:0.6rem; font-size:0.88rem;"></textarea>
          </div>

          <div>
            <label style="display:block; font-size:0.82rem; font-weight:700; color:#334155; margin-bottom:0.3rem;">SEO Meta Keywords</label>
            <input type="text" name="meta_keywords" id="edit_meta_keywords" class="form-control" style="width:100%; padding:0.6rem; font-size:0.85rem;">
          </div>
        </div>

        <!-- Content -->
        <div>
          <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.35rem;">Article Summary / Excerpt</label>
            <textarea name="excerpt" id="edit_excerpt" rows="2" class="form-control" style="width:100%; padding:0.65rem;"></textarea>
          </div>

          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.35rem;">
              <label style="font-size:0.85rem; font-weight:700; color:#334155;">Full Article Content *</label>
              <div style="display:flex; gap:0.25rem;">
                <button type="button" class="btn-sm btn-admin-secondary" onclick="insertTag('edit_content', '<h2>', '</h2>')">H2</button>
                <button type="button" class="btn-sm btn-admin-secondary" onclick="insertTag('edit_content', '<h3>', '</h3>')">H3</button>
                <button type="button" class="btn-sm btn-admin-secondary" onclick="insertTag('edit_content', '<strong>', '</strong>')">Bold</button>
                <button type="button" class="btn-sm btn-admin-secondary" onclick="insertTag('edit_content', '<p>', '</p>')">P</button>
              </div>
            </div>
            <textarea name="content" id="edit_content" rows="12" class="form-control" style="width:100%; padding:0.75rem; font-family:var(--font-body); font-size:0.95rem; line-height:1.6;" required></textarea>
          </div>
        </div>

      </div>

      <div class="admin-modal-footer" style="justify-content:flex-end;">
        <button type="button" class="btn-sm btn-admin-secondary" onclick="closeEditBlogModal()">Cancel</button>
        <button type="submit" class="btn-sm btn-admin-primary" style="padding:0.65rem 1.5rem;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddBlogModal() {
  document.getElementById('addBlogModalOverlay').classList.add('open');
}
function closeAddBlogModal() {
  document.getElementById('addBlogModalOverlay').classList.remove('open');
}

function openEditBlogModal(data) {
  document.getElementById('edit_modal_id').innerText = '#' + data.id;
  document.getElementById('edit_id').value = data.id;
  document.getElementById('edit_title').value = data.title;
  document.getElementById('edit_slug').value = data.slug;
  document.getElementById('edit_category').value = data.category || 'Sea Freight';
  document.getElementById('edit_author').value = data.author || 'Seychelles Cargo Team';
  document.getElementById('edit_status').value = data.status;
  
  document.getElementById('edit_existing_feature').value = data.feature_image || '';
  document.getElementById('edit_existing_banner').value = data.banner_image || '';
  document.getElementById('edit_feature_image_url').value = data.feature_image || '';
  document.getElementById('edit_banner_image_url').value = data.banner_image || '';

  document.getElementById('edit_meta_title').value = data.meta_title || '';
  document.getElementById('edit_meta_description').value = data.meta_description || '';
  document.getElementById('edit_meta_keywords').value = data.meta_keywords || '';
  document.getElementById('edit_excerpt').value = data.excerpt || '';
  document.getElementById('edit_content').value = data.content || '';

  document.getElementById('editBlogModalOverlay').classList.add('open');
}
function closeEditBlogModal() {
  document.getElementById('editBlogModalOverlay').classList.remove('open');
}

function autoGenerateSlug(text, targetId) {
  const target = document.getElementById(targetId);
  if (!target.dataset.userEdited) {
    const slug = text.toLowerCase()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
    target.value = slug;
  }
}

function insertTag(textareaId, startTag, endTag) {
  const txt = document.getElementById(textareaId);
  const start = txt.selectionStart;
  const end = txt.selectionEnd;
  const sel = txt.value.substring(start, end);
  txt.value = txt.value.substring(0, start) + startTag + sel + endTag + txt.value.substring(end);
  txt.focus();
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
  const rows = document.querySelectorAll('#blogsTable .searchable-row');

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
