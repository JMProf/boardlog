<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/db.php';

class GameModel {
    public static function create(string $name, int $creator_id): int {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("INSERT INTO games (name, name_norm, created_by) VALUES (:n, :nn, :cb)");
        $stmt->execute([':n'=>$name, ':nn'=>self::normalize($name), ':cb'=>$creator_id]);
        return (int)$pdo->lastInsertId();
    }
    public static function update(int $id, string $name, int $editor_id): void {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("UPDATE games SET name = :n, name_norm = :nn, updated_at = NOW(), updated_by = :eb WHERE id = :id");
        $stmt->execute([':n'=>$name, ':nn'=>self::normalize($name), ':eb'=>$editor_id, ':id'=>$id]);
    }
    public static function deleteSoft(int $id): void { self::deleteHard($id); }
    public static function deleteHard(int $id): void {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("DELETE FROM plays WHERE game_id = :id");
        $stmt->execute([':id'=>$id]);
        $stmt = $pdo->prepare("DELETE FROM games WHERE id = :id");
        $stmt->execute([':id'=>$id]);
    }
    public static function find(int $id): ?array {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT * FROM games WHERE id = :id");
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public static function search(string $q = ''): array {
        $pdo = get_pdo();
        if ($q !== '') {
            $stmt = $pdo->prepare("SELECT * FROM games WHERE (name LIKE :q OR name_norm LIKE :qn) ORDER BY name ASC");
            $nq = self::normalize($q);
            $stmt->execute([':q'=>'%'.$q.'%', ':qn'=>'%'.$nq.'%']);
        }
        return $pdo->query("SELECT * FROM games  ORDER BY name ASC")->fetchAll();
    }
    public static function normalize(string $name): string {
        $name = mb_strtolower($name, 'UTF-8');
        $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $name = preg_replace('/[^a-z0-9 ]/i', '', $name);
        return trim($name);
    }
}
