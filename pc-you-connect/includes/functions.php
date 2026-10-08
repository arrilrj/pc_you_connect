<?php
require_once __DIR__ . '/../config/database.php';

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }
function post(string $k, $d = '') { return trim($_POST[$k] ?? $d); }
function get_str(string $k, $d = '') { return trim($_GET[$k] ?? $d); }

function flash_set(string $msg, string $type = 'success'): void { $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type]; }
function flash_get(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

function db_scalar(string $sql, array $p = []) { $st = db()->prepare($sql); $st->execute($p); return $st->fetchColumn(); }

function time_ago(?string $dt): string {
    if (!$dt) return '—';
    $d = time() - strtotime($dt);
    if ($d < 60)     return 'just now';
    if ($d < 3600)   return floor($d / 60) . ' mins ago';
    if ($d < 86400)  { $h = floor($d / 3600); return $h . ($h == 1 ? ' hr' : ' hrs') . ' ago'; }
    if ($d < 604800) { $x = floor($d / 86400); return $x . ($x == 1 ? ' day' : ' days') . ' ago'; }
    return date('M j, Y', strtotime($dt));
}
function fmt_date($d): string   { return $d ? date('M j, Y', strtotime($d)) : '—'; }
function fmt_time12($t): string { return $t ? date('g:i A', strtotime($t)) : '—'; }
function initials(string $f, string $l): string { return strtoupper(mb_substr($f, 0, 1) . mb_substr($l, 0, 1)); }

function badge(string $s): string {
    static $map = [
        'available'=>['soft-success','AVAILABLE'],'maintenance'=>['soft-warning','MAINTENANCE'],'unavailable'=>['soft-danger','UNAVAILABLE'],
        'pending'=>['soft-warning','PENDING'],'approved'=>['soft-success','APPROVED'],'rejected'=>['soft-danger','REJECTED'],
        'cancelled'=>['soft-secondary','CANCELLED'],'completed'=>['soft-info','COMPLETED'],'upcoming'=>['soft-primary','UPCOMING'],
        'unclaimed'=>['soft-warning','UNCLAIMED'],'claimed'=>['soft-success','CLAIMED'],'turned_in'=>['soft-purple','TURNED IN'],
        'searching'=>['soft-warning','SEARCHING'],'reunited'=>['soft-primary','REUNITED'],
        'found'=>['soft-info','FOUND'],'lost'=>['soft-danger','LOST'],
        'facilities'=>['soft-info','FACILITIES'],'lost_found'=>['soft-warning','LOST & FOUND'],
        'electronics'=>['soft-secondary','Electronics'],'id_card'=>['soft-secondary','ID / Cards'],
        'accessories'=>['soft-secondary','Accessories'],'bags'=>['soft-secondary','Bags'],'keys'=>['soft-secondary','Keys'],
        'clothing'=>['soft-secondary','Clothing'],'documents'=>['soft-secondary','Documents'],'books'=>['soft-secondary','Books'],
        'other'=>['soft-secondary','Other'],
        'published'=>['soft-success','Published'],'archived'=>['soft-secondary','Archived'],
        'active'=>['soft-success','Active'],'inactive'=>['soft-secondary','Inactive'],
    ];
    [$c, $l] = $map[$s] ?? ['soft-secondary', ucwords(str_replace('_', ' ', $s))];
    return '<span class="badge badge-soft ' . $c . '">' . e($l) . '</span>';
}

function lf_icon(string $cat, string $name = ''): string {
    $n = mb_strtolower($name);
    if (str_contains($n, 'watch'))   return 'bi-smartwatch';
    if (str_contains($n, 'glass'))   return 'bi-eyeglasses';
    if (str_contains($n, 'key'))     return 'bi-key';
    if (str_contains($n, 'atm') || str_contains($n, 'credit')) return 'bi-credit-card';
    if (str_contains($n, 'id card') || str_contains($n, 'student id')) return 'bi-person-badge';
    if (str_contains($n, 'phone') || str_contains($n, 'samsung') || str_contains($n, 'iphone')) return 'bi-phone';
    if (str_contains($n, 'backpack') || str_contains($n, 'bag')) return 'bi-backpack';
    if (str_contains($n, 'charger') || str_contains($n, 'laptop')) return 'bi-laptop';
    return ['electronics'=>'bi-phone','id_card'=>'bi-person-badge','accessories'=>'bi-smartwatch',
            'bags'=>'bi-backpack','keys'=>'bi-key','clothing'=>'bi-handbag',
            'documents'=>'bi-file-earmark-text','books'=>'bi-journal-book'][$cat] ?? 'bi-box-seam';
}
function lf_tint(string $c): string {
    return ['electronics'=>'icon-info','id_card'=>'icon-info','accessories'=>'icon-info','bags'=>'icon-danger',
            'keys'=>'icon-warning','clothing'=>'icon-navy','documents'=>'icon-warning','books'=>'icon-success'][$c] ?? 'icon-navy';
}
function ann_icon(string $c): string {
    return ['facilities'=>'bi-building','lost_found'=>'bi-search'][$c] ?? 'bi-megaphone';
}
function ann_tint(string $c): string {
    return ['facilities'=>'icon-info','lost_found'=>'icon-warning'][$c] ?? 'icon-navy';
}
function facility_icon(array $f): string {
    $n = strtolower($f['name'] . ' ' . ($f['location'] ?? ''));
    if (str_contains($n, 'comlab') || str_contains($n, 'computer') || str_contains($n, 'lab')) return 'bi-pc-display';
    if (str_contains($n, 'gym'))      return 'bi-dribbble';
    if (str_contains($n, 'court'))    return 'bi-basket';
    if (str_contains($n, 'avr') || str_contains($n, 'audio') || str_contains($n, 'conference') || str_contains($n, 'hall')) return 'bi-people';
    if (str_contains($n, 'library') || str_contains($n, 'discussion')) return 'bi-journals';
    return 'bi-building';
}

function log_activity(?int $uid, string $action, string $desc = ''): void {
    try {
        db()->prepare('INSERT INTO activity_logs (user_id,action,description,ip_address) VALUES (?,?,?,?)')
           ->execute([$uid, $action, mb_substr($desc, 0, 255), $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch (Throwable $t) {
    }
}

function user_pref(int $uid, string $key, bool $def = true): bool {
    $s = db_scalar('SELECT settings FROM users WHERE id=?', [$uid]);
    if (!$s) return $def;
    $a = json_decode($s, true) ?: [];
    return isset($a[$key]) ? (bool)$a[$key] : $def;
}
function save_pref(int $uid, array $prefs): void {
    db()->prepare('UPDATE users SET settings=? WHERE id=?')->execute([json_encode($prefs), $uid]);
}

function notify(int $uid, string $type, string $title, string $msg, ?string $link = null): void {
    if (!user_pref($uid, 'notify_' . $type, true)) return;
    db()->prepare('INSERT INTO notifications (user_id,type,title,message,link) VALUES (?,?,?,?,?)')
       ->execute([$uid, $type, $title, $msg, $link]);
}
function notify_admins(string $type, string $title, string $msg, ?string $link = null): void {
    foreach (db()->query("SELECT id FROM users WHERE role='admin' AND status='active'") as $a) {
        notify((int)$a['id'], $type, $title, $msg, $link);
    }
}
function unread_count(int $uid): int {
    return (int)db_scalar('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0', [$uid]);
}

function week_delta(string $table, string $col = 'created_at'): string {
    $c1 = (int)db_scalar("SELECT COUNT(*) FROM $table WHERE $col >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $c0 = (int)db_scalar("SELECT COUNT(*) FROM $table WHERE $col < DATE_SUB(NOW(), INTERVAL 7 DAY) AND $col >= DATE_SUB(NOW(), INTERVAL 14 DAY)");
    if ($c0 === 0) return $c1 > 0 ? '+100%' : '0%';
    $pct = (int)round((($c1 - $c0) / $c0) * 100);
    return ($pct >= 0 ? '+' : '') . $pct . '%';
}

function reservation_conflict(int $facilityId, string $date, string $start, string $end, int $exclude = 0): bool {
    return (bool)db_scalar(
        "SELECT COUNT(*) FROM facility_reservations
         WHERE facility_id=? AND res_date=? AND status IN ('pending','approved')
           AND NOT (end_time <= ? OR start_time >= ?) AND id<>?",
        [$facilityId, $date, $start, $end, $exclude]);
}

function upload_image(?array $file, ?string $current = null): ?string {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return $current;
    if ($file['size'] > 2 * 1024 * 1024) return $current;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) return $current;
    $dir = __DIR__ . '/../assets/uploads';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $name = 'u' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], "$dir/$name") ? $name : $current;
}

function upload_files(?array $files, int $maxFiles = 3): array {
    $saved = [];
    if (!$files || empty($files['name'][0])) return $saved;
    $allowed = ['jpg','jpeg','png','webp','gif','pdf','doc','docx'];
    $dir = __DIR__ . '/../assets/uploads';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $count = min(count($files['name']), $maxFiles);
    for ($i = 0; $i < $count; $i++) {
        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
        if (($files['size'][$i] ?? 0) > 2 * 1024 * 1024) continue;
        $ext = strtolower(pathinfo((string)$files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) continue;
        $name = 'r' . time() . '_' . $i . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        if (move_uploaded_file($files['tmp_name'][$i], "$dir/$name")) $saved[] = $name;
    }
    return $saved;
}

function res_attachments_html(?string $json): string {
    if (!$json) return '';
    $files = json_decode($json, true);
    if (!is_array($files) || !$files) return '';
    $imgExt = ['jpg','jpeg','png','webp','gif'];
    $html = '<div class="attach-row">';
    foreach ($files as $f) {
        $ext = strtolower(pathinfo((string)$f, PATHINFO_EXTENSION));
        if (in_array($ext, $imgExt, true)) {
            $html .= '<a href="../assets/uploads/' . e($f) . '" target="_blank"><img class="attach-thumb" src="../assets/uploads/' . e($f) . '" alt="attachment"></a>';
        } else {
            $icon = $ext === 'pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-word';
            $html .= '<a class="attach-file" href="../assets/uploads/' . e($f) . '" target="_blank"><i class="bi ' . $icon . '"></i> ' . e($f) . '</a>';
        }
    }
    return $html . '</div>';
}