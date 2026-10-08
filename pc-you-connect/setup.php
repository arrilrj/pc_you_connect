<?php
// Run ONCE: http://localhost/pc-you-connect/setup.php — then DELETE this file.
require_once __DIR__ . '/config/database.php';
 $pdo = Database::conn();

 $hash = password_hash('123', PASSWORD_DEFAULT);

// Admin account  → log in via "Admin Log in"  with admin / 123
 $pdo->prepare('INSERT INTO users (role,user_id,first_name,last_name,email,password)
               VALUES ("admin","admin","Admin","User","admin@pcu.edu.ph",?)
               ON DUPLICATE KEY UPDATE password=VALUES(password), role="admin", status="active"')
    ->execute([$hash]);

// Regular user   → log in via "Student Log in" with user / 123
 $pdo->prepare('INSERT INTO users (role,user_id,first_name,last_name,email,password)
               VALUES ("student","user","Campus","User","user@student.pcu.edu.ph",?)
               ON DUPLICATE KEY UPDATE password=VALUES(password), role="student", status="active"')
    ->execute([$hash]);

// Deactivate the old demo accounts so they can't be used
 $pdo->exec("UPDATE users SET status='inactive' WHERE user_id NOT IN ('admin','user')");

// Demo approved booking for the "user" account (fills My Active Bookings)
 $uid = (int)$pdo->query("SELECT id FROM users WHERE user_id='user'")->fetchColumn();
 $fid = (int)$pdo->query("SELECT id FROM facilities WHERE name LIKE '7th Floor%'")->fetchColumn();
if ($uid && $fid && !(int)$pdo->query("SELECT COUNT(*) FROM facility_reservations WHERE user_id=$uid")->fetchColumn()) {
    $date = date('Y-m-d', strtotime('+14 days'));
    $pdo->prepare('INSERT INTO facility_reservations (user_id,facility_id,purpose,res_date,start_time,end_time,status,decided_by,decided_at)
                   VALUES (?,?,?,?,?,?,?,?,NOW())')
       ->execute([$uid, $fid, 'Student Council General Assembly', $date, '09:00:00', '12:00:00', 'approved', 1]);
    $rid = (int)$pdo->lastInsertId();
    $h = $pdo->prepare('INSERT INTO reservation_status_history (reservation_id,status,remarks,changed_by) VALUES (?,?,?,?)');
    $h->execute([$rid, 'pending',  'Reservation submitted', $uid]);
    $h->execute([$rid, 'approved', 'Approved by admin', 1]);
    $pdo->prepare('INSERT INTO notifications (user_id,type,title,message) VALUES (?,?,?,?)')
       ->execute([$uid, 'reservation', 'Reservation Approved', 'Your reservation for the 7th Floor Conference Room has been approved.']);
}

header('Content-Type: text/plain; charset=utf-8');
echo "Setup complete.\n\nLogins (password: 123)\n  Admin  -> User ID: admin\n  User   -> Student Number: user\n\nDelete this file now.";