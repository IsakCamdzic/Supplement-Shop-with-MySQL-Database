<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplement Shop</title>
    <link rel="stylesheet" href="/supplementShop/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>💪 Supplement Shop</h1>
        <nav>
            <a href="/supplementShop/index.php">Početna</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="/supplementShop/admin/dashboard.php">Admin Panel</a>
                <?php elseif ($_SESSION['role'] == 'skladistar'): ?>
                    <a href="/supplementShop/skladistar/dashboard.php">Skladište</a>
                <?php elseif ($_SESSION['role'] == 'dostavljac'): ?>
                    <a href="/supplementShop/dostavljac/dashboard.php">Dostave</a>
                <?php elseif ($_SESSION['role'] == 'kupac'): ?>
                    <a href="/supplementShop/kupac/dashboard.php">Moja korpa</a>
                    <a href="/supplementShop/kupac/narudzbe.php">Moje narudžbe</a>
                <?php endif; ?>
                <a href="/supplementShop/logout.php">Odjava (<?php echo $_SESSION['user_name']; ?>)</a>
            <?php else: ?>
                <a href="/supplementShop/login.php">Prijava</a>
                <a href="/supplementShop/register.php">Registracija</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">