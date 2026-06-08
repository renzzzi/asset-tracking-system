<?php
// models/AuditLog.php
require_once __DIR__ . '/../config/db.php';

class AuditLog {

    /**
     * Inserts an audit log entry securely converting arrays to JSON
     * @return bool
     */
    public static function log($assetId, $userId, $action, $oldData = null, $newData = null, $notes = null) {
        try {
            $oldJson = is_array($oldData) ? json_encode($oldData) : $oldData;
            $newJson = is_array($newData) ? json_encode($newData) : $newData;

            $sql = "INSERT INTO audit_logs (asset_id, user_id, action, old_value, new_value, notes) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = getDB()->prepare($sql);
            return $stmt->execute([$assetId, $userId, $action, $oldJson, $newJson, $notes]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Retrieves all log entries for a single asset, newest first
     * @return array
     */
    public static function getByAsset($assetId) {
        try {
            $sql = "SELECT al.*, u.full_name as user_name 
                    FROM audit_logs al 
                    LEFT JOIN users u ON al.user_id = u.id 
                    WHERE al.asset_id = ? 
                    ORDER BY al.created_at DESC";
            $stmt = getDB()->prepare($sql);
            $stmt->execute([$assetId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Retrieves the most recent N log entries across the entire system
     * @return array
     */
    public static function getRecent($limit = 10) {
        try {
            $sql = "SELECT al.*, u.full_name as user_name, a.name as asset_name, a.asset_tag 
                    FROM audit_logs al 
                    LEFT JOIN users u ON al.user_id = u.id 
                    LEFT JOIN assets a ON al.asset_id = a.id 
                    ORDER BY al.created_at DESC 
                    LIMIT ?";
            $stmt = getDB()->prepare($sql);
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}