<?php
declare(strict_types=1);

function validate_players_count($n): ?string {
    if (!is_numeric($n) || (int)$n != $n) return "Debe ser un número entero.";
    $n = (int)$n;
    if ($n < 1 || $n > 100) return "Debe estar entre 1 y 100.";
    return null;
}

function validate_date_past_or_present(string $date): ?string {
    $ts = strtotime($date);
    if ($ts === false) return "Fecha inválida.";
    $today = strtotime(date('Y-m-d'));
    if ($ts > time()) {
        if ($ts > $today) return "La fecha no puede ser futura.";
    }
    return null;
}

function sanitize_string(string $s): string { return trim($s); }

function normalize_name(string $name): string {
    $name = mb_strtolower($name, 'UTF-8');
    $name = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
    $name = preg_replace('/[^a-z0-9 ]/i', '', $name);
    return trim($name);
}
