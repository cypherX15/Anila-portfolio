<?php
/**
 * Submit Project-Specific Comment Handler
 * Strictly associates comments to the requested project_id.
 * Default status is always 'pending' until administrator approves.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#projects');
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    header('Location: index.php?error=csrf#projects');
    exit;
}

$projectId   = (int)($_POST['project_id'] ?? 0);
$authorName  = trim($_POST['author_name'] ?? '');
$authorEmail = trim($_POST['author_email'] ?? '');
$commentText = trim($_POST['comment_text'] ?? '');

if ($projectId <= 0 || empty($authorName) || empty($authorEmail) || empty($commentText)) {
    header('Location: project.php?id=' . $projectId . '&error=missing_fields');
    exit;
}

if (!filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) {
    header('Location: project.php?id=' . $projectId . '&error=invalid_email');
    exit;
}

try {
    $pdo = getDBConnection();

    // Verify project exists
    $chk = $pdo->prepare("SELECT id FROM projects WHERE id = ? LIMIT 1");
    $chk->execute([$projectId]);
    if (!$chk->fetch()) {
        header('Location: index.php#projects');
        exit;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // Insert comment as 'pending'
    $stmt = $pdo->prepare("INSERT INTO project_comments (project_id, author_name, author_email, comment_text, status, ip_address) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->execute([$projectId, $authorName, $authorEmail, $commentText, $ip]);

    // Save visitor email in session for access check convenience
    $_SESSION['visitor_email'] = $authorEmail;
    $_SESSION['visitor_name']  = $authorName;

    header('Location: project.php?id=' . $projectId . '&comment_submitted=1');
    exit;

} catch (Exception $e) {
    header('Location: project.php?id=' . $projectId . '&error=' . urlencode($e->getMessage()));
    exit;
}
