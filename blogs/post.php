<?php
/**
 * Seychelles International Cargo LLC - Structural Single Blog Post Viewer
 * URL Pattern: /blogs/<slug> or /blogs/post.php?slug=<slug>
 */
require_once __DIR__ . '/../includes/db.php';
$db = get_db_connection();

$slug = sanitize_input($_GET['slug'] ?? '');

// Handle root SEO guide fallback
if (empty($slug) || $slug === 'ship-cargo-jebel-ali-dubai-to-port-victoria-seychelles') {
    require_once __DIR__ . '/../ship-cargo-jebel-ali-dubai-to-port-victoria-seychelles.html';
    exit;
}

// Fetch Article from DB by Slug
$stmt = $db->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'Published'");
$stmt->execute([$slug]);
$blog = $stmt->fetch();

if (!$blog) {
    // Fallback if post not found
    header('Location: index.php');
    exit;
}

// Increment Views Count
try {
    $db->prepare("UPDATE blogs SET views_count = views_count + 1 WHERE id = ?")->execute([$blog['id']]);
} catch (Exception $e) {}

// SEO Metadata
$page_title       = !empty($blog['meta_title']) ? $blog['meta_title'] : $blog['title'] . " | Seychelles Cargo Blog";
$page_description = !empty($blog['meta_description']) ? $blog['meta_description'] : mb_strimwidth(strip_tags($blog['excerpt'] ?: $blog['content']), 0, 160, '...');
$page_keywords    = !empty($blog['meta_keywords']) ? $blog['meta_keywords'] : "Seychelles Cargo Blog, Dubai Freight Guide, Sea Freight";

$feature_img = !empty($blog['feature_image']) ? (str_starts_with($blog['feature_image'], 'http') ? $blog['feature_image'] : 'https://seychellescargo.com/' . ltrim($blog['feature_image'], '/')) : 'https://seychellescargo.com/images/backgrounds/chinaseychellescargo.jpg';
$banner_img  = !empty($blog['banner_image']) ? (str_starts_with($blog['banner_image'], 'http') ? $blog['banner_image'] : '../' . ltrim($blog['banner_image'], '/')) : '../images/backgrounds/chinaseychellescargo.jpg';

// Fetch Related Articles in same category
$rel_stmt = $db->prepare("SELECT * FROM blogs WHERE category = ? AND id != ? AND status = 'Published' LIMIT 3");
$rel_stmt->execute([$blog['category'] ?? 'Sea Freight', $blog['id']]);
$related_posts = $rel_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Social Media Open Graph & Twitter Cards -->
<meta property="og:type" content="article">
<meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($feature_img); ?>">
<meta property="og:url" content="https://seychellescargo.com/blogs/<?php echo htmlspecialchars($blog['slug']); ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($feature_img); ?>">

<!-- Page Hero Banner -->
<section class="page-banner" style="background: linear-gradient(180deg, rgba(9,13,22,0.7) 0%, rgba(9,13,22,0.94) 100%), url('<?php echo htmlspecialchars($banner_img); ?>') center/cover no-repeat; padding: 4.5rem 0 3.5rem 0;">
  <div class="container" style="position:relative; z-index:10;">
    <div style="display:flex; gap:0.75rem; align-items:center; margin-bottom:0.85rem; flex-wrap:wrap;">
      <span class="badge" style="background:#FF7A00; color:#FFFFFF; font-size:0.78rem; font-weight:800; text-transform:uppercase; padding:0.25rem 0.65rem; border-radius:4px;">
        <i class="fa-solid fa-tag me-1"></i> <?php echo htmlspecialchars($blog['category'] ?: 'Sea Freight'); ?>
      </span>
      <span style="color:#CBD5E1; font-size:0.85rem;">
        <i class="fa-regular fa-clock me-1 text-accent"></i> <?php echo htmlspecialchars($blog['read_time'] ?: '5 min read'); ?>
      </span>
    </div>
    
    <h1 class="page-banner-title" style="font-size:2.5rem; max-width:850px; margin-bottom:1rem; line-height:1.25;">
      <?php echo htmlspecialchars($blog['title']); ?>
    </h1>
    
    <div class="breadcrumbs">
      <a href="../index.html">Home</a> &rsaquo; 
      <a href="index.php">Blogs</a> &rsaquo; 
      <span><?php echo htmlspecialchars($blog['title']); ?></span>
    </div>
  </div>
</section>

<!-- Main Article Content Section -->
<section class="section" style="background:#FFFFFF;">
  <div class="container">
    <div class="grid-subpage">
      
      <!-- Left Column: Article Body & Components -->
      <article style="line-height:1.8; font-size:1.02rem; color:#334155;">
        
        <!-- Author Metadata & Social Share Bar -->
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #E2E8F0; padding-bottom:1rem; margin-bottom:2rem; font-size:0.88rem; color:#64748B; flex-wrap:wrap; gap:1rem;">
          <div style="display:flex; align-items:center; gap:0.6rem;">
            <div style="width:36px; height:36px; border-radius:50%; background:#EFF6FF; color:#0066FF; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.9rem;">
              <i class="fa-solid fa-user"></i>
            </div>
            <div>
              <strong style="color:#0F172A; display:block;"><?php echo htmlspecialchars($blog['author']); ?></strong>
              <small>Published <?php echo date('M d, Y', strtotime($blog['created_at'])); ?></small>
            </div>
          </div>

          <!-- Social Share Buttons -->
          <div style="display:flex; gap:0.4rem; align-items:center;">
            <span style="font-size:0.8rem; font-weight:700; color:#64748B; margin-right:0.3rem;">Share:</span>
            <a href="https://wa.me/?text=<?php echo urlencode($blog['title'] . ' - https://seychellescargo.com/blogs/' . $blog['slug']); ?>" target="_blank" class="btn-sm" style="background:#25D366; color:#FFFFFF; border-radius:6px; padding:0.35rem 0.65rem;" title="Share on WhatsApp">
              <i class="fa-brands fa-whatsapp"></i>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://seychellescargo.com/blogs/' . $blog['slug']); ?>" target="_blank" class="btn-sm" style="background:#1877F2; color:#FFFFFF; border-radius:6px; padding:0.35rem 0.65rem;" title="Share on Facebook">
              <i class="fa-brands fa-facebook"></i>
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://seychellescargo.com/blogs/' . $blog['slug']); ?>" target="_blank" class="btn-sm" style="background:#0A66C2; color:#FFFFFF; border-radius:6px; padding:0.35rem 0.65rem;" title="Share on LinkedIn">
              <i class="fa-brands fa-linkedin"></i>
            </a>
            <button type="button" onclick="copyArticleLink()" class="btn-sm btn-admin-secondary" style="border-radius:6px; padding:0.35rem 0.65rem;" title="Copy Article Link">
              <i class="fa-solid fa-link"></i>
            </button>
          </div>
        </div>

        <!-- Executive Summary Callout Box -->
        <?php if (!empty($blog['excerpt'])): ?>
          <div style="background:#F8FAFC; border-left:4px solid var(--color-primary); border-radius:0 12px 12px 0; padding:1.5rem; margin-bottom:2rem; border-top:1px solid #E2E8F0; border-right:1px solid #E2E8F0; border-bottom:1px solid #E2E8F0;">
            <div style="font-size:0.8rem; font-weight:800; color:var(--color-primary); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:0.4rem;">
              <i class="fa-solid fa-lightbulb me-1 text-accent"></i> Executive Summary / Key Takeaways
            </div>
            <p style="margin-bottom:0; font-size:1.05rem; color:#0F172A; font-weight:500; line-height:1.65;">
              <?php echo htmlspecialchars($blog['excerpt']); ?>
            </p>
          </div>
        <?php endif; ?>

        <!-- Automatic Table of Contents Container -->
        <div id="tableOfContentsBox" style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:12px; padding:1.25rem 1.5rem; margin-bottom:2rem; display:none;">
          <h4 style="color:#0066FF; font-size:1.05rem; margin-bottom:0.75rem; font-weight:700;">
            <i class="fa-solid fa-list-ol me-2"></i>Table of Contents
          </h4>
          <ul id="tocList" style="padding-left:1.25rem; margin:0; line-height:1.8; font-size:0.92rem;"></ul>
        </div>

        <!-- Article Body Content -->
        <div class="blog-body-content" id="articleBodyContent">
          <?php echo $blog['content']; ?>
        </div>

        <!-- Author Bio Card -->
        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:1.75rem; margin:3rem 0; display:flex; gap:1.25rem; align-items:center;">
          <div style="width:60px; height:60px; border-radius:50%; background:#0F172A; color:#FF7A00; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0;">
            <i class="fa-solid fa-user-gear"></i>
          </div>
          <div>
            <h4 style="font-size:1.1rem; color:#0F172A; margin-bottom:0.35rem; font-weight:700;">
              Written by <?php echo htmlspecialchars($blog['author']); ?>
            </h4>
            <p style="color:#64748B; font-size:0.9rem; line-height:1.5; margin:0;">
              Logistics experts at Seychelles International Cargo LLC specializing in Middle East sea freight, LCL consolidation, customs clearance, and Indian Ocean island transport.
            </p>
          </div>
        </div>

        <!-- Related Articles Grid -->
        <?php if (!empty($related_posts)): ?>
          <div style="margin-top:3rem; border-top:2px solid #F1F5F9; padding-top:2rem;">
            <h3 style="font-size:1.4rem; color:var(--color-secondary); margin-bottom:1.5rem;">
              <i class="fa-solid fa-newspaper me-2 text-primary"></i>Related Shipping Guides
            </h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem;">
              <?php foreach ($related_posts as $rp): 
                $rImg = !empty($rp['feature_image']) ? (str_starts_with($rp['feature_image'], 'http') ? $rp['feature_image'] : '../' . $rp['feature_image']) : '../images/backgrounds/chinaseychellescargo.jpg';
              ?>
                <div class="card" style="padding:1rem; border-radius:10px; border:1px solid #E2E8F0;">
                  <img src="<?php echo htmlspecialchars($rImg); ?>" alt="<?php echo htmlspecialchars($rp['title']); ?>" style="width:100%; height:130px; object-fit:cover; border-radius:6px; margin-bottom:0.75rem;">
                  <h4 style="font-size:0.95rem; margin-bottom:0.5rem; line-height:1.4;">
                    <a href="<?php echo htmlspecialchars($rp['slug']); ?>" style="color:#0F172A; text-decoration:none;">
                      <?php echo htmlspecialchars($rp['title']); ?>
                    </a>
                  </h4>
                  <a href="<?php echo htmlspecialchars($rp['slug']); ?>" style="color:#0066FF; font-size:0.82rem; font-weight:600; text-decoration:none;">Read Guide &rarr;</a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

      </article>

      <!-- Right Column: Sticky Quote Form & Contact Desk -->
      <aside>
        
        <!-- Sticky Rate Inquiry Card -->
        <div class="card" style="padding:1.75rem; position:sticky; top:95px; margin-bottom:1.75rem; box-shadow:0 10px 30px rgba(0,0,0,0.06);">
          <h3 style="font-size:1.25rem; margin-bottom:0.4rem; color:var(--color-secondary);">
            <i class="fa-solid fa-paper-plane text-primary me-2"></i>Inquire Freight Rate
          </h3>
          <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1.25rem;">
            Request a shipment-specific quote for ocean freight and LCL consolidation.
          </p>

          <form action="../request.php" method="POST" data-ajax="true">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="recipient" value="Blog Article Inquiry: <?php echo htmlspecialchars($blog['title']); ?>">

            <div class="form-group" style="margin-bottom:0.85rem;">
              <label class="form-label" style="font-size:0.82rem;">Your Name *</label>
              <input type="text" name="name" class="form-control" placeholder="Full name" style="padding:0.6rem;" required>
            </div>

            <div class="form-group" style="margin-bottom:0.85rem;">
              <label class="form-label" style="font-size:0.82rem;">Phone / WhatsApp *</label>
              <input type="tel" name="contact" class="form-control" placeholder="+971 50 123 4567" style="padding:0.6rem;" required>
            </div>

            <div class="form-group" style="margin-bottom:0.85rem;">
              <label class="form-label" style="font-size:0.82rem;">Destination Sector</label>
              <input type="text" name="destination" class="form-control" value="Seychelles / Global" style="padding:0.6rem;" required>
            </div>

            <div class="form-group" style="margin-bottom:1rem;">
              <label class="form-label" style="font-size:0.82rem;">Cargo Details / CBM *</label>
              <textarea name="message" class="form-control" rows="3" placeholder="e.g. 2 CBM / 5 boxes / commercial goods" style="padding:0.6rem; font-size:0.88rem;" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding:0.65rem;">
              <i class="fa-solid fa-calculator me-1"></i> Get Instant Quote
            </button>
          </form>
        </div>

        <!-- Direct WhatsApp Support Card -->
        <div style="background:#0F172A; color:#FFFFFF; border-radius:14px; padding:1.5rem;">
          <h4 style="color:#FFFFFF; font-size:1.05rem; margin-bottom:0.75rem;">
            <i class="fa-solid fa-headset text-accent me-2"></i>Dubai Cargo Desk
          </h4>
          <div style="display:flex; flex-direction:column; gap:0.75rem; font-size:0.88rem; color:#CBD5E1;">
            <div><i class="fa-solid fa-phone text-primary me-2"></i><a href="tel:+97143550903" style="color:#60A5FA;">+971 4 3550903</a></div>
            <div><i class="fa-brands fa-whatsapp text-success me-2"></i><a href="https://wa.me/971552038001" target="_blank" style="color:#34D399;">+971 55 203 8001</a></div>
            <div><i class="fa-regular fa-envelope text-primary me-2"></i><a href="mailto:sales@seychellescargo.com" style="color:#60A5FA;">sales@seychellescargo.com</a></div>
          </div>
        </div>

      </aside>

    </div>
  </div>
</section>

<script>
// Auto-Generate Table of Contents from H2 & H3 tags
document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('articleBodyContent');
  const headings = container.querySelectorAll('h2, h3');
  const tocBox = document.getElementById('tableOfContentsBox');
  const tocList = document.getElementById('tocList');

  if (headings.length >= 2) {
    tocBox.style.display = 'block';
    headings.forEach((h, index) => {
      const id = 'section-heading-' + index;
      h.id = id;
      const li = document.createElement('li');
      const a = document.createElement('a');
      a.href = '#' + id;
      a.innerText = h.innerText;
      a.style.color = '#0066FF';
      a.style.textDecoration = 'none';
      if (h.tagName.toLowerCase() === 'h3') {
        li.style.marginLeft = '1rem';
      }
      li.appendChild(a);
      tocList.appendChild(li);
    });
  }
});

function copyArticleLink() {
  navigator.clipboard.writeText(window.location.href);
  alert('Article link copied to clipboard!');
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
