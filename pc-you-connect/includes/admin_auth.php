<?php
require_once __DIR__ . '/auth.php';

function require_admin(): array {
    $u = require_login();
    if (($u['role'] ?? '') !== 'admin') {
        flash_set('Administrator access required.', 'danger');
        redirect('../index.php');
    }
    return $u;
}