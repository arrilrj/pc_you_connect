<?php
 $base = $base ?? '..';
 $page_title = $page_title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> · PC-YOU! Connect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= $base ?>/assets/css/style.css" rel="stylesheet">
<script>window.BASE_URL = '<?= $base ?>';</script>
</head>
<body>

<?php foreach (flash_get() as $f): ?>
<div class="pc-flash d-none" data-type="<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>

<div class="main-area">
<?php include __DIR__ . '/navbar.php'; ?>
<main class="page-content">