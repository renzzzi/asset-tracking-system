<?php
// models/User.php
require_once __DIR__ . '/../config/db.php';

class User {

    /**
     * Retrieves a single user by username (includes password for verification)
     * @return array|false
     */
    public static function getByUsername($username) {
        try {
            $stmt = getDB()->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Creates a new user and returns the new ID
     * @return int|false
     */
    public static function create($data) {
        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = getDB()->prepare("INSERT INTO users (username, password, role, full_name, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['username'],
                $hashedPassword,
                $data['role'] ?? 'staff',
                $data['full_name'],
                $data['email'] ?? null
            ]);
            return getDB()->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Retrieves all users (excludes password column)
     * @return array
     */
    public static function getAll() {
        try {
            $stmt = getDB()->query("SELECT id, username, role, full_name, email, created_at, updated_at FROM users ORDER BY full_name ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Retrieves a single user by ID (excludes password column)
     * @return array|false
     */
    public static function getById($id) {
        try {
            $stmt = getDB()->prepare("SELECT id, username, role, full_name, email, created_at, updated_at FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
}