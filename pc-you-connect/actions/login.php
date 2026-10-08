<?php
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../index.php');

 $role       = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'student';
 $identifier = post('identifier');
 $password   = $_POST['password'] ?? '';

if ($identifier === '' || $password === '') {
    flash_set('Please enter your ' . ($role === 'admin' ? 'User ID' : 'Student Number') . ' and password.', 'danger');
    redirect('../index.php');
}

 $st = db()->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
 $st->execute([$identifier]);
 $user = $st->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    flash_set('Invalid credentials. Please try again.', 'danger');
    redirect('../index.php');
}
if ($user['status'] !== 'active') {
    flash_set('This account is inactive. Please contact the administrator.', 'warning');
    redirect('../index.php');
}
if ($role === 'admin' && $user['role'] !== 'admin') {
    flash_set('This is not an administrator account. Use the Student Log in instead.', 'warning');
    redirect('../index.php');
}

login_session($user);

if (!empty($_POST['remember'])) {
    $token = bin2hex(random_bytes(32));
    db()->prepare('UPDATE users SET remember_token=? WHERE id=?')->execute([hash('sha256', $token), (int)$user['id']]);
    setcookie('pc_remember', $token, ['expires' => time() + 2592000, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
}

log_activity((int)$user['id'], 'login', ucfirst($user['role']) . ' logged in');
flash_set('Welcome back, ' . $user['first_name'] . '!');
redirect($user['role'] === 'admin' ? '../admin/dashboard.php' : '../user/dashboard.php');