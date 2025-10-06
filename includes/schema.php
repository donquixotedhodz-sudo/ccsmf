<?php
// Lightweight schema guard to ensure expected columns exist in dev environments

function ensureSmfStatusColumn(PDO $pdo): void
{
    try {
        $check = $pdo->query("SHOW COLUMNS FROM smf_transactions LIKE 'status'");
        $exists = $check && $check->fetch();
        if (!$exists) {
            $pdo->exec("ALTER TABLE smf_transactions ADD COLUMN `status` ENUM('pending','under_review','approved','rejected','updated') NOT NULL DEFAULT 'pending' AFTER `photo_path`");
        }
    } catch (Throwable $e) {
        // Silently ignore in case of permission issues; the app may still function without status in limited mode
    }
}

function ensureSmfProgramColumn(PDO $pdo): void
{
    try {
        $check = $pdo->query("SHOW COLUMNS FROM smf_transactions LIKE 'program'");
        $exists = $check && $check->fetch();
        if (!$exists) {
            $pdo->exec("ALTER TABLE smf_transactions ADD COLUMN `program` ENUM('BSBA','BSIS','BMMA','BSA','BSTM','BSED','BEED','BCAED') NULL AFTER `student_identifier`");
        }
    } catch (Throwable $e) {
        // Ignore; app still functions without program in limited mode
    }
}