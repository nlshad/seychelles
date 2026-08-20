<?php
/**
 * Seychelles International Cargo LLC - Global Header
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? 'Seychelles International Cargo LLC | Best Door to Door & Cargo Service in Dubai';
$page_description = $page_description ?? 'Seychelles Cargo is one of the best Cargo company in Dubai having service to Seychelles, Mauritius, Moroni, Maldives, Zanzibar. Door to door service LCL service Pickup full container Dubai.';
$page_keywords = $page_keywords ?? 'Door to Door delivery to india, Packing and moving service, Packing and shifting service, Door to door service to india, Door to port service, Courier services to Seychelles, Best FCL, LCL services to Seychelles, Zanzibar, Mauritius, Ghana and Comoros sectors, Seychelles Cargo Dubai';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
  <title><?php echo sanitize_input($page_title); ?></title>
  <meta name="description" content="<?php echo sanitize_input($page_description); ?>">
  <meta name="keywords" content="<?php echo sanitize_input($page_keywords); ?>">
  
  <meta name="author" content="Seychelles International Cargo LLC">
  <meta name="robots" content="index, follow">
  <meta name="google-site-verification" content="YOJIANLayhLffJXegCyAmKKZwq2NbjvwqSCcqawONF8" />
  
  <link rel="icon" href="images/favicon.ico" type="image/x-icon">
  
  <!-- Modern Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- Modern Main CSS Design System -->
  <link rel="stylesheet" href="css/main.css?v=3.0">
</head>
<body>

  <!-- Top Contact Header Bar -->
  <div class="top-bar">
    <div class="container">
      <div class="top-bar-info">
        <span class="top-bar-badge">
          <i class="fa-solid fa-plane-departure" style="font-size:0.75rem;"></i>
          Direct Service Dubai to Africa & Island Sectors
        </span>
        <a href="mailto:<?php echo COMPANY_EMAIL; ?>" class="top-bar-link">
          <i class="fa-regular fa-envelope"></i>
          <?php echo COMPANY_EMAIL; ?>
        </a>
        <a href="<?php echo COMPANY_TEL_HREF; ?>" class="top-bar-link">
          <i class="fa-solid fa-phone"></i>
          <?php echo COMPANY_PHONE; ?>
        </a>
      </div>
      <div class="top-bar-info">
        <span style="color:#94a3b8; font-size:0.8rem;">
          <i class="fa-regular fa-clock me-1"></i> Mon - Sat: 8:00 AM - 8:00 PM
        </span>
      </div>
    </div>
  </div>

  <!-- Main Navigation Bar -->
  <header class="header">
    <div class="container">
      <div class="header-inner">
        <!-- Brand Logo -->
        <a href="index.html" class="brand">
          <img src="images/logo.gif" alt="Seychelles International Cargo LLC" class="brand-logo-img">
        </a>

        <!-- Desktop Navigation -->
        <nav class="nav-menu">
          <a href="index.html" class="nav-link <?php echo is_active_page('index.html'); ?>">
            <i class="fa-solid fa-house me-1" style="font-size:0.85rem;"></i> Home
          </a>
          <a href="door-to-door-service-from-dubai-to-india-best-door-to-door-dubai-uae-dubai-cargo.html" class="nav-link <?php echo is_active_page('door-to-door-service-from-dubai-to-india-best-door-to-door-dubai-uae-dubai-cargo.html'); ?>">
            <i class="fa-solid fa-circle-info me-1" style="font-size:0.85rem;"></i> About Us
          </a>
          
          <!-- Services Dropdown -->
          <div class="nav-item-dropdown">
            <a href="#" class="nav-link">
              <i class="fa-solid fa-boxes-packing me-1" style="font-size:0.85rem;"></i> Services <i class="fa-solid fa-chevron-down ms-1" style="font-size:0.75rem;"></i>
            </a>
            <div class="dropdown-menu">
              <a href="Airfreight-service-dubai-airfreight-service-to-seychelles-airfreight-dubai-best-airfreight-team-in-dubai-airfreight-clearance-airfreight-to-seychelles.html" class="dropdown-item">
                <i class="fa-solid fa-plane-up me-2 text-primary"></i> Air Freight
              </a>
              <a href="sea-freight-dubai-lcl-service-to-dubai-best-seafreight-service-to-dubai-seafreight-service-mauritius-seafreight-to-seychelles-uafl-service-maersk-safmarine.html" class="dropdown-item">
                <i class="fa-solid fa-ship me-2 text-primary"></i> Sea Freight (FCL/LCL)
              </a>
              <a href="sourcing-service-dubai-shop-and-ship-service-in-dubai-dubai-best-cargo-door-door.html" class="dropdown-item">
                <i class="fa-solid fa-bag-shopping me-2 text-primary"></i> Shop & Ship / Sourcing
              </a>
              <a href="dubai-door-to-door-door-to-door-india-door-to-door-shifting-door-to-door-delivery-to-india-best-door-to-door-cargo-in-dubai-door-to-door-uae-door-to-door-service-provider-door-to-door-ajman.html" class="dropdown-item">
                <i class="fa-solid fa-truck-ramp-box me-2 text-primary"></i> Door to Door Cargo
              </a>
              <a href="Warehousing-service-dubai-best-warehouse-inventory-warehouse-lcl-service-warehousing-service-uae-warehousing-sharjah.html" class="dropdown-item">
                <i class="fa-solid fa-warehouse me-2 text-primary"></i> Warehousing & Storage
              </a>
            </div>
          </div>

          <!-- Destinations Dropdown -->
          <div class="nav-item-dropdown">
            <a href="#" class="nav-link">
              <i class="fa-solid fa-globe me-1" style="font-size:0.85rem;"></i> Destinations <i class="fa-solid fa-chevron-down ms-1" style="font-size:0.75rem;"></i>
            </a>
            <div class="dropdown-menu">
              <a href="consolidation-service-to-seychelles.html" class="dropdown-item">🇸🇨 Seychelles Sector</a>
              <a href="consolidation-service-to-mauritius.html" class="dropdown-item">🇲🇺 Mauritius Sector</a>
              <a href="consolidation-service-to-zanzibar.html" class="dropdown-item">🇹🇿 Zanzibar Sector</a>
              <a href="consolidation-service-to-comoros.html" class="dropdown-item">🇰🇲 Comoros Sector</a>
              <a href="consolidation-service-to-dar-es-salam.html" class="dropdown-item">🇹🇿 Dar Es Salaam</a>
              <a href="consolidation-service-to-uganda.html" class="dropdown-item">🇺🇬 Uganda Sector</a>
              <a href="consolidation-service-to-zambia.html" class="dropdown-item">🇿🇲 Zambia Sector</a>
              <a href="consolidation-service-to-maldives.html" class="dropdown-item">🇲🇻 Maldives Sector</a>
              <a href="door-to-door-service-to-india.html" class="dropdown-item">🇮🇳 India Sector</a>
              <a href="door-to-door-service-to-nepal.html" class="dropdown-item">🇳🇵 Nepal Sector</a>
              <a href="door-to-door-service-to-bangladesh.html" class="dropdown-item">🇧🇩 Bangladesh Sector</a>
            </div>
          </div>

          <a href="Enquiry.html" class="nav-link <?php echo is_active_page('Enquiry.html'); ?>">
            <i class="fa-solid fa-file-pen me-1" style="font-size:0.85rem;"></i> Enquiry
          </a>
          <a href="Contact.html" class="nav-link <?php echo is_active_page('Contact.html'); ?>">
            <i class="fa-solid fa-headset me-1" style="font-size:0.85rem;"></i> Contact
          </a>
        </nav>

        <!-- CTA Header Action -->
        <div style="display:flex; align-items:center; gap:1rem;">
          <button class="btn btn-accent btn-sm" data-open-modal="quoteModal">
            <i class="fa-solid fa-paper-plane me-1"></i> Request Quote
          </button>
          
          <button class="mobile-toggle" aria-label="Toggle Navigation">
            <i class="fa-solid fa-bars" style="font-size:1.25rem;"></i>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- Mobile Drawer -->
  <div class="mobile-overlay"></div>
  <div class="mobile-drawer">
    <div>
      <div class="mobile-header">
        <span class="brand-title" style="font-size:1.1rem;">SEYCHELLES <span>CARGO</span></span>
        <button class="mobile-close"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="mobile-nav-links">
        <a href="index.html" class="mobile-nav-link"><i class="fa-solid fa-house me-2"></i> Home</a>
        <a href="door-to-door-service-from-dubai-to-india-best-door-to-door-dubai-uae-dubai-cargo.html" class="mobile-nav-link"><i class="fa-solid fa-circle-info me-2"></i> About Us</a>
        <a href="Airfreight-service-dubai-airfreight-service-to-seychelles-airfreight-dubai-best-airfreight-team-in-dubai-airfreight-clearance-airfreight-to-seychelles.html" class="mobile-nav-link"><i class="fa-solid fa-plane-up me-2"></i> Air Freight</a>
        <a href="sea-freight-dubai-lcl-service-to-dubai-best-seafreight-service-to-dubai-seafreight-service-mauritius-seafreight-to-seychelles-uafl-service-maersk-safmarine.html" class="mobile-nav-link"><i class="fa-solid fa-ship me-2"></i> Sea Freight</a>
        <a href="sourcing-service-dubai-shop-and-ship-service-in-dubai-dubai-best-cargo-door-door.html" class="mobile-nav-link"><i class="fa-solid fa-bag-shopping me-2"></i> Shop & Ship</a>
        <a href="dubai-door-to-door-door-to-door-india-door-to-door-shifting-door-to-door-delivery-to-india-best-door-to-door-cargo-in-dubai-door-to-door-uae-door-to-door-service-provider-door-to-door-ajman.html" class="mobile-nav-link"><i class="fa-solid fa-truck-ramp-box me-2"></i> Door to Door</a>
        <a href="Warehousing-service-dubai-best-warehouse-inventory-warehouse-lcl-service-warehousing-service-uae-warehousing-sharjah.html" class="mobile-nav-link"><i class="fa-solid fa-warehouse me-2"></i> Warehousing</a>
        <a href="Enquiry.html" class="mobile-nav-link"><i class="fa-solid fa-file-pen me-2"></i> Enquiry</a>
        <a href="Contact.html" class="mobile-nav-link"><i class="fa-solid fa-headset me-2"></i> Contact</a>
      </div>
    </div>
    <div style="padding-top:1.5rem; border-top:1px solid var(--border-color);">
      <button class="btn btn-primary btn-block" data-open-modal="quoteModal">
        <i class="fa-solid fa-paper-plane me-1"></i> Request a Quote
      </button>
    </div>
  </div>
