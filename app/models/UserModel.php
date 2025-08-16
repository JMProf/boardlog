<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/db.php';

class UserModel {
    public static function create(string $username, string $password, string $role = 'socio'): int {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:u, :p, :r)");
        $stmt->execute([':u'=>$username, ':p'=>password_hash($password, PASSWORD_DEFAULT), ':r'=>$role]);
        return (int)$pdo->lastInsertId();
    }
    public static function findById(int $id): ?array {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = :id");
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public static function all(): array {
        $pdo = get_pdo();
        return $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    }
    public static function resetPassword(int $id, string $password): void {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :p WHERE id = :id");
        $stmt->execute([':p'=>password_hash($password, PASSWORD_DEFAULT), ':id'=>$id]);
    }
}
