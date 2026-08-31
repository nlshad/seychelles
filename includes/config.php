<?php
/**
 * Seychelles International Cargo LLC - Global Configuration
 * PHP 8+ Modern Architecture
 */

define('SITE_NAME', 'Seychelles International Cargo LLC');
define('SITE_SLOGAN', 'Best Cargo & Door to Door Service in Dubai');
define('COMPANY_PHONE', '+971-4-3550903');
define('COMPANY_TEL_HREF', 'tel:+97143550903');
define('COMPANY_WHATSAPP', '+971 55 203 8001');
define('COMPANY_WHATSAPP_NUM', '971552038001');
define('COMPANY_WHATSAPP_LINK', 'https://wa.me/971552038001');
define('COMPANY_EMAIL', 'sales@seychellescargo.com');
define('FORM_TARGET_EMAIL', 'sales@seychellescargo.com');
define('COMPANY_ADDRESS', 'Bur Dubai, Dubai, United Arab Emirates');

// Base URL detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . $domainName . '/');

// Active Sectors / Destinations
$SECTORS = [
    'seychelles' => 'Seychelles',
    'mauritius'  => 'Mauritius',
    'zanzibar'   => 'Zanzibar',
    'comoros'    => 'Comoros',
    'maldives'   => 'Maldives',
    'dar-es-salam' => 'Dar Es Salaam',
    'ghana'      => 'Ghana',
    'india'      => 'India',
    'nepal'      => 'Nepal',
    'bangladesh' => 'Bangladesh',
    'china'      => 'China'
];
