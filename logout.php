<?php
require_once __DIR__ . '/includes/auth_check.php';

// Unset only visitor session data (or all if desired)
unset($_SESSION['visitor_id']);
unset($_SESSION['visitor_name']);
unset($_SESSION['visitor_email']);

header('Location: index.php?msg=logged_out');
exit;
