<?php
/**
 * Remote MySQL Database Connection Test Diagnostic
 * Access this page at: http://localhost/portfolio/test_db.php
 */

require_once __DIR__ . '/config/db.php';

$startTime = microtime(true);
$connected = false;
$errorMsg = null;
$serverInfo = '';
$tableList = [];

try {
    $pdo = getDBConnection();
    $connected = true;
    $latencyMs = round((microtime(true) - $startTime) * 1000, 2);
    $serverInfo = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    
    // Check tables in current database
    $stmt = $pdo->query("SHOW TABLES");
    $tableList = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Diagnostic - Anila S Portfolio</title>
    <style>
        :root {
            --bg: #0b0f19;
            --card: #111827;
            --accent: #10b981;
            --danger: #ef4444;
            --text: #f3f4f6;
            --text-muted: #9ca3af;
            --border: #1f2937;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 2.5rem 1rem;
            display: flex;
            justify-content: center;
        }
        .container {
            max-width: 700px;
            width: 100%;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .status-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .badge {
            padding: 0.4rem 1rem;
            border-radius: 999px;
            font-size: 0.9rem;
            font-weight: 700;
        }
        .badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent);
            border: 1px solid var(--accent);
        }
        .badge-danger {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            border: 1px solid var(--danger);
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .info-card {
            background: #090d16;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
        }
        .info-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }
        .info-value {
            font-size: 1.05rem;
            font-weight: 600;
            color: #fff;
            word-break: break-all;
        }
        .table-list {
            background: #090d16;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .table-pill {
            display: inline-block;
            background: #1f2937;
            padding: 0.25rem 0.6rem;
            margin: 0.25rem;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #d1d5db;
        }
        .action-btns {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn {
            flex: 1;
            padding: 0.85rem;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-green { background: var(--accent); color: #000; }
        .btn-dark { background: #1f2937; color: #fff; border: 1px solid #374151; }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-header">
            <div>
                <h2 style="margin:0;color:#fff;">Remote MySQL Diagnostic</h2>
                <small style="color:var(--text-muted);">Verifying Apache Server &rarr; Remote Database link</small>
            </div>
            <div>
                <?php if ($connected): ?>
                    <span class="badge badge-success">CONNECTED</span>
                <?php else: ?>
                    <span class="badge badge-danger">FAILED</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-label">Host Configured</div>
                <div class="info-value"><?= htmlspecialchars(DB_HOST) ?>:<?= htmlspecialchars(DB_PORT) ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">Database Name</div>
                <div class="info-value"><?= htmlspecialchars(DB_NAME) ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">Database User</div>
                <div class="info-value"><?= htmlspecialchars(DB_USER) ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">Connection Latency</div>
                <div class="info-value" style="color:var(--accent);"><?= $connected ? $latencyMs . ' ms' : 'N/A' ?></div>
            </div>
        </div>

        <?php if ($connected): ?>
            <div class="info-card" style="margin-bottom:1.5rem;">
                <div class="info-label">MySQL Server Version</div>
                <div class="info-value" style="font-size:0.95rem;"><?= htmlspecialchars($serverInfo) ?></div>
            </div>

            <div class="table-list">
                <div class="info-label" style="margin-bottom:0.75rem;">Discovered Tables (<?= count($tableList) ?>)</div>
                <?php if (empty($tableList)): ?>
                    <p style="color:#f59e0b;font-size:0.9rem;margin:0;">No tables found yet. Click below to run the Database Seeder.</p>
                <?php else: ?>
                    <?php foreach ($tableList as $tbl): ?>
                        <span class="table-pill">📁 <?= htmlspecialchars($tbl) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="background:rgba(239,68,68,0.1);border:1px solid var(--danger);border-radius:10px;padding:1.25rem;">
                <h4 style="margin:0 0 0.5rem 0;color:var(--danger);">Error Message:</h4>
                <code style="color:#fca5a5;word-break:break-all;"><?= htmlspecialchars($errorMsg) ?></code>
            </div>
        <?php endif; ?>

        <div class="action-btns">
            <a href="database/seed.php" class="btn btn-green">⚡ Run Database Seeder</a>
            <a href="index.php" class="btn btn-dark">🏠 Visit Portfolio</a>
        </div>
    </div>
</body>
</html>
