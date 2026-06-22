<?php
// models/User.php
require_once __DIR__ . '/../config/db.php';

class User {

    /**
     * Retrieves a single user by username (includes password for verification)
     * @return array|false
     */
    public static function getByUsername($username) {
        if (!is_string($username) || trim($username) === '') {
            return false;
        }

        try {
            $stmt = getDB()->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([trim($username)]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Verifies a plain-text password against the stored hash.
     * @return bool
     */
    public static function verifyPassword($password, $hash) {
        return is_string($password) && is_string($hash) && password_verify($password, $hash);
    }

    /**
     * Creates a new user and returns the new ID
     * @return int|false
     */
    public static function create($data) {
        if (
            empty($data['username']) ||
            empty($data['password']) ||
            empty($data['full_name'])
        ) {
            return false;
        }

        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = getDB()->prepare("INSERT INTO users (username, password, role, full_name, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                trim($data['username']),
                $hashedPassword,
                $data['role'] ?? 'staff',
                trim($data['full_name']),
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