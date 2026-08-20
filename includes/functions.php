<?php
/**
 * Seychelles International Cargo LLC - Helper Functions
 */

if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/config.php';
}

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

/**
 * Sanitize string input safely
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data ?? '');
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Check active page for navigation highlight
 */
function is_active_page($filename) {
    $current = basename($_SERVER['PHP_SELF'] ?? '');
    if ($current === '' || $current === 'index.php') {
        $current = 'index.html';
    }
    return ($current === $filename) ? 'active' : '';
}

/**
 * Modern HTML Email Sender
 */
function send_inquiry_email($subject, $data, $reply_email = null) {
    $to = FORM_TARGET_EMAIL;
    $from = $reply_email ?: COMPANY_EMAIL;
    
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Seychelles Cargo Inquiry <" . COMPANY_EMAIL . ">\r\n";
    if ($reply_email) {
        $headers .= "Reply-To: {$reply_email}\r\n";
    }
    $headers .= "X-Mailer: PHP/" . phpversion();

    $rows = '';
    foreach ($data as $key => $val) {
        $label = ucwords(str_replace('_', ' ', $key));
        $value = nl2br(sanitize_input($val));
        $rows .= "<tr>
                    <td style='padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 140px; color: #1e293b;'>{$label}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #e2e8f0; color: #334155;'>{$value}</td>
                  </tr>";
    }

    $body = "
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset='UTF-8'>
      <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #f8fafc; margin: 0; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; }
        .header { background: #0B192C; padding: 24px; text-align: center; color: #ffffff; }
        .header h2 { margin: 0; font-size: 20px; font-weight: 600; color: #ffffff; }
        .header p { margin: 4px 0 0; font-size: 13px; color: #94a3b8; }
        .content { padding: 24px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .footer { background: #f1f5f9; padding: 16px; text-align: center; font-size: 12px; color: #64748b; }
      </style>
    </head>
    <body>
      <div class='card'>
        <div class='header'>
          <h2>New Website Inquiry</h2>
          <p>" . SITE_NAME . "</p>
        </div>
        <div class='content'>
          <h3 style='margin-top:0; color:#0f172a;'>Inquiry Details:</h3>
          <table class='table'>
            {$rows}
          </table>
        </div>
        <div class='footer'>
          Sent automatically from website contact system on " . date('Y-m-d H:i:s') . "
        </div>
      </div>
    </body>
    </html>
    ";

    return @mail($to, $subject, $body, $headers);
}
