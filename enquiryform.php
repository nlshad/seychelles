<?php
/**
 * Seychelles International Cargo LLC - Online Enquiry Form Processor
 * Hardened & PHP 8+ Ready with Database Logging
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = sanitize_input($_POST['firstname'] ?? '');
    $lastname  = sanitize_input($_POST['lastname'] ?? '');
    $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone     = sanitize_input($_POST['phone'] ?? '');
    $place     = sanitize_input($_POST['place'] ?? '');
    $message   = sanitize_input($_POST['message'] ?? '');

    if (empty($firstname) || !$email || empty($message)) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Please fill in your name, email, and message.']);
            exit;
        } else {
            header('Location: Enquiry.html?error=invalid');
            exit;
        }
    }

    // Insert into Database
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("INSERT INTO enquiries (firstname, lastname, email, phone, place, message) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $firstname,
            $lastname,
            $email,
            $phone ?: 'Not provided',
            $place ?: 'Not specified',
            $message
        ]);
    } catch (Exception $e) {
        error_log('DB Enquiry Insert Error: ' . $e->getMessage());
    }

    $subject = "Online Cargo Enquiry from {$firstname} {$lastname}";
    $data = [
        'First Name' => $firstname,
        'Last Name'  => $lastname,
        'Email'      => $email,
        'Phone'      => $phone ?: 'Not provided',
        'Location / Place' => $place ?: 'Not specified',
        'Enquiry Details' => $message
    ];

    $sent = send_inquiry_email($subject, $data, $email);

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Thank you! Your enquiry has been received. Our cargo team will contact you shortly.']);
        exit;
    } else {
        header('Location: Enquiry.html?status=success');
        exit;
    }
} else {
    header('Location: Enquiry.html');
    exit;
}