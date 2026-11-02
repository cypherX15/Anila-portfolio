<?php
/**
 * ANILA S — Software Developer Portfolio Homepage
 * Clean, minimal, modern, dark aesthetic.
 * Focuses on high-impact projects, concise editorial about section, and minimal skills grid.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$pdo = getDBConnection();

// Fetch Profile
$profile = $pdo->query("SELECT * FROM profile_info LIMIT 1")->fetch();
if (!$profile) {
    $profile = [
        'full_name'   => 'Anila S',
        'title'       => 'Information Technology Engineer & Developer',
        'headline'    => 'Building intelligent systems, distributed architectures, and efficient software solutions.',
        'short_bio'   => 'Information Technology graduate from Government Engineering College Sreekrishnapuram. Passionate about systems programming, data compression, swarm robotics, and full-stack engineering with a focus on practical problem solving.',
        'email'       => 'anila994785@gmail.com',
        'phone'       => '9961542447',
        'github'      => 'https://github.com/cypherX15',
        'location'    => 'Palakkad, Kerala, India',
        'resume_path' => 'Resume .pdf',
    ];
}

// Fetch Education (Compact)
$education = $pdo->query("SELECT * FROM education ORDER BY display_order ASC")->fetchAll();

// Fetch Skills (Clean categorized grid, strictly no percentage bars)
$skillsQuery = $pdo->query("SELECT * FROM skills ORDER BY display_order ASC")->fetchAll();
$skillsByCategory = [];
foreach ($skillsQuery as $sk) {
    $skillsByCategory[$sk['category']][] = $sk['skill_name'];
}

// Fetch Certifications & Achievements for public display
try {
    $certifications = $pdo->query("SELECT * FROM certifications_achievements ORDER BY display_order ASC, id ASC")->fetchAll();
} catch (Exception $e) {
    $certifications = [];
}

// Fetch Projects (Main Highlight)
$projects = $pdo->query("SELECT * FROM projects ORDER BY display_order ASC, id DESC")->fetchAll();

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- System Status Notification Alerts -->
<div style="max-width:1150px;margin:1.25rem auto 0;padding:0 1.5rem;">
    <?php if (isset($_GET['contact_success'])): ?>
        <div class="alert alert-success">
            <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
            <div>Thank you! Your message has been sent successfully. I will get back to you shortly.</div>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['access_submitted'])): ?>
        <div class="alert alert-info">
            <span class="alert-icon"><i class="fas fa-key"></i></span>
            <div>Your access request has been submitted for administrative review.</div>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- 1. HERO SECTION (Minimal, High-Impact, Creative)                          -->
<!-- ========================================================================= -->
<section id="home" class="hero-section">
    <div class="hero-grid">
        <div class="hero-content">
            <div class="hero-greeting reveal">
                <span class="greeting-dot"></span>
                <span>Available for Engineering Roles &amp; Collaborations</span>
            </div>

            <h1 class="hero-title reveal reveal-delay-1">
                Anila S
            </h1>

            <div class="hero-tagline reveal reveal-delay-2">
                <?= e($profile['headline'] ?? 'Building intelligent systems, distributed architectures, and efficient software solutions.') ?>
            </div>

            <p class="hero-bio reveal reveal-delay-3">
                <?= e($profile['short_bio'] ?? $profile['bio'] ?? '') ?>
            </p>

            <div class="hero-actions reveal">
                <a href="#projects" class="btn btn-primary btn-lg">
                    <i class="fas fa-arrow-down"></i> Explore My Projects
                </a>
                <a href="#contact" class="btn btn-outline btn-lg">
                    <i class="fas fa-paper-plane"></i> Contact Me
                </a>
            </div>
        </div>

        <div class="hero-visual reveal reveal-delay-2">
            <!-- Modern Abstract Code & Architectural Graphic -->
            <div class="abstract-graphic-wrap">
                <div class="abstract-halo"></div>
                <div class="abstract-card">
                    <div class="abstract-header">
                        <span class="dot-red"></span>
                        <span class="dot-yellow"></span>
                        <span class="dot-green"></span>
                        <span class="abstract-file">anila_core.sys</span>
                    </div>
                    <div class="code-preview">
<span class="code-comment">// Distributed &amp; Algorithmic Engineering</span>
<span class="code-keyword">class</span> <span class="code-entity">SoftwareEngineer</span> {
  <span class="code-prop">name</span> = <span class="code-string">"Anila S"</span>;
  <span class="code-prop">focus</span> = [
    <span class="code-string">"Systems Architecture"</span>,
    <span class="code-string">"Data Compression"</span>,
    <span class="code-string">"Swarm Robotics"</span>
  ];
  <span class="code-fn">execute</span>() {
    <span class="code-keyword">return</span> <span class="code-string">"Building innovative solutions"</span>;
  }
}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- ========================================================================= -->
<!-- 2. ABOUT & COMPACT EDUCATION (Extremely Minimal, Editorial Layout)        -->
<!-- ========================================================================= -->
<section id="about" class="section">
    <div class="section-header">
        <div class="section-tag">Background</div>
        <h2 class="section-title reveal">About Me</h2>
    </div>

    <div class="about-grid">
        <div class="about-lead reveal">
            <?php
            $bioText = !empty($profile['bio']) ? $profile['bio'] : "I am an Information Technology Engineer with a focus on writing clean, high-performance software and exploring practical distributed technologies.\n\nMy work spans low-level algorithm optimization like token-based document compression, autonomous multi-agent robotics coordination, and modern web application development.";
            $paragraphs = preg_split('/\r\n\r\n|\n\n|\r\r/', trim($bioText));
            foreach ($paragraphs as $i => $para):
                if (empty(trim($para))) continue;
            ?>
                <p <?= $i > 0 ? 'style="margin-top:1.25rem;"' : '' ?>>
                    <?= nl2br(e(trim($para))) ?>
                </p>
            <?php endforeach; ?>

            <?php 
                $resumeLink = 'Resume .pdf';
                if (!empty($profile['resume_path'])) {
                    $resumeLink = file_exists(__DIR__ . '/uploads/' . $profile['resume_path']) 
                        ? 'uploads/' . e($profile['resume_path']) 
                        : e($profile['resume_path']);
                }
            ?>
            <div style="margin-top:2rem;">
                <a href="<?= $resumeLink ?>" class="btn btn-ghost" download target="_blank">
                    <i class="fas fa-file-pdf"></i> Download Official Resume
                </a>
            </div>
        </div>

        <div class="about-education reveal reveal-delay-1">
            <h3 style="font-size:1.15rem;font-weight:700;color:var(--text-primary);margin-bottom:1.5rem;display:flex;align-items:center;gap:0.6rem;">
                <i class="fas fa-graduation-cap" style="color:var(--primary-light);"></i> Education
            </h3>

            <div class="education-compact-list">
                <?php foreach ($education as $edu): ?>
                    <div class="edu-card">
                        <div class="edu-degree"><?= e($edu['degree']) ?></div>
                        <div class="edu-school"><?= e($edu['institution']) ?></div>
                        <div class="edu-meta">
                            <span><i class="fas fa-map-marker-alt" style="font-size:0.75rem;"></i> <?= e($edu['location'] ?? 'Palakkad, Kerala') ?></span>
                            <span>&bull;</span>
                            <span><?= e($edu['completion_date']) ?></span>
                            <?php if (!empty($edu['score'])): ?>
                                <span>&bull;</span>
                                <span class="edu-badge"><?= e($edu['score']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- ========================================================================= -->
<!-- 3. PROJECTS SECTION (THE MAIN HIGHLIGHT — Prominent, Visual Showcase)     -->
<!-- ========================================================================= -->
<section id="projects" class="section">
    <div class="section-header">
        <div class="section-tag">Featured Work</div>
        <h2 class="section-title reveal">Projects &amp; Systems</h2>
        <p class="section-subtitle reveal">
            Real software systems, algorithms, and technical engineering implementations.
        </p>
    </div>

    <div class="projects-container">
        <?php foreach ($projects as $index => $project):
            $isEven       = ($index % 2 === 1);
            $isRestricted = !empty($project['is_restricted']);
            $tagline      = !empty($project['short_tagline']) ? $project['short_tagline'] : mb_strimwidth($project['description'] ?? '', 0, 140, '...');
        ?>
            <article class="project-showcase <?= $isEven ? 'project-showcase-reversed' : '' ?> reveal">
                <!-- Large Project Visual Preview -->
                <div class="project-showcase-preview">
                    <a href="project.php?id=<?= $project['id'] ?>" class="preview-link-wrap">
                        <?php if (!empty($project['thumbnail'])): ?>
                            <img src="uploads/thumbnails/<?= e($project['thumbnail']) ?>"
                                 alt="<?= e($project['title']) ?> Preview"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="project-preview-fallback">
                                <i class="fas fa-code"></i>
                            </div>
                        <?php endif; ?>
                        <div class="preview-overlay">
                            <span class="preview-hover-btn">
                                <i class="fas fa-arrow-right"></i> View Details
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Project Content -->
                <div class="project-showcase-content">
                    <div class="project-meta-row">
                        <span class="project-category-tag"><?= e($project['category']) ?></span>
                        <?php if ($isRestricted): ?>
                            <span class="project-restricted-tag"><i class="fas fa-lock"></i> Restricted Materials</span>
                        <?php endif; ?>
                    </div>

                    <h3 class="project-showcase-title">
                        <a href="project.php?id=<?= $project['id'] ?>"><?= e($project['title']) ?></a>
                    </h3>

                    <p class="project-showcase-tagline">
                        <?= e($tagline) ?>
                    </p>

                    <div class="project-tech-tags">
                        <?php foreach (explode(',', $project['technologies']) as $tech): ?>
                            <span class="tech-tag"><?= e(trim($tech)) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="project-cta-row">
                        <a href="project.php?id=<?= $project['id'] ?>" class="btn btn-primary">
                            <span>View Project</span> <i class="fas fa-arrow-right" style="font-size:0.8rem;"></i>
                        </a>

                        <?php if (!empty($project['github_url'])): ?>
                            <a href="<?= e($project['github_url']) ?>" target="_blank" rel="noopener" class="btn btn-ghost" title="Source Code">
                                <i class="fab fa-github"></i> GitHub
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($project['demo_url']) && $project['demo_url'] !== '#'): ?>
                            <a href="<?= e($project['demo_url']) ?>" target="_blank" rel="noopener" class="btn btn-outline" title="Live Preview">
                                <i class="fas fa-external-link-alt"></i> Live Demo
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<div class="section-divider"></div>

<!-- ========================================================================= -->
<!-- 4. TECHNICAL SKILLS SECTION (Clean Tag Grid, No Percentage Indicators)     -->
<!-- ========================================================================= -->
<section id="skills" class="section">
    <div class="section-header">
        <div class="section-tag">Competencies</div>
        <h2 class="section-title reveal">Technical Skills</h2>
        <p class="section-subtitle reveal">
            Core technologies and tools applied across software and systems engineering.
        </p>
    </div>

    <div class="skills-clean-grid">
        <?php
        $categoryIcons = [
            'Programming Languages' => 'fas fa-terminal',
            'Web Development'      => 'fas fa-globe',
            'App Development'      => 'fas fa-mobile-alt',
            'Database and Tools'   => 'fas fa-database',
        ];
        foreach ($skillsByCategory as $catName => $skillList):
            $icon = $categoryIcons[$catName] ?? 'fas fa-code';
        ?>
            <div class="skill-category-card reveal">
                <div class="skill-cat-header">
                    <i class="<?= $icon ?>"></i>
                    <h3><?= e($catName) ?></h3>
                </div>
                <div class="skill-chips-wrap">
                    <?php foreach ($skillList as $sName): ?>
                        <span class="skill-chip"><?= e($sName) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="section-divider"></div>

<!-- ========================================================================= -->
<!-- X. CERTIFICATIONS, ACHIEVEMENTS & SPORTS                               -->
<!-- ========================================================================= -->
<?php if (!empty($certifications)): ?>
<section id="certifications" class="section" style="padding-top:5rem;padding-bottom:5rem;">
    <div class="section-header">
        <div class="section-tag">Recognition</div>
        <h2 class="section-title reveal">Certifications &amp; Achievements</h2>
    </div>

    <?php
    $certs   = array_filter($certifications, fn($c) => $c['type'] === 'certification');
    $achiev  = array_filter($certifications, fn($c) => $c['type'] === 'achievement');
    $sports  = array_filter($certifications, fn($c) => $c['type'] === 'sports');
    ?>

    <div class="certs-grid reveal">
        <?php foreach ($certifications as $cert): ?>
            <div class="cert-card-pub">
                <?php if ($cert['type'] === 'certification'): ?>
                    <div class="cert-pub-icon" style="color:var(--primary);background:rgba(139,92,246,0.1);">
                        <i class="fas fa-certificate"></i>
                    </div>
                <?php elseif ($cert['type'] === 'sports'): ?>
                    <div class="cert-pub-icon" style="color:var(--cyan);background:rgba(6,182,212,0.1);">
                        <i class="fas fa-running"></i>
                    </div>
                <?php else: ?>
                    <div class="cert-pub-icon" style="color:var(--warning);background:rgba(245,158,11,0.1);">
                        <i class="fas fa-trophy"></i>
                    </div>
                <?php endif; ?>
                <div class="cert-pub-body">
                    <h4 class="cert-pub-title"><?= e($cert['title']) ?></h4>
                    <?php if (!empty($cert['issuer_org'])): ?>
                        <p class="cert-pub-org"><?= e($cert['issuer_org']) ?>
                            <?php if (!empty($cert['date_text'])): ?>
                                <span class="cert-pub-date">&nbsp;&bull;&nbsp;<?= e($cert['date_text']) ?></span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($cert['description'])): ?>
                        <p class="cert-pub-desc"><?= e($cert['description']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($cert['document_file'])): ?>
                        <div style="margin-top:0.75rem;">
                            <a href="uploads/certificates/<?= e($cert['document_file']) ?>" target="_blank" class="cert-doc-badge">
                                <i class="fas fa-file-alt"></i> View Supporting Document
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 5. CONTACT SECTION & MESSAGING SYSTEM                                    -->
<!-- ========================================================================= -->
<section id="contact" class="section">
    <div class="section-header">
        <div class="section-tag">Inquiries</div>
        <h2 class="section-title reveal">Let's Build Something Great Together</h2>
        <p class="section-subtitle reveal">
            Have an engineering role, collaborative project, or technical question? Send a message directly.
        </p>
    </div>

    <div class="contact-minimal-wrap reveal">
        <!-- Direct Links Strip -->
        <div class="contact-direct-strip">
            <a href="mailto:<?= e($profile['email']) ?>" class="contact-strip-item">
                <i class="fas fa-envelope"></i>
                <span><?= e($profile['email']) ?></span>
            </a>
            <a href="<?= e($profile['github']) ?>" target="_blank" rel="noopener" class="contact-strip-item">
                <i class="fab fa-github"></i>
                <span>github.com/cypherX15</span>
            </a>
            <span class="contact-strip-item">
                <i class="fas fa-map-marker-alt"></i>
                <span><?= e($profile['location'] ?? 'Palakkad, Kerala, India') ?></span>
            </span>
        </div>

        <!-- Clean Contact Form -->
        <div class="contact-card">
            <form action="submit_message.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="msg_name">Your Name *</label>
                        <input type="text" id="msg_name" name="name" class="form-control"
                               placeholder="e.g. Recruiter / Collaborator" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="msg_email">Your Email *</label>
                        <input type="email" id="msg_email" name="email" class="form-control"
                               placeholder="e.g. name@company.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="msg_subject">Subject *</label>
                    <input type="text" id="msg_subject" name="subject" class="form-control"
                           placeholder="Project Collaboration / Career Opportunity" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="msg_body">Message *</label>
                    <textarea id="msg_body" name="message" class="form-control" rows="5"
                              placeholder="Hello Anila, I reviewed your work on distributed systems..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
