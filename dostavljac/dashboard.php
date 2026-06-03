<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('dostavljac');

// Označi narudžbu kao isporučenu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_delivered'])) {
    $order_id = $_POST['order_id'];
    
    $stmt = $pdo->prepare("UPDATE narudzba SET status_narudzbe = 'Isporuceno' WHERE id_narudzbe = ?");
    $stmt->execute([$order_id]);
    
    $success = "Narudžba #$order_id označena kao isporučena!";
    header('Location: dashboard.php');
    exit();
}

// Dohvati sve narudžbe koje su potvrđene ili u isporuci
$stmt = $pdo->query("
    SELECT 
        n.id_narudzbe,
        CONCAT(k.ime, ' ', k.prezime) as kupac,
        COALESCE(n.adresa_dostave, k.adresa) as adresa_dostave,
        DATE_FORMAT(n.datum, '%d.%m.%Y') as datum,
        COALESCE(r.ukupan_iznos, 0) as iznos,
        n.status_narudzbe
    FROM narudzba n
    JOIN kupac k ON n.id_kupca = k.id_kupca
    LEFT JOIN racun r ON n.id_narudzbe = r.id_narudzbe
    WHERE n.status_narudzbe IN ('Potvrdjeno', 'Na cekanju')
    ORDER BY n.status_narudzbe = 'Potvrdjeno' DESC, n.datum ASC
");
$orders = $stmt->fetchAll();

// Statistike
$total_deliveries = count($orders);
$active_deliveries = count(array_filter($orders, function($o) { 
    return $o['status_narudzbe'] == 'Potvrdjeno'; 
}));
$completed_deliveries = count(array_filter($orders, function($o) { 
    return $o['status_narudzbe'] == 'Isporuceno'; 
}));
?>
<!DOCTYPE html>
<html class="dark" lang="bs">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;800;900&amp;family=Inter:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-surface": "#e2e4cf",
                        "on-primary-fixed-variant": "#3c4d00",
                        "tertiary": "#ffffff",
                        "on-secondary-fixed": "#1c1b1b",
                        "surface-bright": "#373b2c",
                        "tertiary-fixed-dim": "#c4c6cf",
                        "surface-container-highest": "#333627",
                        "secondary-fixed-dim": "#c8c6c5",
                        "on-background": "#e2e4cf",
                        "on-error-container": "#ffdad6",
                        "on-surface-variant": "#c4c9ac",
                        "on-primary-container": "#556d00",
                        "tertiary-container": "#e0e2eb",
                        "secondary": "#c8c6c5",
                        "primary-container": "#c3f400",
                        "on-secondary-fixed-variant": "#474646",
                        "on-primary": "#283500",
                        "surface-container-low": "#1a1d10",
                        "secondary-container": "#4a4949",
                        "error-container": "#93000a",
                        "surface-container-high": "#282b1d",
                        "primary-fixed": "#c3f400",
                        "surface-container": "#1e2113",
                        "on-primary-fixed": "#161e00",
                        "on-secondary": "#313030",
                        "error": "#ffb4ab",
                        "inverse-surface": "#e2e4cf",
                        "surface-container-lowest": "#0c0f04",
                        "on-error": "#690005",
                        "tertiary-fixed": "#e0e2eb",
                        "surface-tint": "#abd600",
                        "primary-fixed-dim": "#abd600",
                        "outline-variant": "#444933",
                        "background": "#111508",
                        "surface-dim": "#111508",
                        "on-tertiary": "#2d3037",
                        "inverse-on-surface": "#2f3223",
                        "surface-variant": "#333627",
                        "on-secondary-container": "#bab8b7",
                        "on-tertiary-container": "#62646c",
                        "outline": "#8e9379",
                        "inverse-primary": "#506600",
                        "surface": "#111508",
                        "primary": "#ffffff",
                        "on-tertiary-fixed": "#191c22",
                        "secondary-fixed": "#e5e2e1",
                        "on-tertiary-fixed-variant": "#44474e"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    spacing: {
                        xs: "4px",
                        sm: "12px",
                        md: "24px",
                        gutter: "24px",
                        xl: "80px",
                        lg: "48px",
                        "container-max": "1280px",
                        base: "8px"
                    },
                    fontFamily: {
                        "headline-xl-mobile": ["Montserrat"],
                        "headline-md": ["Montserrat"],
                        "label-bold": ["Inter"],
                        "headline-xl": ["Montserrat"],
                        "display-lg": ["Montserrat"],
                        "body-md": ["Inter"],
                        "body-lg": ["Inter"],
                        "display-lg-mobile": ["Montserrat"]
                    },
                    fontSize: {
                        "headline-xl-mobile": ["32px", {"lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "800"}],
                        "headline-md": ["24px", {"lineHeight": "1.3", "fontWeight": "700"}],
                        "label-bold": ["14px", {"lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "headline-xl": ["40px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "800"}],
                        "display-lg": ["64px", {"lineHeight": "1.1", "letterSpacing": "-0.04em", "fontWeight": "900"}],
                        "body-md": ["16px", {"lineHeight": "1.5", "fontWeight": "400"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "display-lg-mobile": ["40px", {"lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "900"}]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        body {
            background-color: #111508;
            color: #e2e4cf;
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            border-color: #c3f400;
        }
        .delivery-row {
            transition: background-color 0.2s ease;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-body-md text-body-md selection:bg-primary-fixed selection:text-on-primary-fixed">

<!-- TopNavBar -->
<header class="bg-surface shadow-md fixed top-0 w-full z-50 bg-background/95 backdrop-blur-md border-b border-outline-variant">
    <div class="flex justify-between items-center px-gutter w-full h-20 max-w-container-max mx-auto">
        <div class="font-display-lg-mobile text-display-lg-mobile tracking-tighter text-primary-fixed">
            SUPP.SCIENCE
        </div>
        <nav class="hidden md:flex items-center space-x-md">
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors cursor-pointer" href="../index.php">Početna</a>
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors cursor-pointer" href="#">Moja korpa</a>
            <a class="text-primary-fixed font-bold border-b-2 border-primary-fixed font-label-bold cursor-pointer" href="#">Moje narudžbe</a>
        </nav>
        <div class="flex items-center gap-md">
            <span class="text-on-surface-variant font-label-bold hidden sm:block"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="../logout.php" class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors cursor-pointer">
                Odjava
            </a>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="flex-grow w-full max-w-container-max mx-auto px-gutter pt-32 pb-xl">
    
    <!-- Header Section -->
    <div class="mb-lg space-y-xs">
        <h1 class="font-display-lg-mobile md:font-display-lg text-display-lg-mobile md:text-display-lg text-primary-fixed uppercase">
            PANEL DOSTAVLJAČA
        </h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
            Pregled i upravljanje aktivnim dostavama u realnom vremenu.
        </p>
    </div>

    <!-- Success Message -->
    <?php if (isset($success)): ?>
        <div class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6 border border-green-500/30">
            ✅ <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Dashboard Statistics (Bento Style) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-md mb-lg">
        <div class="stat-card bg-surface-container-high p-md rounded-xl border border-white/5 flex flex-col justify-between hover:border-primary-fixed/50 transition-all">
            <span class="text-on-surface-variant font-label-bold uppercase tracking-wider">Ukupno dostava</span>
            <span class="text-headline-xl font-headline-xl text-primary-fixed"><?php echo $total_deliveries; ?></span>
        </div>
        <div class="stat-card bg-surface-container-high p-md rounded-xl border border-white/5 flex flex-col justify-between hover:border-primary-fixed/50 transition-all">
            <span class="text-on-surface-variant font-label-bold uppercase tracking-wider">Aktivne</span>
            <span class="text-headline-xl font-headline-xl text-tertiary"><?php echo $active_deliveries; ?></span>
        </div>
        <div class="stat-card bg-surface-container-high p-md rounded-xl border border-white/5 flex flex-col justify-between hover:border-primary-fixed/50 transition-all">
            <span class="text-on-surface-variant font-label-bold uppercase tracking-wider">Završene</span>
            <span class="text-headline-xl font-headline-xl text-primary-fixed"><?php echo $completed_deliveries; ?></span>
        </div>
        <div class="stat-card bg-surface-container-high p-md rounded-xl border border-white/5 overflow-hidden relative group hover:border-primary-fixed/50 transition-all">
            <div class="absolute inset-0 bg-primary-fixed/5 group-hover:bg-primary-fixed/10 transition-colors"></div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <span class="text-on-surface-variant font-label-bold uppercase tracking-wider">Sistem Status</span>
                <span class="flex items-center gap-xs text-primary-fixed font-bold">
                    <span class="w-2 h-2 rounded-full bg-primary-fixed animate-pulse"></span>
                    ONLINE
                </span>
            </div>
        </div>
    </div>

    <!-- Active Deliveries Table -->
    <div class="bg-surface-container-low border border-white/5 rounded-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-highest border-b border-outline-variant">
                        <th class="px-md py-md font-label-bold text-primary-fixed uppercase tracking-widest">ID narudžbe</th>
                        <th class="px-md py-md font-label-bold text-primary-fixed uppercase tracking-widest">Kupac</th>
                        <th class="px-md py-md font-label-bold text-primary-fixed uppercase tracking-widest">Adresa za dostavu</th>
                        <th class="px-md py-md font-label-bold text-primary-fixed uppercase tracking-widest">Datum narudžbe</th>
                        <th class="px-md py-md font-label-bold text-primary-fixed uppercase tracking-widest">Iznos</th>
                        <th class="px-md py-md font-label-bold text-primary-fixed uppercase tracking-widest">Status</th>
                        <th class="px-md py-md font-label-bold text-primary-fixed uppercase tracking-widest text-center">Akcija</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" class="px-md py-xl text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl mb-2">local_shipping</span>
                                <p>Trenutno nema aktivnih dostava.</p>
                                <p class="text-sm">Nove narudžbe će se pojaviti ovdje automatski.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): 
                            $is_active = $order['status_narudzbe'] == 'Potvrdjeno';
                        ?>
                        <tr class="delivery-row hover:bg-surface-container-high transition-colors group">
                            <td class="px-md py-md font-headline-md text-headline-md text-tertiary">#<?php echo $order['id_narudzbe']; ?> </td>
                            <td class="px-md py-md">
                                <div class="font-label-bold text-on-surface"><?php echo htmlspecialchars($order['kupac']); ?></div>
                                <?php if (!empty($order['kupac_telefon'])): ?>
                                    <div class="text-xs text-on-surface-variant"><?php echo htmlspecialchars($order['kupac_telefon']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-md py-md text-on-surface-variant font-body-md max-w-[250px]"><?php echo htmlspecialchars($order['adresa_dostave']); ?> </td>
                            <td class="px-md py-md text-on-surface-variant font-body-md"><?php echo $order['datum']; ?> </td>
                            <td class="px-md py-md font-label-bold text-tertiary"><?php echo number_format($order['iznos'], 2); ?> KM</td>
                            <td class="px-md py-md">
                                <?php if ($is_active): ?>
                                    <span class="inline-flex items-center px-sm py-xs rounded-full bg-orange-500/10 text-orange-500 text-[10px] font-bold uppercase tracking-tighter border border-orange-500/20">
                                        ⏳ Čeka dostavu
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-sm py-xs rounded-full bg-yellow-500/10 text-yellow-500 text-[10px] font-bold uppercase tracking-tighter border border-yellow-500/20">
                                        📋 Na čekanju
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-md py-md text-center">
                                <?php if ($is_active): ?>
                                    <form method="POST" onsubmit="return confirm('Potvrdite da je narudžba isporučena?')">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id_narudzbe']; ?>">
                                        <button type="submit" name="mark_delivered" class="inline-flex items-center justify-center gap-xs px-md py-sm bg-primary-fixed text-on-primary-fixed font-label-bold rounded-lg hover:brightness-110 active:scale-95 transition-all">
                                            <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                            Označi kao isporučeno
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-on-surface-variant opacity-50">
                                        <span class="material-symbols-outlined">pending</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delivery Map Preview & Quick Actions -->
    <div class="mt-lg grid grid-cols-1 lg:grid-cols-3 gap-md">
        <div class="lg:col-span-2 bg-surface-container-high rounded-xl overflow-hidden min-h-[300px] border border-white/5 relative">
            <div class="absolute inset-0 grayscale opacity-40 hover:grayscale-0 transition-all duration-700 flex items-center justify-center">
                <div class="text-center">
                    <span class="material-symbols-outlined text-6xl text-primary-fixed/50">map</span>
                    <p class="text-on-surface-variant text-sm mt-2">Mapa dostava (integracija)</p>
                </div>
            </div>
            <div class="absolute bottom-md left-md bg-background/80 backdrop-blur-md p-md rounded-lg border border-primary-fixed/20">
                <h3 class="font-headline-md text-headline-md text-primary-fixed mb-xs">Aktivna Ruta</h3>
                <p class="text-sm text-on-surface-variant">Trenutna optimalna putanja: <?php echo $active_deliveries; ?> aktivnih dostava</p>
            </div>
        </div>
        
        <div class="bg-surface-container-high rounded-xl p-md border border-white/5 space-y-md">
            <h3 class="font-label-bold text-primary-fixed uppercase tracking-widest">Brze Akcije</h3>
            <button class="w-full flex items-center justify-between p-md bg-surface-container-highest rounded-lg border border-white/10 hover:border-primary-fixed/50 transition-all text-left">
                <div>
                    <div class="font-label-bold">Prijavi problem</div>
                    <div class="text-xs text-on-surface-variant">Kašnjenje ili oštećenje</div>
                </div>
                <span class="material-symbols-outlined text-error">report</span>
            </button>
            <button class="w-full flex items-center justify-between p-md bg-surface-container-highest rounded-lg border border-white/10 hover:border-primary-fixed/50 transition-all text-left">
                <div>
                    <div class="font-label-bold">Kontaktiraj bazu</div>
                    <div class="text-xs text-on-surface-variant">Hitne konsultacije</div>
                </div>
                <span class="material-symbols-outlined text-primary-fixed">support_agent</span>
            </button>
            <button class="w-full flex items-center justify-between p-md bg-surface-container-highest rounded-lg border border-white/10 hover:border-primary-fixed/50 transition-all text-left">
                <div>
                    <div class="font-label-bold">Skeniraj QR kod</div>
                    <div class="text-xs text-on-surface-variant">Automatska potvrda</div>
                </div>
                <span class="material-symbols-outlined text-tertiary">qr_code_scanner</span>
            </button>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="bg-surface-container-lowest border-t border-outline-variant mt-auto">
    <div class="flex flex-col md:flex-row justify-between items-center py-xl px-gutter max-w-container-max mx-auto w-full">
        <div class="mb-md md:mb-0">
            <span class="font-headline-md text-headline-md text-primary-fixed uppercase tracking-tighter">SUPP.SCIENCE</span>
            <p class="mt-xs text-on-surface-variant font-body-md">© 2024 SUPP.SCIENCE. Sva prava zadržana.</p>
        </div>
        <div class="flex gap-lg">
            <a class="text-on-surface-variant hover:text-primary-fixed transition-all font-body-md" href="#">O nama</a>
            <a class="text-on-surface-variant hover:text-primary-fixed transition-all font-body-md" href="#">Dostava</a>
            <a class="text-on-surface-variant hover:text-primary-fixed transition-all font-body-md" href="#">Uslovi korištenja</a>
            <a class="text-on-surface-variant hover:text-primary-fixed transition-all font-body-md" href="#">Kontakt</a>
        </div>
    </div>
</footer>

</body>
</html>