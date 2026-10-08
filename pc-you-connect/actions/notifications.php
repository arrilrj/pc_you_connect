<?php
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../index.php');
 $user = require_login();
 $uid = (int)$user['id'];
 $back = $_POST['back'] ?? ($_SERVER['HTTP_REFERER'] ?? (($user['role'] === 'admin' ? '../admin/' : '../user/') . 'notifications.php'));

if (($_POST['action'] ?? '') === 'read') {
    db()->prepare('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?')->execute([(int)post('id'), $uid]);
} elseif (($_POST['action'] ?? '') === 'read_all') {
    db()->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([$uid]);
    flash_set('All notifications marked as read.');
}
redirect($back);