<?php
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool   { return isset($_SESSION['user']); }
function is_admin(): bool       { return ($_SESSION['user']['role'] ?? '') === 'admin'; }

function login_session(array $u): void {
    $_SESSION['user'] = [
        'id'         => (int)$u['id'],
        'role'       => $u['role'],
        'user_id'    => $u['user_id'],
        'name'       => trim($u['first_name'] . ' ' . $u['last_name']),
        'first_name' => $u['first_name'],
        'email'      => $u['email'],
        'avatar'     => $u['avatar'],
    ];
    session_regenerate_id(true);
}

function require_login(): array {
    if (is_logged_in()) return $_SESSION['user'];

    // "Remember me" cookie login
    if (!empty($_COOKIE['pc_remember'])) {
        $st = db()->prepare("SELECT * FROM users WHERE remember_token=? AND status='active' LIMIT 1");
        $st->execute([hash('sha256', $_COOKIE['pc_remember'])]);
        if ($u = $st->fetch()) { login_session($u); return $_SESSION['user']; }
    }
    flash_set('Please log in to continue.', 'warning');
    redirect('../index.php');
}