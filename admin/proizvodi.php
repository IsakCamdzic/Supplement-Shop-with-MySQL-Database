<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/image_helper.php';
$current_page = basename($_SERVER['PHP_SELF']);
requireRole('admin');

// Brisanje proizvoda
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Provjeri da li proizvod ima narudžbi
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stavke_narudzbe WHERE id_proizvoda = ?");
    $stmt->execute([$id]);
    $has_orders = $stmt->fetchColumn();
    
    if ($has_orders > 0) {
        $error = "Ne možete obrisati proizvod koji je već naručen!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM stavke_korpe WHERE id_proizvoda = ?");
        $stmt->execute([$id]);
        
        $stmt = $pdo->prepare("DELETE FROM proizvod WHERE id_proizvoda = ?");
        $stmt->execute([$id]);
        
        $success = "Proizvod uspješno obrisan!";
    }
    header('Location: proizvodi.php');
    exit();
}

// Dohvati sve proizvode
$stmt = $pdo->query("
    SELECT 
        p.*,
        k.naziv_kategorije,
        (SELECT COUNT(*) FROM stavke_narudzbe WHERE id_proizvoda = p.id_proizvoda) as broj_narudzbi
    FROM proizvod p
    JOIN kategorija k ON p.id_kategorije = k.id_kategorije
    ORDER BY p.id_proizvoda
");
$products = $stmt->fetchAll();

$total_products = count($products);
$low_stock_count = 0;
$total_value = 0;

foreach ($products as $product) {
    if ($product['kolicina_na_stanju'] < 5) {
        $low_stock_count++;
    }
    $total_value += $product['cijena'] * $product['kolicina_na_stanju'];
}
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
        body {
            background-color: #111508;
            color: #e2e4cf;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #1a1d10; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #444933; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #abd600; }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            border-color: #c3f400;
        }
        .product-row {
            transition: background-color 0.2s ease;
        }
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
            <h1 class="font-headline-xl text-headline-xl uppercase text-primary">Upravljanje proizvodima</h1>
        </div>
        <p class="text-on-surface-variant font-body-lg">Dodajte nove proizvode, uredite postojeće i pratite stanje zaliha u realnom vremenu.</p>
    </header>

    <!-- Stats Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-md mb-xl">
        <!-- Stat 1 -->
        <div class="stat-card bg-surface-container border border-outline-variant/30 rounded-xl p-md flex flex-col justify-between hover:border-primary-fixed/50 transition-all duration-300">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-primary-fixed text-[32px]">inventory_2</span>
                <span class="text-on-surface-variant text-[12px] font-label-bold uppercase tracking-wider">Ukupno</span>
            </div>
            <div class="mt-lg">
                <h2 class="font-display-lg-mobile text-display-lg-mobile text-primary leading-none"><?php echo $total_products; ?></h2>
                <p class="text-on-surface-variant font-label-bold uppercase mt-xs">Proizvoda</p>
            </div>
        </div>
        
        <!-- Stat 2 -->
        <div class="stat-card bg-surface-container border border-outline-variant/30 rounded-xl p-md flex flex-col justify-between hover:border-primary-fixed/50 transition-all duration-300">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-primary-fixed text-[32px]">warning</span>
                <span class="text-on-surface-variant text-[12px] font-label-bold uppercase tracking-wider">Niske zalihe</span>
            </div>
            <div class="mt-lg">
                <h2 class="font-display-lg-mobile text-display-lg-mobile text-primary leading-none"><?php echo $low_stock_count; ?></h2>
                <p class="text-on-surface-variant font-label-bold uppercase mt-xs">Potrebna nabavka</p>
            </div>
        </div>
        
        <!-- Stat 3 -->
        <div class="stat-card bg-surface-container border border-outline-variant/30 rounded-xl p-md flex flex-col justify-between hover:border-primary-fixed/50 transition-all duration-300">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-primary-fixed text-[32px]">category</span>
                <span class="text-on-surface-variant text-[12px] font-label-bold uppercase tracking-wider">Kategorije</span>
            </div>
            <div class="mt-lg">
                <h2 class="font-display-lg-mobile text-display-lg-mobile text-primary leading-none">7</h2>
                <p class="text-on-surface-variant font-label-bold uppercase mt-xs">Različitih</p>
            </div>
        </div>
        
        <!-- Stat 4 -->
        <div class="stat-card bg-primary-fixed rounded-xl p-md flex flex-col justify-between shadow-[0_0_30px_rgba(195,244,0,0.2)]">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-on-primary-fixed text-[32px]">store</span>
                <span class="text-on-primary-fixed-variant text-[12px] font-label-bold uppercase tracking-wider">Vrijednost</span>
            </div>
            <div class="mt-lg">
                <h2 class="font-display-lg-mobile text-display-lg-mobile text-on-primary-fixed leading-none"><?php echo number_format($total_value, 0); ?> <span class="text-[20px]">KM</span></h2>
                <p class="text-on-primary-fixed-variant font-label-bold uppercase mt-xs">Zaliha ukupno</p>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="flex flex-col md:flex-row gap-md items-center justify-between mb-lg">
        <div class="relative w-full md:w-96">
            <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
            <input id="searchInput" class="bg-surface-container-low border-outline-variant text-on-surface rounded-full pl-10 pr-md py-sm focus:ring-primary-fixed focus:border-primary-fixed text-sm w-full" placeholder="Pretraži proizvode..." type="text"/>
        </div>
        <div class="flex gap-sm w-full md:w-auto">
            <button class="flex-1 md:flex-none bg-surface-container-high border border-outline-variant px-md py-sm rounded-lg text-on-surface font-label-bold flex items-center justify-center gap-xs hover:text-primary-fixed transition-colors">
                <span class="material-symbols-outlined text-[20px]">filter_list</span>
                Filteri
            </button>
            <button class="flex-1 md:flex-none bg-primary-fixed text-on-primary-fixed px-lg py-sm rounded-lg font-label-bold flex items-center justify-center gap-xs hover:brightness-110 active:scale-95 transition-all shadow-[0_0_20px_rgba(195,244,0,0.2)]">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                Dodaj Proizvod
            </button>
        </div>
    </div>

    <!-- Main Table Container -->
    <section class="bg-surface-container-low border border-outline-variant/20 rounded-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-high text-on-surface-variant uppercase text-[12px] font-label-bold tracking-widest">
                        <th class="px-md py-sm">ID</th>
                        <th class="px-md py-sm">Proizvod</th>
                        <th class="px-md py-sm">Kategorija</th>
                        <th class="px-md py-sm">Cijena</th>
                        <th class="px-md py-sm">Zaliha</th>
                        <th class="px-md py-sm">Proizvođač</th>
                        <th class="px-md py-sm">Status</th>
                        <th class="px-md py-sm text-right">Akcije</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10" id="productsTable">
                    <?php foreach ($products as $product): 
                        $stock_status = $product['kolicina_na_stanju'] < 5 ? 'Niska zaliha' : 'Na stanju';
                        $stock_class = $product['kolicina_na_stanju'] < 5 ? 'bg-error-container text-on-error-container animate-pulse' : 'bg-primary-container text-on-primary-container';
                    ?>
                    <tr class="product-row hover:bg-surface-container-highest/50 transition-colors product-row" data-name="<?php echo strtolower(htmlspecialchars($product['naziv'])); ?>">
                        <td class="px-md py-md font-label-bold text-on-surface-variant">#PRD-<?php echo str_pad($product['id_proizvoda'], 3, '0', STR_PAD_LEFT); ?> </td>
                        <td class="px-md py-md">
                            <div class="flex items-center gap-sm">
                                <div class="w-10 h-10 rounded bg-surface-container-highest flex items-center justify-center overflow-hidden">
                                <?php 
                                $img_path = getProductImage($product['id_proizvoda'], $product['naziv']);
                                ?>
                                <img src="<?php echo $img_path; ?>" 
                                    alt="<?php echo htmlspecialchars($product['naziv']); ?>" 
                                    class="w-full h-full object-cover">
                                </div>
                                <span class="font-bold text-primary product-name"><?php echo htmlspecialchars($product['naziv']); ?></span>
                            </div>
                        </td>
                        <td class="px-md py-md text-on-surface-variant"><?php echo htmlspecialchars($product['naziv_kategorije']); ?> </td>
                        <td class="px-md py-md font-bold text-primary"><?php echo number_format($product['cijena'], 2); ?> KM</td>
                        <td class="px-md py-md text-on-surface"><?php echo $product['kolicina_na_stanju']; ?> kom</td>
                        <td class="px-md py-md text-on-surface-variant"><?php echo htmlspecialchars($product['proizvodjac']); ?> </td>
                        <td class="px-md py-md">
                            <span class="inline-flex items-center px-sm py-1 rounded-full text-[11px] font-bold uppercase <?php echo $stock_class; ?>">
                                <?php echo $stock_status; ?>
                            </span>
                        </td>
                        <td class="px-md py-md">
                            <div class="flex justify-end gap-sm">
                                <a href="#" class="w-8 h-8 flex items-center justify-center rounded bg-surface-container-high text-primary-fixed hover:bg-primary-fixed hover:text-on-primary-fixed transition-all" title="Uredi">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </a>
                                <?php if ($product['broj_narudzbi'] == 0): ?>
                                    <a href="?delete=<?php echo $product['id_proizvoda']; ?>" class="w-8 h-8 flex items-center justify-center rounded bg-surface-container-high text-error hover:bg-error-container hover:text-on-error-container transition-all" title="Obriši" onclick="return confirm('Trajno obriši proizvod? Ova akcija se NE MOŽE oporaviti!')">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </a>
                                <?php else: ?>
                                    <span class="w-8 h-8 flex items-center justify-center rounded bg-surface-container-high text-gray-500 cursor-not-allowed" title="Ne može se obrisati - proizvod je naručen">
                                        <span class="material-symbols-outlined text-[18px]">block</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-md bg-surface-container-low border-t border-outline-variant/30 flex justify-between items-center flex-wrap gap-sm">
            <p class="text-[12px] text-on-surface-variant font-label-bold uppercase">Prikazano <?php echo $total_products; ?> od <?php echo $total_products; ?> proizvoda</p>
            <div class="flex gap-xs">
                <button class="w-8 h-8 rounded bg-surface-container-high border border-outline-variant flex items-center justify-center text-on-surface hover:text-primary-fixed transition-colors">
                    <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                </button>
                <button class="w-8 h-8 rounded bg-primary-fixed text-on-primary-fixed flex items-center justify-center font-bold">1</button>
                <button class="w-8 h-8 rounded bg-surface-container-high border border-outline-variant flex items-center justify-center text-on-surface hover:text-primary-fixed transition-colors">
                    <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                </button>
            </div>
        </div>
    </section>
</main>

<!-- Footer Section -->
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

<script>
// Pretraga po proizvodu
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchValue = this.value.toLowerCase();
    let rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        let productName = row.querySelector('.product-name').textContent.toLowerCase();
        if (productName.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

</body>
</html>