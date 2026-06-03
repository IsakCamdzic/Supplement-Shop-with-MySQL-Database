<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('kupac');

// Dohvati ID korpe za trenutnog kupca
$stmt = $pdo->prepare("
    SELECT id_korpe FROM korpa 
    WHERE id_kupca = ? 
    ORDER BY datum DESC LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$korpa = $stmt->fetch();

if (!$korpa) {
    $stmt = $pdo->prepare("INSERT INTO korpa (id_kupca) VALUES (?)");
    $stmt->execute([$_SESSION['user_id']]);
    $korpa_id = $pdo->lastInsertId();
} else {
    $korpa_id = $korpa['id_korpe'];
}

// Dodavanje proizvoda u korpu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $kolicina = $_POST['kolicina'];
    $cijena = $_POST['cijena'];
    
    // Provjeri da li proizvod već postoji u korpi
    $stmt = $pdo->prepare("
        SELECT id_proizvoda, kolicina 
        FROM stavke_korpe 
        WHERE id_korpe = ? AND id_proizvoda = ?
    ");
    $stmt->execute([$korpa_id, $product_id]);
    $postoji = $stmt->fetch();
    
    if ($postoji) {
        // Ažuriraj količinu
        $nova_kolicina = $postoji['kolicina'] + $kolicina;
        $stmt = $pdo->prepare("
            UPDATE stavke_korpe 
            SET kolicina = ? 
            WHERE id_korpe = ? AND id_proizvoda = ?
        ");
        $stmt->execute([$nova_kolicina, $korpa_id, $product_id]);
    } else {
        // Dodaj novi proizvod
        $stmt = $pdo->prepare("
            INSERT INTO stavke_korpe (id_korpe, id_proizvoda, kolicina, cijena)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$korpa_id, $product_id, $kolicina, $cijena]);
    }
    
    // Redirekcija nazad na dashboard
    header('Location: dashboard.php');
    exit();
}

// Ako neko direktno pristupi ovom fajlu bez POST requesta
header('Location: dashboard.php');
exit();
?>