<?php
// models/TransactionLog.php
require_once __DIR__ . '/../config/db.php';

class TransactionLog {

    /**
     * Create a checkout transaction record.
     */
    public static function create($data) {
        try {
            $sql = "INSERT INTO transaction_logs 
                    (asset_id, user_id, checked_out_by, due_date, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = getDB()->prepare($sql);
            return $stmt->execute([
                (int)$data['asset_id'],
                isset($data['user_id']) ? (int)$data['user_id'] : null,
                isset($data['checked_out_by']) ? (int)$data['checked_out_by'] : null,
                $data['due_date'],
                $data['status'] ?? 'checked_out',
                $data['notes'] ?? null,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Return the active (checked_out) transaction for an asset.
     */
    public static function getActiveByAsset($assetId) {
        try {
            $sql = "SELECT tl.*, u.full_name as user_name, c.full_name as checked_out_by_name,
                           CASE WHEN tl.due_date < CURDATE() THEN 1 ELSE 0 END as is_overdue
                    FROM transaction_logs tl
                    LEFT JOIN users u ON tl.user_id = u.id
                    LEFT JOIN users c ON tl.checked_out_by = c.id
                    WHERE tl.asset_id = ? AND tl.status = 'checked_out'
                    ORDER BY tl.checked_out_at DESC
                    LIMIT 1";
            $stmt = getDB()->prepare($sql);
            $stmt->execute([(int)$assetId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Mark a transaction as returned with optional condition and notes.
     * Condition values: 'good' | 'damaged' | 'needs_repair'
     */
    public static function markReturned($id, $condition = 'good', $notes = null) {
        try {
            $sql = "UPDATE transaction_logs
                    SET status      = 'returned',
                        returned_at = NOW(),
                        condition   = ?,
                        notes       = CASE WHEN ? IS NOT NULL THEN ? ELSE notes END
                    WHERE id = ? AND status = 'checked_out'";
            $stmt = getDB()->prepare($sql);
            return $stmt->execute([$condition, $notes, $notes, (int)$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get recent transactions.
     */
    public static function getRecent($limit = 10) {
        try {
            $sql = "SELECT tl.*, a.name as asset_name, u.full_name as user_name
                    FROM transaction_logs tl
                    LEFT JOIN assets a ON tl.asset_id = a.id
                    LEFT JOIN users u ON tl.user_id = u.id
                    ORDER BY tl.checked_out_at DESC
                    LIMIT ?";
            $stmt = getDB()->prepare($sql);
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get all currently overdue transactions (checked_out past due_date).
     */
    public static function getOverdue() {
        try {
            $sql = "SELECT tl.*, 
                           a.name as asset_name, a.asset_tag,
                           u.full_name as user_name,
                           DATEDIFF(CURDATE(), tl.due_date) as days_overdue
                    FROM transaction_logs tl
                    LEFT JOIN assets a ON tl.asset_id = a.id
                    LEFT JOIN users u  ON tl.user_id  = u.id
                    WHERE tl.status = 'checked_out'
                      AND tl.due_date < CURDATE()
                    ORDER BY tl.due_date ASC";
            $stmt = getDB()->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Count of currently overdue transactions.
     */
    public static function countOverdue() {
        try {
            $stmt = getDB()->query(
                "SELECT COUNT(*) as cnt FROM transaction_logs 
                 WHERE status = 'checked_out' AND due_date < CURDATE()"
            );
            $row = $stmt->fetch();
            return (int)($row['cnt'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }
}