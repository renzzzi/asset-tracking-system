<?php
// models/Category.php
require_once __DIR__ . '/../config/db.php';

class Category {

    /**
     * Retrieves all categories ordered by name
     * @return array
     */
    public static function getAll() {
        try {
            $stmt = getDB()->query("SELECT * FROM categories ORDER BY name ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Retrieves a single category by ID
     * @return array|false
     */
    public static function getById($id) {
        try {
            $stmt = getDB()->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Creates a new category and returns the new ID
     * @return int|false
     */
    public static function create($data) {
        try {
            $stmt = getDB()->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$data['name'], $data['description'] ?? null]);
            return getDB()->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Updates an existing category
     * @return bool
     */
    public static function update($id, $data) {
        try {
            $stmt = getDB()->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            return $stmt->execute([$data['name'], $data['description'] ?? null, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Deletes a category if no assets are attached to it
     * @return bool
     */
    public static function delete($id) {
        try {
            // Check dependency
            $checkStmt = getDB()->prepare("SELECT COUNT(*) as cnt FROM assets WHERE category_id = ?");
            $checkStmt->execute([$id]);
            $result = $checkStmt->fetch();
            
            if ($result['cnt'] > 0) {
                return false; // Cannot delete, assets depend on this category
            }

            $stmt = getDB()->prepare("DELETE FROM categories WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Retrieves all categories with their respective asset count
     * @return array
     */
    public static function getWithAssetCount() {
        try {
            $sql = "SELECT c.*, COUNT(a.id) as asset_count 
                    FROM categories c 
                    LEFT JOIN assets a ON c.id = a.category_id 
                    GROUP BY c.id 
                    ORDER BY c.name ASC";
            $stmt = getDB()->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}