<?php
/**
 * Submit Contact Message Handler
 * Validates inputs, stores in `contact_messages` table, and redirects with confirmation.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#contact');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: index.php?error=csrf#contact');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'Portfolio Inquiry');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    header('Location: index.php?error=empty_fields#contact');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?error=invalid_email#contact');
    exit;
}

try {
    $pdo = getDBConnection();
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, is_read, ip_address) VALUES (?, ?, ?, ?, 0, ?)");
    $stmt->execute([$name, $email, $subject, $message, $ip]);

    // Store visitor session for seamless return
    $_SESSION['visitor_name']  = $name;
    $_SESSION['visitor_email'] = $email;

    header('Location: index.php?contact_success=1#contact');
    exit;

} catch (Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()) . '#contact');
    exit;
}
