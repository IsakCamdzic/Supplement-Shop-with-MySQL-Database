<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/image_helper.php';
requireRole('kupac');

// Dohvati ili kreiraj korpu za trenutnog kupca
$stmt = $pdo->prepare("
    SELECT korpa_id FROM korpa 
    WHERE kupac_id = ? 
    ORDER BY datum DESC LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$korpa = $stmt->fetch();

if (!$korpa) {
    $stmt = $pdo->prepare("INSERT INTO korpa (kupac_id) VALUES (?)");
    $stmt->execute([$_SESSION['user_id']]);
    $korpa_id = $pdo->lastInsertId();
} else {
    $korpa_id = $korpa['korpa_id'];
}

// Obrada dodavanja u korpu iz index.php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $kolicina = $_POST['kolicina'];
    $cijena = $_POST['cijena'];

    $stmt = $pdo->prepare("SELECT kolicina FROM stavke_korpe WHERE korpa_id = ? AND proizvod_id = ?");
    $stmt->execute([$korpa_id, $product_id]);
    $postoji = $stmt->fetch();

    if ($postoji) {
        $stmt = $pdo->prepare("UPDATE stavke_korpe SET kolicina = kolicina + ? WHERE korpa_id = ? AND proizvod_id = ?");
        $stmt->execute([$kolicina, $korpa_id, $product_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO stavke_korpe (korpa_id, proizvod_id, kolicina, cijena) VALUES (?, ?, ?, ?)");
        $stmt->execute([$korpa_id, $product_id, $kolicina, $cijena]);
    }

    header('Location: dashboard.php');
    exit();
}

// Obrada ažuriranja količine
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $kolicina = $_POST['kolicina'];

    if ($kolicina <= 0) {
        $stmt = $pdo->prepare("DELETE FROM stavke_korpe WHERE korpa_id = ? AND proizvod_id = ?");
        $stmt->execute([$korpa_id, $product_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE stavke_korpe SET kolicina = ? WHERE korpa_id = ? AND proizvod_id = ?");
        $stmt->execute([$kolicina, $korpa_id, $product_id]);
    }

    header('Location: dashboard.php');
    exit();
}

// Obrada uklanjanja stavke
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    $stmt = $pdo->prepare("DELETE FROM stavke_korpe WHERE korpa_id = ? AND proizvod_id = ?");
    $stmt->execute([$korpa_id, $product_id]);
    header('Location: dashboard.php');
    exit();
}

// Obrada checkout-a
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    try {
        $pdo->beginTransaction();

        // Nađi default adresu kupca
        $stmt = $pdo->prepare("SELECT kupac_adresa_id FROM kupac_adresa WHERE kupac_id = ? AND je_default = TRUE LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $kupac_adresa_id = $stmt->fetchColumn();

        if (!$kupac_adresa_id) {
            throw new Exception("Nemate postavljenu adresu za dostavu. Molimo dodajte adresu u profilu prije naručivanja.");
        }

        // Dohvati stavke korpe sa trenutnim stanjem zaliha
        $stmt = $pdo->prepare("
            SELECT sk.proizvod_id, sk.kolicina, sk.cijena,
                COALESCE((SELECT SUM(zt.kolicina_promjena) FROM zaliha_transakcija zt WHERE zt.proizvod_id = sk.proizvod_id), 0) as kolicina_na_stanju
            FROM stavke_korpe sk
            JOIN proizvod p ON sk.proizvod_id = p.proizvod_id
            WHERE sk.korpa_id = ?
        ");
        $stmt->execute([$korpa_id]);
        $cart_items = $stmt->fetchAll();

        if (empty($cart_items)) {
            throw new Exception("Korpa je prazna!");
        }

        foreach ($cart_items as $item) {
            if ($item['kolicina_na_stanju'] < $item['kolicina']) {
                throw new Exception("Nedovoljno zaliha za proizvod ID: " . $item['proizvod_id']);
            }
        }

        // Status "Na cekanju"
        $stmt = $pdo->prepare("SELECT status_id FROM status_narudzbe WHERE naziv = 'Na cekanju'");
        $stmt->execute();
        $status_id = $stmt->fetchColumn();

        // Kreiraj narudžbu (zaposlenik_id ostaje NULL dok se ne dodijeli)
        $stmt = $pdo->prepare("
            INSERT INTO narudzba (kupac_adresa_id, status_id, zaposlenik_id)
            VALUES (?, ?, NULL)
        ");
        $stmt->execute([$kupac_adresa_id, $status_id]);
        $narudzba_id = $pdo->lastInsertId();

        // Prebaci stavke u narudžbu i upiši izlaz sa zaliha
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO stavke_narudzbe (narudzba_id, proizvod_id, kolicina, cijena)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$narudzba_id, $item['proizvod_id'], $item['kolicina'], $item['cijena']]);

            $stmt = $pdo->prepare("
                INSERT INTO zaliha_transakcija (proizvod_id, tip, kolicina_promjena, narudzba_id)
                VALUES (?, 'Kupovina', ?, ?)
            ");
            $stmt->execute([$item['proizvod_id'], -$item['kolicina'], $narudzba_id]);
        }

        // Isprazni korpu
        $stmt = $pdo->prepare("DELETE FROM stavke_korpe WHERE korpa_id = ?");
        $stmt->execute([$korpa_id]);

        $pdo->commit();

        header('Location: narudzbe.php?success=1');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Dohvati stavke korpe za prikaz
$stmt = $pdo->prepare("
    SELECT 
        sk.proizvod_id as id_proizvoda,
        p.naziv,
        sk.kolicina,
        sk.cijena,
        (sk.kolicina * sk.cijena) as ukupno,
        pr.naziv_proizvodjaca as proizvodjac,
        k.naziv as naziv_kategorije
    FROM stavke_korpe sk
    JOIN proizvod p ON sk.proizvod_id = p.proizvod_id
    JOIN kategorija k ON p.kategorija_id = k.kategorija_id
    JOIN proizvodjac pr ON p.proizvodjac_id = pr.proizvodjac_id
    WHERE sk.korpa_id = ?
");
$stmt->execute([$korpa_id]);
$cart_items = $stmt->fetchAll();

$total = array_sum(array_column($cart_items, 'ukupno'));
$free_shipping_threshold = 100;
$free_shipping_needed = max(0, $free_shipping_threshold - $total);
?>
<!DOCTYPE html>
<html class="dark" lang="bs">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Moja Korpa - SUPP.SCIENCE</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&amp;family=Inter:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "inverse-primary": "#506600",
                        "surface-tint": "#abd600",
                        "on-error-container": "#ffdad6",
                        "primary-fixed-dim": "#abd600",
                        "on-secondary": "#313030",
                        "outline": "#8e9379",
                        "primary": "#ffffff",
                        "on-primary-container": "#556d00",
                        "surface-variant": "#333627",
                        "on-error": "#690005",
                        "tertiary-fixed": "#e0e2eb",
                        "on-background": "#e2e4cf",
                        "on-secondary-fixed": "#1c1b1b",
                        "on-tertiary": "#2d3037",
                        "surface-container-low": "#1a1d10",
                        "on-tertiary-container": "#62646c",
                        "tertiary": "#ffffff",
                        "surface-container": "#1e2113",
                        "outline-variant": "#444933",
                        "secondary": "#c8c6c5",
                        "on-tertiary-fixed-variant": "#44474e",
                        "surface-container-highest": "#333627",
                        "surface-dim": "#111508",
                        "secondary-fixed-dim": "#c8c6c5",
                        "background": "#111508",
                        "tertiary-fixed-dim": "#c4c6cf",
                        "on-secondary-container": "#bab8b7",
                        "error": "#ffb4ab",
                        "secondary-fixed": "#e5e2e1",
                        "primary-container": "#c3f400",
                        "inverse-surface": "#e2e4cf",
                        "on-primary": "#283500",
                        "on-secondary-fixed-variant": "#474646",
                        "surface": "#111508",
                        "primary-fixed": "#c3f400",
                        "on-surface-variant": "#c4c9ac",
                        "on-primary-fixed": "#161e00",
                        "inverse-on-surface": "#2f3223",
                        "tertiary-container": "#e0e2eb",
                        "on-surface": "#e2e4cf",
                        "secondary-container": "#4a4949",
                        "on-primary-fixed-variant": "#3c4d00",
                        "surface-bright": "#373b2c",
                        "error-container": "#93000a",
                        "surface-container-high": "#282b1d",
                        "surface-container-lowest": "#0c0f04",
                        "on-tertiary-fixed": "#191c22"
                    },
                    borderRadius: {
                        sm: "0.125rem",
                        DEFAULT: "0.25rem",
                        md: "0.375rem",
                        lg: "0.5rem",
                        xl: "0.75rem",
                        full: "9999px"
                    },
                    spacing: {
                        base: "8px",
                        xs: "4px",
                        sm: "12px",
                        md: "24px",
                        lg: "48px",
                        xl: "80px",
                        container_max: "1280px",
                        gutter: "24px"
                    },
                    fontFamily: {
                        "body-lg": ["Inter"],
                        "label-bold": ["Inter"],
                        "headline-xl-mobile": ["Montserrat"],
                        "display-lg": ["Montserrat"],
                        "headline-md": ["Montserrat"],
                        "body-md": ["Inter"],
                        "headline-xl": ["Montserrat"],
                        "display-lg-mobile": ["Montserrat"]
                    },
                    fontSize: {
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "label-bold": ["14px", {"lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "headline-xl-mobile": ["32px", {"lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "800"}],
                        "display-lg": ["64px", {"lineHeight": "1.1", "letterSpacing": "-0.04em", "fontWeight": "900"}],
                        "headline-md": ["24px", {"lineHeight": "1.3", "fontWeight": "700"}],
                        "body-md": ["16px", {"lineHeight": "1.5", "fontWeight": "400"}],
                        "headline-xl": ["40px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "800"}],
                        "display-lg-mobile": ["40px", {"lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "900"}]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .cart-item-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .cart-item-hover:hover {
            background: #282b1d;
            border-color: #c3f400;
        }
        .progress-bar {
            transition: width 0.5s ease-out;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            opacity: 0.5;
        }
        input[type="number"]:focus::-webkit-inner-spin-button,
        input[type="number"]:focus::-webkit-outer-spin-button {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-background text-on-background selection:bg-primary-fixed selection:text-on-primary-fixed">

<!-- TopNavBar -->
<nav class="fixed top-0 w-full z-50 bg-background/95 backdrop-blur-md border-b border-outline-variant shadow-lg">
    <div class="flex justify-between items-center h-20 px-gutter max-w-container_max mx-auto" style="padding-left: 24px; padding-right: 24px;">
        <a href="../index.php" class="font-display-lg-mobile text-display-lg-mobile font-black text-primary-fixed tracking-tighter hover:brightness-110 transition-all">
            SUPP.SCIENCE
        </a>
        <div class="hidden md:flex items-center gap-md">
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="../index.php">Početna</a>
            <a class="text-primary-fixed font-label-bold border-b-2 border-primary-fixed pb-1" href="dashboard.php">Moja korpa</a>
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="narudzbe.php">Moje narudžbe</a>
        </div>
        <div class="flex items-center gap-sm">
            <div class="flex items-center gap-2">
                <span class="text-primary-fixed text-sm hidden md:inline"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="../logout.php" class="flex items-center gap-xs text-primary-fixed font-label-bold active:scale-95 transition-transform px-sm py-xs rounded hover:bg-surface-container-highest">
                    <span class="material-symbols-outlined text-base">logout</span>
                    <span class="hidden md:inline">Odjava</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="pt-32 pb-xl px-gutter max-w-container_max mx-auto" style="padding-top: 128px; padding-bottom: 80px; padding-left: 24px; padding-right: 24px;">
    
    <!-- Page Header -->
    <div class="mb-lg">
        <h1 class="font-headline-xl text-headline-xl text-primary mb-xs">Moja Korpa</h1>
        <div class="h-1.5 w-24 bg-primary-fixed rounded-full"></div>
        <p class="text-on-surface-variant text-body-lg mt-sm">Pregledajte i uredite stavke vaše narudžbe</p>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-error-container border border-error rounded-xl p-md mb-lg flex items-center gap-sm" style="padding: 24px;">
        <span class="material-symbols-outlined text-error">error</span>
        <p class="text-on-error-container"><?php echo htmlspecialchars($error); ?></p>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-lg">
        
        <!-- Cart Items Section -->
        <div class="lg:col-span-8">
            <?php if (empty($cart_items)): ?>
            <div class="bg-surface-container-high border border-outline-variant rounded-xl p-xl text-center" style="padding: 80px;">
                <span class="material-symbols-outlined text-7xl text-on-surface-variant mb-md">shopping_cart_off</span>
                <h3 class="font-headline-md text-primary mb-xs">Vaša korpa je prazna</h3>
                <p class="text-on-surface-variant mb-lg">Izgleda da još niste dodali proizvode u korpu.</p>
                <a href="../index.php" class="inline-flex items-center gap-sm bg-primary-fixed text-on-primary-fixed font-label-bold px-lg py-md rounded-lg hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    ZAPOČNITE KUPOVINU
                </a>
            </div>
            <?php else: ?>
            <div class="space-y-md">
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item-hover bg-surface-container-high border border-outline-variant rounded-xl p-md transition-all duration-300" style="padding: 24px;">
                    <div class="flex flex-col md:flex-row gap-md items-start md:items-center">
                        <!-- Product Icon -->
                        <div class="bg-surface-dim rounded-lg p-sm flex-shrink-0">
                        <?php 
                        $img_path = getProductImage($item['id_proizvoda'], $item['naziv']);
                        ?>
                        <img src="<?php echo $img_path; ?>" 
                            alt="<?php echo htmlspecialchars($item['naziv']); ?>" 
                            class="w-12 h-12 object-contain">
                        </div>
                                            
                        <!-- Product Info -->
                        <div class="flex-grow">
                            <p class="text-on-surface-variant font-label-bold text-xs uppercase tracking-wider mb-xs"><?php echo htmlspecialchars($item['proizvodjac']); ?></p>
                            <h3 class="font-headline-md text-headline-md text-primary mb-xs"><?php echo htmlspecialchars($item['naziv']); ?></h3>
                            <p class="text-on-surface-variant text-body-md"><?php echo htmlspecialchars($item['naziv_kategorije']); ?></p>
                        </div>
                        
                        <!-- Quantity and Price -->
                        <div class="flex items-center gap-md">
                            <form method="POST" class="flex items-center gap-xs">
                                <input type="hidden" name="product_id" value="<?php echo $item['id_proizvoda']; ?>">
                                <input type="number" name="kolicina" value="<?php echo $item['kolicina']; ?>" min="1" class="w-20 bg-background border border-outline-variant rounded-lg px-sm py-xs text-center font-label-bold focus:border-primary-fixed focus:ring-0 focus:outline-none">
                                <button type="submit" name="update_quantity" class="text-primary-fixed hover:brightness-110 transition-all p-xs">
                                    <span class="material-symbols-outlined">refresh</span>
                                </button>
                            </form>
                            <div class="text-right">
                                <p class="font-headline-md text-primary-fixed"><?php echo number_format($item['ukupno'], 2); ?> KM</p>
                                <p class="text-on-surface-variant text-body-md text-sm"><?php echo number_format($item['cijena'], 2); ?> KM/kom</p>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $item['id_proizvoda']; ?>">
                                <button type="submit" name="remove_item" class="text-on-surface-variant hover:text-error transition-colors p-xs">
                                    <span class="material-symbols-outlined">delete_outline</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Order Summary Section -->
        <div class="lg:col-span-4">
            <div class="bg-surface-container-high border border-outline-variant rounded-xl p-md sticky top-28" style="padding: 24px;">
                <h3 class="font-headline-md text-primary mb-md">Pregled narudžbe</h3>
                
                <!-- Free Shipping Progress -->
                <div class="mb-md">
                    <div class="flex justify-between text-body-md mb-xs">
                        <span class="text-on-surface-variant">Besplatna dostava</span>
                        <span class="<?php echo $total >= $free_shipping_threshold ? 'text-primary-fixed' : 'text-on-surface-variant'; ?>">
                            <?php if ($total >= $free_shipping_threshold): ?>
                                Ostvareno! 🎉
                            <?php else: ?>
                                Još <?php echo number_format($free_shipping_needed, 2); ?> KM
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="h-2 bg-surface-dim rounded-full overflow-hidden">
                        <div class="progress-bar h-full bg-primary-fixed rounded-full" style="width: <?php echo min(100, ($total / $free_shipping_threshold) * 100); ?>%"></div>
                    </div>
                </div>
                
                <!-- Totals -->
                <div class="space-y-sm mb-lg">
                    <div class="flex justify-between text-body-md">
                        <span class="text-on-surface-variant">Ukupno</span>
                        <span class="text-on-surface"><?php echo number_format($total, 2); ?> KM</span>
                    </div>
                    <div class="flex justify-between text-body-md">
                        <span class="text-on-surface-variant">Dostava</span>
                        <span class="text-on-surface"><?php echo $total >= $free_shipping_threshold ? '0.00 KM' : '9.00 KM'; ?></span>
                    </div>
                    <div class="border-t border-outline-variant my-md"></div>
                    <div class="flex justify-between font-headline-md">
                        <span class="text-primary">Ukupno za platiti</span>
                        <span class="text-primary-fixed"><?php echo number_format($total + ($total >= $free_shipping_threshold ? 0 : 9), 2); ?> KM</span>
                    </div>
                </div>
                
                <!-- Checkout Form -->
                <?php if (!empty($cart_items)): ?>
                <form method="POST">
                    <div class="mb-md">
                        <p class="text-on-surface-variant text-body-md text-sm">Dostava na adresu iz vašeg profila.</p>
                    </div>
                    <button type="submit" name="checkout" class="w-full bg-primary-fixed text-on-primary-fixed font-label-bold py-md px-lg rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-sm">
                        <span class="material-symbols-outlined">shopping_bag_checkout</span>
                        POTVRDI NARUDŽBU
                    </button>
                </form>
                <?php endif; ?>
                
                <!-- Continue Shopping -->
                <div class="mt-md text-center">
                    <a href="../index.php" class="text-on-surface-variant hover:text-primary-fixed transition-colors text-body-md inline-flex items-center gap-xs">
                        <span class="material-symbols-outlined">arrow_back</span>
                        Nastavi kupovinu
                    </a>
                </div>
            </div>
        </div>
        
    </div>
</main>

<!-- Footer -->
<footer class="w-full py-lg mt-xl bg-surface-container-lowest border-t border-outline-variant" style="padding-top: 48px; padding-bottom: 48px; margin-top: 80px;">
    <div class="flex flex-col md:flex-row justify-between items-center px-gutter max-w-container_max mx-auto gap-md" style="padding-left: 24px; padding-right: 24px;">
        <div class="text-center md:text-left">
            <div class="font-headline-md text-headline-md font-bold text-on-surface mb-xs">SUPP.SCIENCE</div>
            <p class="text-on-surface-variant font-body-md">Vaš partner u postizanju vrhunskih performansi.</p>
        </div>
        <div class="flex flex-wrap justify-center gap-md">
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-all" href="#">O nama</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-all" href="#">Dostava</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-all" href="#">Uslovi korištenja</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-all" href="mailto:info@supp.science">Kontakt</a>
        </div>
        <div class="text-on-surface-variant font-body-md">
            © 2024 SUPP.SCIENCE. Sva prava pridržana.
        </div>
    </div>
</footer>

</body>
</html>