<?php
/**
 * Seychelles International Cargo LLC - Quick Quote / Request Form Processor
 * Hardened & PHP 8+ Ready with Database Logging
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = sanitize_input($_POST['name'] ?? '');
    $contact   = sanitize_input($_POST['contact'] ?? '');
    $poster    = sanitize_input($_POST['poster'] ?? '');
    $recipient = sanitize_input($_POST['recipient'] ?? '');
    $departure = sanitize_input($_POST['departure'] ?? '');
    $message   = sanitize_input($_POST['message'] ?? '');

    if (empty($name) || empty($contact)) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Please provide your name and phone/contact info.']);
            exit;
        } else {
            header('Location: index.html?error=invalid');
            exit;
        }
    }

    // Insert into Database
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("INSERT INTO quotes (name, contact, origin, destination, departure_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $name,
            $contact,
            $poster ?: 'Dubai, UAE',
            $recipient ?: 'Not specified',
            $departure ?: 'Flexible'
        ]);
    } catch (Exception $e) {
        // Log error silently if db fails, send mail regardless
        error_log('DB Quote Insert Error: ' . $e->getMessage());
    }

    $subject = "Rate Quote Request from {$name}";
    $data = [
        'Customer Name'   => $name,
        'Phone / Contact' => $contact,
        'City Origin'     => $poster ?: 'Dubai, UAE',
        'Destination'     => $recipient ?: 'Not specified',
        'Departure Date'  => $departure ?: 'Flexible',
        'Notes / Details' => $message ?: 'Standard Quote Request'
    ];

    $sent = send_inquiry_email($subject, $data);

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Thank you! Your quote request has been submitted. Our team will contact you with competitive rates.']);
        exit;
    } else {
        header('Location: index.html?status=success');
        exit;
    }
} else {
    header('Location: index.html');
    exit;
}