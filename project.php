<?php
/**
 * Individual Project Details Page
 * Displays project overview, problem statement, objectives, implementation details,
 * screenshots gallery, video demonstration, documentation reports, and per-project comments.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$pdo = getDBConnection();

$projectId = (int)($_GET['id'] ?? 0);
if ($projectId <= 0) {
    header('Location: index.php#projects');
    exit;
}

// Fetch Project
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? LIMIT 1");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: index.php#projects');
    exit;
}

// Fetch Screenshots
$resStmt = $pdo->prepare("SELECT * FROM project_resources WHERE project_id = ? ORDER BY display_order ASC, id ASC");
$resStmt->execute([$projectId]);
$resources = $resStmt->fetchAll();

// Access Control Logic
$isRestricted = !empty($project['is_restricted']);
$visitorEmail = getVisitorEmail();
$hasAccess    = !$isRestricted;
$accessStatus = null;

if ($isRestricted && !empty($visitorEmail)) {
    $reqStmt = $pdo->prepare("SELECT status FROM project_access_requests WHERE project_id = ? AND visitor_email = ? ORDER BY id DESC LIMIT 1");
    $reqStmt->execute([$projectId, $visitorEmail]);
    $req = $reqStmt->fetch();
    if ($req) {
        $accessStatus = $req['status'];
        if ($req['status'] === 'approved') {
            $hasAccess = true;
        }
    }
}

// Fetch Approved Comments for this specific project only
$comStmt = $pdo->prepare("SELECT * FROM project_comments WHERE project_id = ? AND status = 'approved' ORDER BY created_at DESC");
$comStmt->execute([$projectId]);
$comments = $comStmt->fetchAll();

$pageTitle = $project['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="project-page-wrap">
    <!-- Back Navigation -->
    <div style="margin-bottom:2rem;">
        <a href="index.php#projects" class="btn btn-ghost btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>

    <!-- Alert Notifications -->
    <?php if (isset($_GET['comment_submitted'])): ?>
        <div class="alert alert-info">
            <span class="alert-icon"><i class="fas fa-clock"></i></span>
            <div>Your comment has been submitted and is awaiting approval.</div>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['access_requested'])): ?>
        <div class="alert alert-info">
            <span class="alert-icon"><i class="fas fa-key"></i></span>
            <div>Your access request has been submitted and is awaiting administrator approval.</div>
        </div>
    <?php endif; ?>

    <!-- Project Header -->
    <header class="project-detail-header reveal">
        <div class="project-meta-row">
            <span class="project-category-tag"><?= e($project['category']) ?></span>
            <?php if ($isRestricted): ?>
                <span class="project-restricted-tag"><i class="fas fa-lock"></i> Restricted Materials</span>
            <?php else: ?>
                <span class="badge-pill badge-success"><i class="fas fa-globe"></i> Public Access</span>
            <?php endif; ?>
        </div>

        <h1 class="project-detail-title"><?= e($project['title']) ?></h1>
        <p class="project-detail-tagline"><?= e(!empty($project['short_tagline']) ? $project['short_tagline'] : mb_strimwidth($project['description'] ?? '', 0, 140, '...')) ?></p>

        <div class="project-tech-tags" style="margin-top:1.25rem;">
            <?php foreach (explode(',', $project['technologies']) as $tech): ?>
                <span class="tech-tag"><?= e(trim($tech)) ?></span>
            <?php endforeach; ?>
        </div>
    </header>

    <!-- Main Hero Image / Thumbnail -->
    <?php if (!empty($project['thumbnail'])): ?>
        <div class="project-hero-media reveal">
            <img src="uploads/thumbnails/<?= e($project['thumbnail']) ?>" alt="<?= e($project['title']) ?>">
        </div>
    <?php endif; ?>

    <!-- Two-column Body: Main Details + Sidebar Links -->
    <div class="project-layout-grid">
        <!-- Main Content -->
        <div class="project-main-col">

            <!-- Overview -->
            <?php if (!empty($project['overview']) || !empty($project['description'])): ?>
                <section class="detail-block reveal">
                    <h2 class="detail-block-title"><i class="fas fa-compass"></i> Project Overview</h2>
                    <div class="detail-body">
                        <?= nl2br(e(!empty($project['overview']) ? $project['overview'] : $project['description'])) ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Problem Statement -->
            <?php if (!empty($project['problem_statement'])): ?>
                <section class="detail-block reveal">
                    <h2 class="detail-block-title"><i class="fas fa-exclamation-triangle"></i> Problem Statement</h2>
                    <div class="detail-body">
                        <?= nl2br(e($project['problem_statement'])) ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Objectives -->
            <?php if (!empty($project['objectives'])): ?>
                <section class="detail-block reveal">
                    <h2 class="detail-block-title"><i class="fas fa-bullseye"></i> Key Objectives</h2>
                    <div class="detail-body">
                        <ul class="detail-bullet-list">
                            <?php foreach (explode("\n", trim($project['objectives'])) as $obj):
                                $obj = trim($obj);
                                if (!empty($obj)): ?>
                                    <li><?= e($obj) ?></li>
                            <?php endif; endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Implementation Details -->
            <?php if (!empty($project['implementation_details'])): ?>
                <section class="detail-block reveal">
                    <h2 class="detail-block-title"><i class="fas fa-cogs"></i> Implementation Details</h2>
                    <div class="detail-body">
                        <?= nl2br(e($project['implementation_details'])) ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Screenshots Gallery -->
            <?php if (!empty($resources)): ?>
                <section class="detail-block reveal">
                    <h2 class="detail-block-title"><i class="fas fa-images"></i> Screenshots &amp; Interfaces</h2>
                    <div class="screenshots-gallery-grid">
                        <?php foreach ($resources as $res): ?>
                            <div class="gallery-item" data-lightbox="uploads/screenshots/<?= e($res['file_path']) ?>">
                                <img src="uploads/screenshots/<?= e($res['file_path']) ?>" alt="<?= e($res['caption'] ?? 'Screenshot') ?>" loading="lazy">
                                <?php if (!empty($res['caption'])): ?>
                                    <div class="gallery-caption"><?= e($res['caption']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Demonstration Video -->
            <?php if (!empty($project['video_url'])): ?>
                <section class="detail-block reveal">
                    <h2 class="detail-block-title"><i class="fas fa-play-circle"></i> Demonstration Video</h2>
                    <?php if ($hasAccess): ?>
                        <div class="video-container">
                            <?php
                            $vUrl = $project['video_url'];
                            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $vUrl, $matches)) {
                                echo '<iframe src="https://www.youtube.com/embed/' . e($matches[1]) . '" allowfullscreen></iframe>';
                            } else {
                                echo '<iframe src="' . e($vUrl) . '" allowfullscreen></iframe>';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="restricted-box">
                            <i class="fas fa-lock restricted-lock-icon"></i>
                            <h3>Demonstration Video Restricted</h3>
                            <p>Access to this project demonstration video is restricted. Please request access below.</p>
                            <a href="request_access.php?project_id=<?= $project['id'] ?>" class="btn btn-outline btn-sm">Request Access</a>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <!-- Project Report (PDF) -->
            <?php if (!empty($project['report_pdf'])): ?>
                <section class="detail-block reveal">
                    <h2 class="detail-block-title"><i class="fas fa-file-pdf"></i> Technical Documentation &amp; Report</h2>
                    <?php if ($hasAccess): ?>
                        <div class="doc-download-card">
                            <div style="display:flex;align-items:center;gap:1rem;">
                                <i class="fas fa-file-pdf" style="font-size:2rem;color:var(--primary-light);"></i>
                                <div>
                                    <div style="font-weight:700;color:#fff;"><?= e(basename($project['report_pdf'])) ?></div>
                                    <div style="font-size:0.8rem;color:var(--text-muted);">Comprehensive Project Report &amp; Benchmark Study</div>
                                </div>
                            </div>
                            <a href="<?= e($project['report_pdf']) ?>" class="btn btn-primary btn-sm" target="_blank" download>
                                <i class="fas fa-download"></i> Download Report
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="restricted-box">
                            <i class="fas fa-lock restricted-lock-icon"></i>
                            <h3>Documentation Restricted</h3>
                            <p>The comprehensive project report is protected under access control.</p>
                            <a href="request_access.php?project_id=<?= $project['id'] ?>" class="btn btn-outline btn-sm">Request Access</a>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

        </div>

        <!-- Sidebar Actions & Access Status -->
        <aside class="project-side-col">
            <!-- Quick Actions Card -->
            <div class="project-side-card reveal">
                <h3 class="side-card-title">Project Links</h3>
                <div class="side-links-stack">
                    <?php if (!empty($project['demo_url']) && $project['demo_url'] !== '#'): ?>
                        <?php if ($hasAccess): ?>
                            <a href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-full">
                                <i class="fas fa-external-link-alt"></i> Live Website
                            </a>
                        <?php else: ?>
                            <button class="btn btn-ghost btn-full" disabled style="opacity:0.6;">
                                <i class="fas fa-lock"></i> Live Demo (Restricted)
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($project['github_url'])): ?>
                        <?php if ($hasAccess): ?>
                            <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-full">
                                <i class="fab fa-github"></i> GitHub Source Code
                            </a>
                        <?php else: ?>
                            <button class="btn btn-ghost btn-full" disabled style="opacity:0.6;">
                                <i class="fas fa-lock"></i> GitHub (Restricted)
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Access Control Card -->
            <?php if ($isRestricted): ?>
                <div class="project-side-card reveal reveal-delay-1">
                    <h3 class="side-card-title"><i class="fas fa-shield-alt"></i> Access Control</h3>

                    <?php if ($hasAccess): ?>
                        <div class="access-pill-approved">
                            <i class="fas fa-check-circle"></i> Access Granted
                        </div>
                        <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.75rem;">
                            You are authorized to view and download all materials for this project.
                        </p>
                    <?php elseif ($accessStatus === 'pending'): ?>
                        <div class="access-pill-pending">
                            <i class="fas fa-hourglass-half"></i> Request Pending
                        </div>
                        <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.75rem;">
                            Your request is awaiting administrative review. You will be granted access once approved.
                        </p>
                    <?php elseif ($accessStatus === 'rejected'): ?>
                        <div class="access-pill-rejected">
                            <i class="fas fa-times-circle"></i> Request Not Approved
                        </div>
                        <p style="font-size:0.85rem;color:var(--text-secondary);margin-top:0.75rem;">
                            Your previous access request was declined.
                        </p>
                        <a href="request_access.php?project_id=<?= $project['id'] ?>" class="btn btn-outline btn-sm btn-full" style="margin-top:0.75rem;">
                            Submit New Request
                        </a>
                    <?php else: ?>
                        <p style="font-size:0.88rem;color:var(--text-secondary);line-height:1.6;margin-bottom:1rem;">
                            Certain source code, benchmarks, and research reports for this project are confidential.
                        </p>
                        <a href="request_access.php?project_id=<?= $project['id'] ?>" class="btn btn-primary btn-full">
                            <i class="fas fa-key"></i> Request Access
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </aside>
    </div>

    <!-- ===================================================================== -->
    <!-- PROJECT-SPECIFIC COMMENTS SECTION                                      -->
    <!-- Only comments for this particular project ID are displayed/stored.     -->
    <!-- ===================================================================== -->
    <section class="project-comments-section reveal">
        <div class="comments-header">
            <h2 style="font-size:1.5rem;font-weight:700;color:#fff;">
                <i class="fas fa-comments" style="color:var(--primary-light);"></i> Project Discussion &amp; Feedback
            </h2>
            <p style="color:var(--text-secondary);font-size:0.9rem;margin-top:0.25rem;">
                Comments and feedback specifically regarding <em><?= e($project['title']) ?></em>.
            </p>
        </div>

        <!-- Approved Comments Stream -->
        <div class="comments-stream">
            <?php if (empty($comments)): ?>
                <div class="no-comments-box">
                    <p>No comments posted on this project yet. Share your thoughts below!</p>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <article class="comment-item">
                        <div class="comment-author-badge">
                            <?= strtoupper(substr($c['author_name'], 0, 1)) ?>
                        </div>
                        <div class="comment-content-body">
                            <div class="comment-meta">
                                <span class="comment-author-name"><?= e($c['author_name']) ?></span>
                                <span class="comment-date"><?= date('M d, Y', strtotime($c['created_at'])) ?></span>
                            </div>
                            <div class="comment-text">
                                <?= nl2br(e($c['comment_text'])) ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Add Project Comment Form -->
        <div class="comment-form-card">
            <h3 style="font-size:1.15rem;font-weight:700;color:#fff;margin-bottom:0.5rem;">Leave a Comment</h3>
            <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:1.5rem;">
                All comments are reviewed by the administrator before being published publicly.
            </p>

            <form action="submit_comment.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="comm_author">Your Name *</label>
                        <input type="text" id="comm_author" name="author_name" class="form-control"
                               placeholder="e.g. Recruiter / Peer Engineer" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="comm_email">Email Address * (Never displayed publicly)</label>
                        <input type="email" id="comm_email" name="author_email" class="form-control"
                               placeholder="e.g. name@organization.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="comm_text">Comment *</label>
                    <textarea id="comm_text" name="comment_text" class="form-control" rows="4"
                              placeholder="Write your feedback, question, or technical comment about this project..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Comment
                </button>
            </form>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
