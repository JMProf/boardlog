<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/db.php';

class PlayModel {
    public static function create(int $game_id, int $players_count, string $played_at, int $creator_id): int {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("INSERT INTO plays (game_id, players_count, played_at, created_by) VALUES (:g, :p, :d, :cb)");
        $stmt->execute([':g'=>$game_id, ':p'=>$players_count, ':d'=>$played_at, ':cb'=>$creator_id]);
        return (int)$pdo->lastInsertId();
    }
    public static function update(int $id, int $game_id, int $players_count, string $played_at, int $editor_id): void {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("UPDATE plays SET game_id = :g, players_count = :p, played_at = :d, updated_at = NOW(), updated_by = :eb WHERE id = :id");
        $stmt->execute([':g'=>$game_id, ':p'=>$players_count, ':d'=>$played_at, ':eb'=>$editor_id, ':id'=>$id]);
    }
    public static function deleteSoft(int $id): void { self::deleteHard($id); }
    public static function deleteHard(int $id): void {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("DELETE FROM plays WHERE id = :id");
        $stmt->execute([':id'=>$id]);
    }
    public static function find(int $id): ?array {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT * FROM plays WHERE id = :id");
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public static function search(?int $game_id = null, ?string $from = null, ?string $to = null): array {
        $pdo = get_pdo();
        $sql = "SELECT p.*, g.name AS game_name FROM plays p JOIN games g ON g.id = p.game_id WHERE 1=1";
        $params = [];
        if ($game_id) { $sql .= " AND p.game_id = :gid"; $params[':gid'] = $game_id; }
        if ($from) { $sql .= " AND p.played_at >= :from"; $params[':from'] = $from; }
        if ($to) { $sql .= " AND p.played_at <= :to"; $params[':to'] = $to; }
        $sql .= " ORDER BY p.played_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    public static function averages(?string $from = null, ?string $to = null): array {
        $pdo = get_pdo();
        $sql = "SELECT g.name, AVG(p.players_count) AS avg_players
                FROM plays p
                JOIN games g ON g.id = p.game_id
                WHERE 1=1";
        $params = [];
        if ($from) { $sql .= " AND p.played_at >= :from"; $params[':from'] = $from; }
        if ($to) { $sql .= " AND p.played_at <= :to"; $params[':to'] = $to; }
        $sql .= " GROUP BY g.id, g.name ORDER BY g.name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
