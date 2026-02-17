<?php
/**
 * Auto-complete purchases after package duration has passed.
 *
 * Usage (CLI): php auto_complete_purchases.php
 * Recommended: run daily via cron / Task Scheduler.
 */
require_once __DIR__ . '/database.php';

function parseDurationToDays($duration) {
    // Examples: "30 days", "12 weeks", "6 months"
    $duration = trim(strtolower($duration));
    if (!$duration) return null;

    if (preg_match('/^(\d+)\s*(day|days)$/', $duration, $m)) {
        return (int)$m[1];
    }
    if (preg_match('/^(\d+)\s*(week|weeks)$/', $duration, $m)) {
        return (int)$m[1] * 7;
    }
    if (preg_match('/^(\d+)\s*(month|months)$/', $duration, $m)) {
        return (int)$m[1] * 30;
    }
    if (preg_match('/^(\d+)\s*(year|years)$/', $duration, $m)) {
        return (int)$m[1] * 365;
    }

    // fallback: try numeric only
    if (preg_match('/^(\d+)$/', $duration, $m)) {
        return (int)$m[1];
    }
    return null;
}

try {
    // Find active purchases that are not yet completed
    $stmt = $pdo->query("SELECT pur.id as purchase_id, pur.user_id, pur.package_id, pur.purchase_date, pur.status, pk.duration FROM purchases pur JOIN packages pk ON pur.package_id = pk.id WHERE pur.is_active = 1 AND (pur.status IS NULL OR pur.status != 'completed')");
    $rows = $stmt->fetchAll();

    $completed = 0;
    foreach ($rows as $row) {
        $days = parseDurationToDays($row['duration']);
        if ($days === null) continue;

        $purchaseDate = new DateTime($row['purchase_date']);
        $due = (clone $purchaseDate)->modify("+{$days} days");
        $now = new DateTime();
        if ($now >= $due) {
            $upd = $pdo->prepare("UPDATE purchases SET status = 'completed', completed_at = NOW() WHERE id = ?");
            $upd->execute([$row['purchase_id']]);
            $completed++;
        }
    }

    $output = "Auto-complete finished. Purchases completed: " . $completed . "\n";
    if (php_sapi_name() === 'cli') {
        echo $output;
    } else {
        echo nl2br(htmlspecialchars($output));
    }
} catch (PDOException $e) {
    if (php_sapi_name() === 'cli') {
        echo "Auto-complete failed: " . $e->getMessage() . "\n";
    } else {
        echo "Auto-complete failed: " . htmlspecialchars($e->getMessage());
    }
}

?>
