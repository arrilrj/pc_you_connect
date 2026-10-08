<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) {
    db()->prepare('UPDATE users SET remember_token=NULL WHERE id=?')->execute([(int)$_SESSION['user']['id']]);
    log_activity((int)$_SESSION['user']['id'], 'logout', $_SESSION['user']['name'] . ' signed out');
}
 $_SESSION = [];
if (ini_get('session.use_cookies')) { setcookie(session_name(), '', time() - 42000, '/'); }
setcookie('pc_remember', '', time() - 3600, '/');
session_destroy();
redirect('index.php');