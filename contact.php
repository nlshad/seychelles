<?php
/**
 * Seychelles International Cargo LLC - Contact Form Processor
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
    $message   = sanitize_input($_POST['message'] ?? '');

    if (empty($firstname) || !$email || empty($message)) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields with a valid email address.']);
            exit;
        } else {
            header('Location: Contact.html?error=invalid');
            exit;
        }
    }

    // Insert into Database
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("INSERT INTO contacts (firstname, lastname, email, phone, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $firstname,
            $lastname,
            $email,
            $phone ?: 'Not provided',
            $message
        ]);
    } catch (Exception $e) {
        error_log('DB Contact Insert Error: ' . $e->getMessage());
    }

    $subject = "New Contact Us Message from {$firstname} {$lastname}";
    $data = [
        'First Name' => $firstname,
        'Last Name'  => $lastname,
        'Email'      => $email,
        'Phone'      => $phone ?: 'Not provided',
        'Message'    => $message
    ];

    $sent = send_inquiry_email($subject, $data, $email);

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Thank you! Your message has been sent successfully. We will get back to you shortly.']);
        exit;
    } else {
        header('Location: Contact.html?status=success');
        exit;
    }
} else {
    header('Location: Contact.html');
    exit;
}