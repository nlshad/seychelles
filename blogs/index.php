<?php
/**
 * Seychelles International Cargo LLC - Structural Blog & Knowledge Hub
 * URL: /blogs/ or /blogs/index.php
 */
require_once __DIR__ . '/../includes/db.php';
$db = get_db_connection();

$page_title = "Logistics Blogs & Trade Shipping Guides | Seychelles International Cargo LLC";
$page_description = "Explore expert shipping guides, sea freight consolidation advice, customs clearance tips (SRC/ASYCUDA), and sector insights connecting Dubai to Seychelles, Mauritius, Zanzibar & beyond.";
$page_keywords = "Seychelles Cargo Blogs, Dubai Shipping Guides, Jebel Ali Cargo News, Sea Freight Articles, LCL Consolidation Seychelles";

// Fetch All Published Blogs
$stmt = $db->prepare("SELECT * FROM blogs WHERE status = 'Published' ORDER BY id DESC");
$stmt->execute();
$db_blogs = $stmt->fetchAll();

// Category List
$categories = ['All Articles', 'Seychelles Sector', 'Sea Freight', 'LCL Consolidation', 'Air Freight', 'Customs & Clearance', 'Door to Door'];
$active_cat = sanitize_input($_GET['category'] ?? 'All Articles');

// Filter by category if specified
$blogs = [];
foreach ($db_blogs as $b) {
    if ($active_cat === 'All Articles' || strtolower($b['category'] ?? '') === strtolower($active_cat)) {
        $blogs[] = $b;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Structural Hero Banner -->
<section class="page-banner" style="background: linear-gradient(180deg, rgba(9,13,22,0.85) 0%, rgba(9,13,22,0.95) 100%), url('../images/backgrounds/chinaseychellescargo.jpg') center/cover no-repeat; padding: 4.5rem 0 3.5rem 0;">
  <div class="container" style="position:relative; z-index:10;">
    <span class="pill-badge" style="margin-bottom:0.85rem; background:rgba(255,122,0,0.15); border-color:#FF7A00; color:#FF7A00;">
      <i class="fa-solid fa-graduation-cap me-1"></i> Trade Knowledge & Logistics Hub
    </span>
    <h1 class="page-banner-title" style="font-size:2.6rem; max-width:800px; margin-bottom:0.75rem;">
      Dubai to Global Shipping Guides & Blogs
    </h1>
    <p style="color:#94A3B8; font-size:1.1rem; max-width:680px; margin-bottom:1.5rem; line-height:1.6;">
      Expert advice on sea freight consolidation, customs clearance (SRC/ASYCUDA), container loading, and door-to-door cargo logistics.
    </p>
    <div class="breadcrumbs">
      <a href="../index.html">Home</a> &rsaquo; <span>Logs & Guides</span>
    </div>
  </div>
</section>

<!-- Featured Hero Guide Card -->
<section style="background:#0F172A; color:#FFFFFF; padding:2.5rem 0; border-bottom:1px solid rgba(255,255,255,0.08);">
  <div class="container">
    <div style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,122,0,0.3); border-radius:16px; padding:2rem; display:grid; grid-template-columns:1fr 1fr; gap:2rem; align-items:center;">
      <div>
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.75rem;">
          <span style="background:#FF7A00; color:#FFFFFF; font-size:0.75rem; font-weight:800; text-transform:uppercase; padding:0.25rem 0.65rem; border-radius:4px; letter-spacing:0.5px;">
            FEATURED GUIDE
          </span>
          <span style="color:#94A3B8; font-size:0.85rem;"><i class="fa-regular fa-clock me-1 text-accent"></i> 8 min read</span>
        </div>
        
        <h2 style="font-size:1.85rem; color:#FFFFFF; line-height:1.3; margin-bottom:1rem; font-weight:800;">
          Ship Your Cargo from Jebel Ali (Dubai) to Port Victoria, Seychelles
        </h2>
        
        <p style="color:#CBD5E1; font-size:0.98rem; line-height:1.65; margin-bottom:1.5rem;">
          Master the complete sea freight journey connecting Dubai to Mahé Island. Learn about LCL consolidation, mandatory SRC import paperwork, ASYCUDA World lodging, and doorstep delivery.
        </p>

        <div style="display:flex; align-items:center; gap:1.25rem; flex-wrap:wrap;">
          <a href="ship-cargo-jebel-ali-dubai-to-port-victoria-seychelles" class="btn btn-accent" style="padding:0.75rem 1.5rem; font-weight:700;">
            Read Featured Guide <i class="fa-solid fa-arrow-right ms-1"></i>
          </a>
          <span style="color:#94A3B8; font-size:0.85rem;">
            <i class="fa-solid fa-user me-1 text-primary"></i> Seychelles Cargo Logistics Desk
          </span>
        </div>
      </div>

      <div style="position:relative; border-radius:12px; overflow:hidden; min-height:260px; box-shadow:0 15px 35px rgba(0,0,0,0.5);">
        <img src="../images/backgrounds/chinaseychellescargo.jpg" alt="Featured Seychelles Cargo Guide" style="width:100%; height:100%; object-fit:cover; position:absolute; inset:0;">
      </div>
    </div>
  </div>
</section>

<!-- Category Filter Pills & Search Bar -->
<section style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; padding:1.25rem 0;">
  <div class="container" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <!-- Category Pills -->
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
      <?php foreach ($categories as $cat): 
        $isActive = (strtolower($active_cat) === strtolower($cat));
      ?>
        <a href="index.php?category=<?php echo urlencode($cat); ?>" 
           style="padding:0.45rem 1rem; border-radius:9999px; font-size:0.85rem; font-weight:600; text-decoration:none; transition:all 0.2s ease; <?php echo $isActive ? 'background:#0066FF; color:#FFFFFF; box-shadow:0 4px 10px rgba(0,102,255,0.25);' : 'background:#FFFFFF; color:#475569; border:1px solid #CBD5E1;'; ?>">
          <?php echo htmlspecialchars($cat); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Live Search Input -->
    <div style="position:relative; width:100%; max-width:280px;">
      <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:#94A3B8; font-size:0.85rem;"></i>
      <input type="text" id="blogSearchInput" onkeyup="filterBlogCards()" placeholder="Search guide titles or topics..." style="width:100%; padding:0.5rem 1rem 0.5rem 2.4rem; border-radius:8px; border:1px solid #CBD5E1; outline:none; font-size:0.88rem; background:#FFFFFF;">
    </div>
  </div>
</section>

<!-- Main Blog Directory Section -->
<section class="section" style="background:#FFFFFF;">
  <div class="container">
    <div class="grid-subpage">
      
      <!-- Left Column: Article Grid -->
      <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; border-bottom:2px solid #F1F5F9; padding-bottom:0.75rem;">
          <h2 style="font-size:1.4rem; color:var(--color-secondary); margin:0;">
            <i class="fa-solid fa-book-open me-2 text-primary"></i>All Articles & Guides
          </h2>
          <span style="font-size:0.85rem; color:#64748B; font-weight:600;" id="articleCountLabel">
            Showing <?php echo count($blogs) + 1; ?> Articles
          </span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.5rem;" id="blogCardsContainer">
          
          <!-- Article 1: Built-in Jebel Ali Guide -->
          <div class="card blog-card-item" data-title="ship cargo from jebel ali dubai to port victoria seychelles sea freight lcl" style="padding:0; overflow:hidden; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 8px 25px rgba(0,0,0,0.04); display:flex; flex-direction:column;">
            <div style="position:relative; height:190px; overflow:hidden; background:#0F172A;">
              <img src="../images/backgrounds/chinaseychellescargo.jpg" alt="Jebel Ali to Port Victoria Seychelles Cargo" style="width:100%; height:100%; object-fit:cover;">
              <span style="position:absolute; top:0.85rem; left:0.85rem; background:#0066FF; color:#FFFFFF; font-size:0.72rem; font-weight:700; text-transform:uppercase; padding:0.2rem 0.55rem; border-radius:4px;">
                SEYCHELLES SECTOR
              </span>
              <span style="position:absolute; bottom:0.85rem; right:0.85rem; background:rgba(15,23,42,0.85); color:#FFFFFF; font-size:0.72rem; font-weight:600; padding:0.2rem 0.55rem; border-radius:4px; backdrop-filter:blur(4px);">
                <i class="fa-regular fa-clock me-1 text-accent"></i> 8 min read
              </span>
            </div>

            <div style="padding:1.35rem; flex-grow:1; display:flex; flex-direction:column; justify-content:space-between;">
              <div>
                <div style="font-size:0.78rem; color:#64748B; margin-bottom:0.4rem;">
                  <i class="fa-regular fa-calendar me-1 text-primary"></i> Sep 01, 2026 &bull; By Seychelles Cargo Team
                </div>
                <h3 style="font-size:1.15rem; color:#0F172A; line-height:1.4; margin-bottom:0.65rem; font-weight:700;">
                  <a href="ship-cargo-jebel-ali-dubai-to-port-victoria-seychelles" style="color:inherit; text-decoration:none;">
                    Ship Cargo from Jebel Ali (Dubai) to Port Victoria, Seychelles
                  </a>
                </h3>
                <p style="color:#64748B; font-size:0.9rem; line-height:1.55; margin-bottom:1.25rem;">
                  Complete guide on sea freight, LCL consolidation, customs clearance (SRC), ASYCUDA World lodging, and mandatory import documentation.
                </p>
              </div>

              <a href="ship-cargo-jebel-ali-dubai-to-port-victoria-seychelles" class="btn btn-outline btn-block" style="text-align:center; font-size:0.88rem; padding:0.6rem;">
                Read Full Guide <i class="fa-solid fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>

          <!-- Dynamic DB Blog Cards -->
          <?php if (!empty($blogs)): ?>
            <?php foreach ($blogs as $b): 
              if ($b['slug'] === 'ship-cargo-jebel-ali-dubai-to-port-victoria-seychelles') continue;

              $imgSrc = !empty($b['feature_image']) ? (str_starts_with($b['feature_image'], 'http') ? $b['feature_image'] : '../' . $b['feature_image']) : '../images/backgrounds/chinaseychellescargo.jpg';
              $searchString = strtolower($b['title'] . ' ' . $b['slug'] . ' ' . ($b['category'] ?? ''));
            ?>
              <div class="card blog-card-item" data-title="<?php echo htmlspecialchars($searchString); ?>" style="padding:0; overflow:hidden; border-radius:14px; border:1px solid #E2E8F0; box-shadow:0 8px 25px rgba(0,0,0,0.04); display:flex; flex-direction:column;">
                <div style="position:relative; height:190px; overflow:hidden; background:#0F172A;">
                  <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" style="width:100%; height:100%; object-fit:cover;">
                  <span style="position:absolute; top:0.85rem; left:0.85rem; background:#0066FF; color:#FFFFFF; font-size:0.72rem; font-weight:700; text-transform:uppercase; padding:0.2rem 0.55rem; border-radius:4px;">
                    <?php echo htmlspecialchars($b['category'] ?: 'Sea Freight'); ?>
                  </span>
                  <span style="position:absolute; bottom:0.85rem; right:0.85rem; background:rgba(15,23,42,0.85); color:#FFFFFF; font-size:0.72rem; font-weight:600; padding:0.2rem 0.55rem; border-radius:4px; backdrop-filter:blur(4px);">
                    <i class="fa-regular fa-clock me-1 text-accent"></i> <?php echo htmlspecialchars($b['read_time'] ?: '5 min read'); ?>
                  </span>
                </div>

                <div style="padding:1.35rem; flex-grow:1; display:flex; flex-direction:column; justify-content:space-between;">
                  <div>
                    <div style="font-size:0.78rem; color:#64748B; margin-bottom:0.4rem;">
                      <i class="fa-regular fa-calendar me-1 text-primary"></i> <?php echo date('M d, Y', strtotime($b['created_at'])); ?> &bull; By <?php echo htmlspecialchars($b['author']); ?>
                    </div>
                    <h3 style="font-size:1.15rem; color:#0F172A; line-height:1.4; margin-bottom:0.65rem; font-weight:700;">
                      <a href="<?php echo htmlspecialchars($b['slug']); ?>" style="color:inherit; text-decoration:none;">
                        <?php echo htmlspecialchars($b['title']); ?>
                      </a>
                    </h3>
                    <p style="color:#64748B; font-size:0.9rem; line-height:1.55; margin-bottom:1.25rem;">
                      <?php echo htmlspecialchars(mb_strimwidth($b['excerpt'] ?: strip_tags($b['content']), 0, 120, '...')); ?>
                    </p>
                  </div>

                  <a href="<?php echo htmlspecialchars($b['slug']); ?>" class="btn btn-outline btn-block" style="text-align:center; font-size:0.88rem; padding:0.6rem;">
                    Read Full Article <i class="fa-solid fa-arrow-right ms-1"></i>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>

      <!-- Right Column: Sidebar Widgets -->
      <aside>
        
        <!-- Category Directory Widget -->
        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:14px; padding:1.5rem; margin-bottom:1.75rem;">
          <h4 style="font-size:1.1rem; color:var(--color-secondary); margin-bottom:1rem; border-bottom:2px solid #E2E8F0; padding-bottom:0.5rem;">
            <i class="fa-solid fa-folder me-2 text-primary"></i>Popular Categories
          </h4>
          <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.5rem; font-size:0.9rem;">
            <li>
              <a href="index.php?category=Seychelles+Sector" style="display:flex; justify-content:space-between; color:#334155; text-decoration:none; padding:0.4rem 0;">
                <span>🇸🇨 Seychelles Sector</span>
                <span class="badge" style="background:#EFF6FF; color:#0066FF;">Active</span>
              </a>
            </li>
            <li>
              <a href="index.php?category=Sea+Freight" style="display:flex; justify-content:space-between; color:#334155; text-decoration:none; padding:0.4rem 0;">
                <span>🚢 Sea Freight (FCL/LCL)</span>
                <span class="badge" style="background:#EFF6FF; color:#0066FF;">Popular</span>
              </a>
            </li>
            <li>
              <a href="index.php?category=LCL+Consolidation" style="display:flex; justify-content:space-between; color:#334155; text-decoration:none; padding:0.4rem 0;">
                <span>📦 LCL Consolidation</span>
                <span class="badge" style="background:#EFF6FF; color:#0066FF;">Weekly</span>
              </a>
            </li>
            <li>
              <a href="index.php?category=Customs+%26+Clearance" style="display:flex; justify-content:space-between; color:#334155; text-decoration:none; padding:0.4rem 0;">
                <span>📄 Customs & Clearance</span>
                <span class="badge" style="background:#EFF6FF; color:#0066FF;">SRC / ASYCUDA</span>
              </a>
            </li>
            <li>
              <a href="index.php?category=Air+Freight" style="display:flex; justify-content:space-between; color:#334155; text-decoration:none; padding:0.4rem 0;">
                <span>✈️ Air Freight Express</span>
                <span class="badge" style="background:#EFF6FF; color:#0066FF;">Daily</span>
              </a>
            </li>
          </ul>
        </div>

        <!-- Sticky Quick Quote Form -->
        <div class="card" style="padding:1.75rem; position:sticky; top:95px; margin-bottom:1.75rem; box-shadow:0 10px 30px rgba(0,0,0,0.06);">
          <h3 style="font-size:1.25rem; margin-bottom:0.4rem; color:var(--color-secondary);">
            <i class="fa-solid fa-calculator text-primary me-2"></i>Inquire Freight Rate
          </h3>
          <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1.25rem;">
            Get instant CBM & KG shipping rates for Seychelles & global sectors.
          </p>

          <form action="../request.php" method="POST" data-ajax="true">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="hidden" name="recipient" value="Blog Portal Inquiry">

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
              <select name="destination" class="form-control" style="padding:0.6rem;">
                <option value="Seychelles">🇸🇨 Seychelles (Port Victoria)</option>
                <option value="Mauritius">🇲🇺 Mauritius (Port Louis)</option>
                <option value="Zanzibar">🇹🇿 Zanzibar (Malindi Port)</option>
                <option value="Comoros">🇰🇲 Comoros (Moroni Port)</option>
                <option value="Other">Global / Other</option>
              </select>
            </div>

            <div class="form-group" style="margin-bottom:1rem;">
              <label class="form-label" style="font-size:0.82rem;">Cargo Details / CBM *</label>
              <textarea name="message" class="form-control" rows="2" placeholder="e.g. 2 CBM / 5 boxes / furniture" style="padding:0.6rem; font-size:0.88rem;" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding:0.65rem;">
              <i class="fa-solid fa-paper-plane me-1"></i> Request Rate Quote
            </button>
          </form>
        </div>

        <!-- Direct WhatsApp Support Card -->
        <div style="background:#0F172A; color:#FFFFFF; border-radius:14px; padding:1.5rem;">
          <h4 style="color:#FFFFFF; font-size:1.05rem; margin-bottom:0.75rem;">
            <i class="fa-brands fa-whatsapp text-success me-2"></i>Direct Freight Support
          </h4>
          <p style="color:#94A3B8; font-size:0.85rem; line-height:1.5; margin-bottom:1rem;">
            Have questions about cargo cut-off dates, container loading, or Seychelles customs documents?
          </p>
          <a href="https://wa.me/971552038001" target="_blank" class="btn btn-block" style="background:#25D366; color:#FFFFFF; text-align:center; font-weight:700; font-size:0.88rem; padding:0.65rem;">
            <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp +971 55 203 8001
          </a>
        </div>

      </aside>

    </div>
  </div>
</section>

<script>
function filterBlogCards() {
  const query = document.getElementById('blogSearchInput').value.toLowerCase().trim();
  const cards = document.querySelectorAll('#blogCardsContainer .blog-card-item');
  let visible = 0;

  cards.forEach(card => {
    const text = card.dataset.title.toLowerCase();
    if (text.includes(query)) {
      card.style.display = 'flex';
      visible++;
    } else {
      card.style.display = 'none';
    }
  });

  document.getElementById('articleCountLabel').innerText = 'Showing ' + visible + ' Articles';
}
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
