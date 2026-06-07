<?php
// models/Asset.php
require_once __DIR__ . '/../config/db.php';

class Asset {

    /**
     * Retrieves all assets with dynamic filtering
     * @return array
     */
    public static function getAll($filters = []) {
        try {
            $sql = "SELECT a.*, c.name as category_name, u.full_name as creator_name 
                    FROM assets a 
                    LEFT JOIN categories c ON a.category_id = c.id 
                    LEFT JOIN users u ON a.created_by = u.id 
                    WHERE 1=1";
            $params = [];

            if (!empty($filters['category_id'])) {
                $sql .= " AND a.category_id = ?";
                $params[] = $filters['category_id'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND a.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (a.name LIKE ? OR a.asset_tag LIKE ? OR a.serial_number LIKE ? OR a.location LIKE ?)";
                $keyword = '%' . $filters['search'] . '%';
                array_push($params, $keyword, $keyword, $keyword, $keyword);
            }

            $sql .= " ORDER BY a.created_at DESC";
            
            $stmt = getDB()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Retrieves a single asset by ID joined with category name
     * @return array|false
     */
    public static function getById($id) {
        try {
            $sql = "SELECT a.*, c.name as category_name 
                    FROM assets a 
                    LEFT JOIN categories c ON a.category_id = c.id 
                    WHERE a.id = ? LIMIT 1";
            $stmt = getDB()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Creates a new asset and auto-generates asset_tag
     * @return int|false
     */
    public static function create($data) {
        try {
            $db = getDB();
            $db->beginTransaction();

            // Insert with temporary asset tag to satisfy UNIQUE NOT NULL constraint
            $tempTag = 'TEMP-' . uniqid();
            $sql = "INSERT INTO assets (asset_tag, name, description, category_id, serial_number, location, status, purchase_date, purchase_cost, assigned_to, notes, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $tempTag,
                $data['name'],
                $data['description'] ?? null,
                $data['category_id'],
                $data['serial_number'] ?? null,
                $data['location'] ?? null,
                $data['status'] ?? 'active',
                $data['purchase_date'] ?? null,
                $data['purchase_cost'] ?? null,
                $data['assigned_to'] ?? null,
                $data['notes'] ?? null,
                $data['created_by'] ?? null
            ]);

            $newId = $db->lastInsertId();

            // Update with properly padded Asset Tag
            $realTag = 'ASSET-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
            $updateStmt = $db->prepare("UPDATE assets SET asset_tag = ? WHERE id = ?");
            $updateStmt->execute([$realTag, $newId]);

            $db->commit();
            return $newId;
        } catch (PDOException $e) {
            if (isset($db)) $db->rollBack();
            return false;
        }
    }

    /**
     * Updates an existing asset
     * @return bool
     */
    public static function update($id, $data) {
        try {
            $sql = "UPDATE assets SET 
                    name = ?, description = ?, category_id = ?, serial_number = ?, 
                    location = ?, status = ?, purchase_date = ?, purchase_cost = ?, 
                    assigned_to = ?, notes = ? 
                    WHERE id = ?";
            $stmt = getDB()->prepare($sql);
            return $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['category_id'],
                $data['serial_number'] ?? null,
                $data['location'] ?? null,
                $data['status'] ?? 'active',
                $data['purchase_date'] ?? null,
                $data['purchase_cost'] ?? null,
                $data['assigned_to'] ?? null,
                $data['notes'] ?? null,
                $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Deletes an asset by ID
     * @return bool
     */
    public static function delete($id) {
        try {
            $stmt = getDB()->prepare("DELETE FROM assets WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Returns total asset counts grouped by status
     * @return array
     */
    public static function getCountByStatus() {
        try {
            $stmt = getDB()->query("SELECT status, COUNT(id) as count FROM assets GROUP BY status");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Returns total asset counts grouped by category
     * @return array
     */
    public static function getCountByCategory() {
        try {
            $sql = "SELECT c.name as category_name, COUNT(a.id) as count 
                    FROM categories c 
                    LEFT JOIN assets a ON c.id = a.category_id 
                    GROUP BY c.id";
            $stmt = getDB()->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}