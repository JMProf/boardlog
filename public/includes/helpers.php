<?php
function fmt_date(string $date): string {
    $ts = strtotime($date);
    if ($ts === false) return htmlspecialchars($date);
    return date('d/m/Y', $ts);
}
function fmt_datetime(string $dt): string {
    $ts = strtotime($dt);
    if ($ts === false) return htmlspecialchars($dt);
    return date('d/m/Y H:i', $ts);
}
