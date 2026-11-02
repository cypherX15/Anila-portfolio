<?php
/**
 * Request Access to a Restricted Project
 * Stores in `project_access_requests` table with status 'pending'.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$pdo = getDBConnection();

$projectId = (int)($_GET['project_id'] ?? 0);
$error     = '';
$success   = '';

if ($projectId > 0) {
    $stmt = $pdo->prepare("SELECT id, title, is_restricted FROM projects WHERE id = ? LIMIT 1");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();
} else {
    $project = null;
}

if (!$project || !$project['is_restricted']) {
    header('Location: index.php#projects');
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh the page.';
    } else {
        $name   = trim($_POST['visitor_name'] ?? '');
        $email  = trim($_POST['visitor_email'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if (empty($name) || empty($email) || empty($reason)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check existing request status
            $chk = $pdo->prepare("SELECT status FROM project_access_requests WHERE project_id = ? AND visitor_email = ? ORDER BY id DESC LIMIT 1");
            $chk->execute([$projectId, $email]);
            $existing = $chk->fetch();

            if ($existing && $existing['status'] === 'approved') {
                $success = 'You already have approved access to this project! Return to the project page to view all resources.';
                $_SESSION['visitor_email'] = $email;
                $_SESSION['visitor_name']  = $name;
            } elseif ($existing && $existing['status'] === 'pending') {
                $success = 'Your request is currently awaiting administrator review. Thank you for your patience.';
                $_SESSION['visitor_email'] = $email;
                $_SESSION['visitor_name']  = $name;
            } else {
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $ins = $pdo->prepare("INSERT INTO project_access_requests (project_id, visitor_name, visitor_email, reason, status, ip_address) VALUES (?, ?, ?, ?, 'pending', ?)");
                $ins->execute([$projectId, $name, $email, $reason, $ip]);

                $_SESSION['visitor_email'] = $email;
                $_SESSION['visitor_name']  = $name;

                header('Location: project.php?id=' . $projectId . '&access_requested=1');
                exit;
            }
        }
    }
}

$pageTitle = 'Request Access · ' . $project['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width:580px;margin:5rem auto 4rem;padding:0 1.5rem;position:relative;z-index:1;">
    <div style="margin-bottom:1.5rem;">
        <a href="project.php?id=<?= $project['id'] ?>" class="btn btn-ghost btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
    </div>

    <div class="glass-card">
        <div style="text-align:center;margin-bottom:1.75rem;">
            <div style="font-size:2.2rem;margin-bottom:0.75rem;color:var(--primary-light);">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 style="font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:0.4rem;">
                Request Access
            </h1>
            <p style="color:var(--text-secondary);font-size:0.9rem;">
                Access restricted materials for: <strong style="color:var(--text-primary);"><?= e($project['title']) ?></strong>
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span class="alert-icon"><i class="fas fa-exclamation-circle"></i></span>
                <div><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" data-persist="1">
                <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                <div><?= e($success) ?></div>
            </div>
            <div style="margin-top:1.5rem;text-align:center;">
                <a href="project.php?id=<?= $project['id'] ?>" class="btn btn-primary">Return to Project Details</a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                <div class="form-group">
                    <label class="form-label" for="req_name">Full Name *</label>
                    <input type="text" id="req_name" name="visitor_name" class="form-control"
                           placeholder="e.g. Jane Doe / Faculty Member" required
                           value="<?= e(getVisitorName() !== 'Guest' ? getVisitorName() : '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="req_email">Email Address *</label>
                    <input type="email" id="req_email" name="visitor_email" class="form-control"
                           placeholder="e.g. jane@institution.edu" required
                           value="<?= e(getVisitorEmail()) ?>">
                    <span class="form-hint">Used for access verification and status determination.</span>
                </div>

                <div class="form-group" style="margin-bottom:1.75rem;">
                    <label class="form-label" for="req_reason">Reason for Request *</label>
                    <textarea id="req_reason" name="reason" class="form-control" rows="4"
                              placeholder="Please explain your professional, academic, or evaluation interest in reviewing this restricted source code and report..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-paper-plane"></i> Submit Access Request
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
