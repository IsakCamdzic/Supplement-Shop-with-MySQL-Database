<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
$current_page = basename($_SERVER['PHP_SELF']);
requireRole('admin');

// Dohvati top 10 proizvoda iz view-a
$stmt = $pdo->query("SELECT * FROM top10_proizvoda");
$top_products = $stmt->fetchAll();

// Dohvati dodatne statistike za dashboard
$stmt = $pdo->query("
    SELECT 
        COALESCE(SUM(ukupna_prodaja), 0) as total_sales,
        COALESCE(SUM(ukupno_prodano), 0) as total_items
    FROM top10_proizvoda
");
$stats = $stmt->fetch();

// Najprodavanija kategorija
$stmt = $pdo->query("
    SELECT 
        naziv_kategorije,
        SUM(ukupno_prodano) as ukupno
    FROM top10_proizvoda
    GROUP BY naziv_kategorije
    ORDER BY ukupno DESC
    LIMIT 1
");
$top_category = $stmt->fetch();
?>
<!DOCTYPE html>
<html class="dark" lang="bs">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&amp;family=Montserrat:wght@700;800;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-container": "#1e2113",
                        "secondary-fixed-dim": "#c8c6c5",
                        "primary-fixed-dim": "#abd600",
                        "on-secondary-fixed": "#1c1b1b",
                        "outline-variant": "#444933",
                        "on-error-container": "#ffdad6",
                        "surface": "#111508",
                        "on-background": "#e2e4cf",
                        "surface-container-high": "#282b1d",
                        "primary": "#ffffff",
                        "on-primary": "#283500",
                        "tertiary-fixed-dim": "#c4c6cf",
                        "tertiary": "#ffffff",
                        "surface-container-low": "#1a1d10",
                        "surface-bright": "#373b2c",
                        "primary-container": "#c3f400",
                        "inverse-primary": "#506600",
                        "on-surface-variant": "#c4c9ac",
                        "on-tertiary-container": "#62646c",
                        "surface-variant": "#333627",
                        "inverse-surface": "#e2e4cf",
                        "on-tertiary-fixed-variant": "#44474e",
                        "error-container": "#93000a",
                        "surface-container-lowest": "#0c0f04",
                        "on-secondary": "#313030",
                        "on-tertiary-fixed": "#191c22",
                        "secondary-container": "#4a4949",
                        "surface-dim": "#111508",
                        "on-primary-fixed-variant": "#3c4d00",
                        "tertiary-container": "#e0e2eb",
                        "primary-fixed": "#c3f400",
                        "on-primary-container": "#556d00",
                        "surface-container-highest": "#333627",
                        "tertiary-fixed": "#e0e2eb",
                        "on-error": "#690005",
                        "outline": "#8e9379",
                        "secondary": "#c8c6c5",
                        "inverse-on-surface": "#2f3223",
                        "on-secondary-fixed-variant": "#474646",
                        "on-secondary-container": "#bab8b7",
                        "secondary-fixed": "#e5e2e1",
                        "background": "#111508",
                        "on-primary-fixed": "#161e00",
                        "error": "#ffb4ab",
                        "surface-tint": "#abd600",
                        "on-surface": "#e2e4cf",
                        "on-tertiary": "#2d3037"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    spacing: {
                        xl: "80px",
                        "container-max": "1280px",
                        xs: "4px",
                        sm: "12px",
                        lg: "48px",
                        md: "24px",
                        gutter: "24px",
                        base: "8px"
                    },
                    fontFamily: {
                        "label-bold": ["Inter"],
                        "display-lg-mobile": ["Montserrat"],
                        "display-lg": ["Montserrat"],
                        "headline-xl-mobile": ["Montserrat"],
                        "headline-xl": ["Montserrat"],
                        "body-md": ["Inter"],
                        "headline-md": ["Montserrat"],
                        "body-lg": ["Inter"]
                    },
                    fontSize: {
                        "label-bold": ["14px", {"lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "display-lg-mobile": ["40px", {"lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "900"}],
                        "display-lg": ["64px", {"lineHeight": "1.1", "letterSpacing": "-0.04em", "fontWeight": "900"}],
                        "headline-xl-mobile": ["32px", {"lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "800"}],
                        "headline-xl": ["40px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "800"}],
                        "body-md": ["16px", {"lineHeight": "1.5", "fontWeight": "400"}],
                        "headline-md": ["24px", {"lineHeight": "1.3", "fontWeight": "700"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}]
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #111508; color: #e2e4cf; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #1a1d10; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #444933; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #abd600; }
        .rank-1 { 
            background: linear-gradient(135deg, #c3f40020 0%, #c3f40005 100%);
            border-left: 3px solid #c3f400;
        }
        .rank-2 { border-left: 3px solid #abd600; }
        .rank-3 { border-left: 3px solid #8e9379; }
    </style>
</head>
<body class="font-body-md text-body-md overflow-x-hidden">

<!-- TopNavBar Section -->
<!-- Admin Navigation -->
<nav class="fixed top-0 w-full z-50 bg-background/95 backdrop-blur-md border-b border-outline-variant shadow-md">
    <div class="flex justify-between items-center h-20 px-6 max-w-7xl mx-auto">
        <div class="font-bold text-2xl text-primary-fixed tracking-tighter">
            SUPP.SCIENCE ADMIN
        </div>
        <div class="hidden md:flex items-center gap-6">
            <a href="/supplementShop/admin/dashboard.php" class="font-label-bold transition-colors <?php echo ($current_page == 'dashboard.php') ? 'text-primary-fixed border-b-2 border-primary-fixed pb-1' : 'text-on-surface-variant hover:text-primary-fixed'; ?>">
                Dashboard
            </a>
            <a href="/supplementShop/admin/proizvodi.php" class="font-label-bold transition-colors <?php echo ($current_page == 'proizvodi.php') ? 'text-primary-fixed border-b-2 border-primary-fixed pb-1' : 'text-on-surface-variant hover:text-primary-fixed'; ?>">
                Proizvodi
            </a>
            <a href="/supplementShop/admin/kupci.php" class="font-label-bold transition-colors <?php echo ($current_page == 'kupci.php') ? 'text-primary-fixed border-b-2 border-primary-fixed pb-1' : 'text-on-surface-variant hover:text-primary-fixed'; ?>">
                Kupci
            </a>
            <a href="/supplementShop/admin/top10.php" class="font-label-bold transition-colors <?php echo ($current_page == 'top10.php') ? 'text-primary-fixed border-b-2 border-primary-fixed pb-1' : 'text-on-surface-variant hover:text-primary-fixed'; ?>">
                Top 10
            </a>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <p class="text-xs text-on-surface-variant uppercase">Prijavljeni ste kao</p>
                <p class="text-on-surface font-bold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            </div>
            <a href="/supplementShop/logout.php" class="bg-surface-container-highest px-4 py-2 rounded-full hover:bg-primary-fixed hover:text-on-primary-fixed transition-all">Logout</a>
        </div>
    </div>
</nav>

<!-- Main Content Canvas -->
<main class="pt-32 pb-xl px-gutter max-w-container-max mx-auto">
    
    <!-- Hero Header -->
    <header class="mb-lg">
        <div class="flex items-end gap-sm mb-xs">
            <div class="w-1.5 h-8 bg-primary-fixed"></div>
            <h1 class="font-headline-xl text-headline-xl uppercase text-primary">Top 10 proizvoda</h1>
        </div>
        <p class="text-on-surface-variant font-body-lg">Najprodavaniji proizvodi svih vremena prema ukupnoj prodaji.</p>
    </header>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-md mb-xl">
        <div class="stat-card bg-surface-container border border-outline-variant/30 rounded-xl p-md flex justify-between items-center hover:border-primary-fixed/50 transition-all duration-300">
            <div>
                <p class="text-on-surface-variant text-sm uppercase tracking-wider">Ukupna prodaja</p>
                <p class="text-2xl font-bold text-primary-fixed"><?php echo number_format($stats['total_sales'], 2); ?> KM</p>
            </div>
            <span class="material-symbols-outlined text-primary-fixed text-4xl">payments</span>
        </div>
        
        <div class="stat-card bg-surface-container border border-outline-variant/30 rounded-xl p-md flex justify-between items-center hover:border-primary-fixed/50 transition-all duration-300">
            <div>
                <p class="text-on-surface-variant text-sm uppercase tracking-wider">Ukupno prodanih komada</p>
                <p class="text-2xl font-bold text-primary-fixed"><?php echo number_format($stats['total_items']); ?></p>
            </div>
            <span class="material-symbols-outlined text-primary-fixed text-4xl">shopping_bag</span>
        </div>
        
        <div class="stat-card bg-primary-fixed rounded-xl p-md flex justify-between items-center shadow-[0_0_30px_rgba(195,244,0,0.2)]">
            <div>
                <p class="text-on-primary-fixed text-sm uppercase tracking-wider">Najbolja kategorija</p>
                <p class="text-2xl font-bold text-on-primary-fixed"><?php echo htmlspecialchars($top_category['naziv_kategorije'] ?? 'Nema podataka'); ?></p>
            </div>
            <span class="material-symbols-outlined text-on-primary-fixed text-4xl">emoji_events</span>
        </div>
    </div>

    <!-- Top 10 Table -->
    <section class="bg-surface-container-low border border-outline-variant/20 rounded-xl overflow-hidden shadow-2xl">
        <div class="p-md border-b border-outline-variant/30">
            <h3 class="font-headline-md text-headline-md uppercase text-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-primary-fixed">leaderboard</span>
                TOP 10 NAJPRODAVANIJIH
            </h3>
        </div>
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-high text-on-surface-variant uppercase text-[12px] font-label-bold tracking-widest">
                        <th class="px-md py-sm text-center w-16">#</th>
                        <th class="px-md py-sm">Proizvod</th>
                        <th class="px-md py-sm">Proizvođač</th>
                        <th class="px-md py-sm">Kategorija</th>
                        <th class="px-md py-sm text-center">Prodano komada</th>
                        <th class="px-md py-sm text-right">Ukupna prodaja</th>
                        <th class="px-md py-sm text-center">Udio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    <?php 
                    $total_sales = $stats['total_sales'];
                    foreach ($top_products as $index => $product):
                        $rank = $index + 1;
                        $percentage = $total_sales > 0 ? ($product['ukupna_prodaja'] / $total_sales) * 100 : 0;
                        $rank_class = '';
                        if ($rank == 1) $rank_class = 'rank-1';
                        elseif ($rank == 2) $rank_class = 'rank-2';
                        elseif ($rank == 3) $rank_class = 'rank-3';
                    ?>
                    <tr class="hover:bg-surface-container-highest/50 transition-colors <?php echo $rank_class; ?>">
                        <td class="px-md py-md text-center">
                            <?php if ($rank == 1): ?>
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-fixed text-on-primary-fixed font-bold text-lg">🥇</span>
                            <?php elseif ($rank == 2): ?>
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-surface-container-highest text-primary-fixed font-bold text-lg">🥈</span>
                            <?php elseif ($rank == 3): ?>
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-surface-container-highest text-primary-fixed font-bold text-lg">🥉</span>
                            <?php else: ?>
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-surface-container-highest text-on-surface-variant font-bold"><?php echo $rank; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-md py-md">
                            <div class="flex items-center gap-sm">
                                <div class="w-10 h-10 rounded bg-surface-container-highest flex items-center justify-center overflow-hidden border border-outline-variant/20">
                                    <span class="material-symbols-outlined text-primary-fixed">fitness_center</span>
                                </div>
                                <span class="font-bold text-primary"><?php echo htmlspecialchars($product['naziv']); ?></span>
                            </div>
                        </td>
                        <td class="px-md py-md text-on-surface-variant"><?php echo htmlspecialchars($product['proizvodjac']); ?> </td>
                        <td class="px-md py-md text-on-surface-variant">
                            <span class="inline-flex px-sm py-1 rounded-full bg-surface-container-highest text-xs">
                                <?php echo htmlspecialchars($product['naziv_kategorije']); ?>
                            </span>
                        </td>
                        <td class="px-md py-md text-center font-bold text-on-surface">
                            <?php echo number_format($product['ukupno_prodano']); ?> kom
                        </td>
                        <td class="px-md py-md text-right font-bold text-primary-fixed">
                            <?php echo number_format($product['ukupna_prodaja'], 2); ?> KM
                        </td>
                        <td class="px-md py-md">
                            <div class="flex items-center gap-2">
                                <div class="flex-grow h-2 bg-surface-container-highest rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-fixed rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="text-xs text-on-surface-variant w-12"><?php echo number_format($percentage, 1); ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($top_products)): ?>
        <div class="p-xl text-center">
            <span class="material-symbols-outlined text-6xl text-on-surface-variant mb-4">bar_chart_4_bars</span>
            <p class="text-on-surface-variant">Još nema prodanih proizvoda.</p>
            <p class="text-on-surface-variant text-sm">Kupci će se pojaviti ovdje nakon prve narudžbe.</p>
        </div>
        <?php endif; ?>
        
        <div class="p-md bg-surface-container-low border-t border-outline-variant/30 flex justify-between items-center flex-wrap gap-sm">
            <p class="text-[12px] text-on-surface-variant font-label-bold uppercase">
                Prikazano top <?php echo count($top_products); ?> proizvoda
            </p>
            <?php if (!empty($top_products) && count($top_products) >= 10): ?>
            <p class="text-[12px] text-primary-fixed font-label-bold uppercase">
                🔥 Kompletna lista najprodavanijih proizvoda
            </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Info Card -->
    <div class="mt-xl bg-surface-container border border-outline-variant/30 rounded-xl p-md">
        <div class="flex items-center gap-2 mb-md text-primary-fixed">
            <span class="material-symbols-outlined">info</span>
            <h4 class="font-headline-md text-headline-md">Napomena</h4>
        </div>
        <div class="text-on-surface-variant text-sm">
            <p>• Top lista se ažurira automatski na osnovu uspješnih narudžbi.</p>
            <p>• Otkazane narudžbe se ne uračunavaju u statistiku.</p>
            <p>• Podaci prikazuju ukupnu prodaju za sve vremenske periode.</p>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="w-full py-lg mt-xl bg-surface-container-lowest border-t border-outline-variant">
    <div class="flex flex-col md:flex-row justify-between items-center px-gutter max-w-container-max mx-auto gap-md">
        <div>
            <div class="font-headline-md text-headline-md font-bold text-on-surface uppercase mb-xs">SUPP.SCIENCE</div>
            <p class="text-on-surface-variant font-body-md">© 2024 SUPP.SCIENCE. Sva prava pridržana.</p>
        </div>
        <div class="flex flex-wrap justify-center gap-lg">
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">O nama</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">Dostava</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">Uslovi korištenja</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">Kontakt</a>
        </div>
        <div class="flex gap-md">
            <span class="material-symbols-outlined text-secondary hover:text-primary-fixed transition-colors cursor-pointer">social_leaderboard</span>
            <span class="material-symbols-outlined text-secondary hover:text-primary-fixed transition-colors cursor-pointer">share_reviews</span>
            <span class="material-symbols-outlined text-secondary hover:text-primary-fixed transition-colors cursor-pointer">monitoring</span>
        </div>
    </div>
</footer>

</body>
</html>