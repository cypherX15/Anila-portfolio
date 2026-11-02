<?php
/**
 * Visitor & Reviewer Entry Portal
 * No password needed: visitors enter their Name and Email to enter the portfolio.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            $error = 'Please enter both your name and email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please provide a valid email address.';
        } else {
            try {
                $pdo = getDBConnection();
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

                // Check if returning visitor
                $stmt = $pdo->prepare("SELECT id, visit_count FROM visitors WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $visitorId = $existing['id'];
                    $update = $pdo->prepare("UPDATE visitors SET name = ?, visit_count = visit_count + 1, last_visit = CURRENT_TIMESTAMP, ip_address = ? WHERE id = ?");
                    $update->execute([$name, $ip, $visitorId]);
                } else {
                    $insert = $pdo->prepare("INSERT INTO visitors (name, email, ip_address, visit_count) VALUES (?, ?, ?, 1)");
                    $insert->execute([$name, $email, $ip]);
                    $visitorId = $pdo->lastInsertId();
                }

                // Register visitor in session
                $_SESSION['visitor_id'] = $visitorId;
                $_SESSION['visitor_name'] = $name;
                $_SESSION['visitor_email'] = $email;

                header('Location: index.php?welcome=1');
                exit;

            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Enter Anila S Portfolio</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(16px);
            border-radius: var(--radius-lg);
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            position: relative;
        }
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--cyan-accent));
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .auth-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <span class="brand-badge"></span>
                <span>ANILA S</span>
            </div>
            <p class="auth-subtitle">
                Welcome to my engineering &amp; developer portfolio. Please let me know who is visiting!
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                <div class="form-group">
                    <label class="form-label" for="name">Your Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Alex Johnson / Recruiter" required autofocus value="<?= e($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group" style="margin-bottom:2rem;">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="e.g. alex@company.com" required value="<?= e($_POST['email'] ?? '') ?>">
                    <small style="color:var(--text-muted);font-size:0.8rem;display:block;margin-top:0.35rem;">
                        No password needed. This links your session so you can post feedback and message me.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;padding:0.9rem;">
                    🚀 Enter Portfolio
                </button>
            </form>

            <div style="margin-top:2rem;text-align:center;border-top:1px solid var(--card-border);padding-top:1.5rem;display:flex;flex-direction:column;gap:0.75rem;">
                <a href="index.php" style="color:var(--text-secondary);font-size:0.9rem;">
                    Continue as Guest without signing in &rarr;
                </a>
                <a href="admin/login.php" style="color:var(--text-muted);font-size:0.8rem;">
                    🔒 Site Owner / Admin Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
