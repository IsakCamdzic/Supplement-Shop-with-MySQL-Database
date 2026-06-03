<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
$current_page = basename($_SERVER['PHP_SELF']);
requireRole('admin');

// Statistike
$stmt = $pdo->query("SELECT COUNT(*) as total FROM proizvod");
$products_count = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM kupac");
$customers_count = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM narudzba WHERE status_narudzbe = 'Na cekanju'");
$pending_orders = $stmt->fetch()['total'];

$stmt = $pdo->query("
    SELECT COALESCE(SUM(ukupan_iznos), 0) as total 
    FROM racun 
    WHERE status_placanja = 'Potvrdjeno' 
    AND MONTH(datum_izdavanja) = MONTH(CURDATE())
    AND YEAR(datum_izdavanja) = YEAR(CURDATE())
");
$monthly_sales = $stmt->fetch()['total'];

// Dohvati sve narudžbe
$stmt = $pdo->query("
    SELECT 
        n.id_narudzbe,
        CONCAT(k.ime, ' ', k.prezime) as kupac,
        DATE_FORMAT(n.datum, '%d.%m.%Y') as datum,
        n.status_narudzbe,
        COALESCE(r.status_placanja, 'Nije kreiran') as status_placanja,
        COALESCE(r.ukupan_iznos, 0) as ukupan_iznos
    FROM narudzba n
    JOIN kupac k ON n.id_kupca = k.id_kupca
    LEFT JOIN racun r ON n.id_narudzbe = r.id_narudzbe
    ORDER BY n.datum DESC
");
$orders = $stmt->fetchAll();

// Helper funkcije za badge boje
function getStatusClass($status) {
    switch ($status) {
        case 'Isporuceno': return 'bg-green-500/20 text-green-400 border-green-500/30';
        case 'Potvrdjeno': return 'bg-blue-500/20 text-blue-400 border-blue-500/30';
        case 'Na cekanju': return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
        case 'Otkazano': return 'bg-red-500/20 text-red-400 border-red-500/30';
        default: return 'bg-gray-500/20 text-gray-400 border-gray-500/30';
    }
}

function getPaymentClass($status) {
    switch ($status) {
        case 'Potvrdjeno': return 'bg-green-500/20 text-green-400 border-green-500/30';
        case 'Na cekanju': return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
        case 'Odbijeno': return 'bg-red-500/20 text-red-400 border-red-500/30';
        default: return 'bg-gray-500/20 text-gray-400 border-gray-500/30';
    }
}

// Obrada promjene statusa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $pdo->prepare("UPDATE narudzba SET status_narudzbe = ? WHERE id_narudzbe = ?");
        $stmt->execute([$_POST['status_narudzbe'], $_POST['order_id']]);
        header('Location: dashboard.php');
        exit();
    }
    if (isset($_POST['update_payment'])) {
        $stmt = $pdo->prepare("UPDATE racun SET status_placanja = ? WHERE id_narudzbe = ?");
        $stmt->execute([$_POST['status_placanja'], $_POST['order_id']]);
        header('Location: dashboard.php');
        exit();
    }
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
        .table-row {
            transition: background-color 0.2s ease;
        }
    </style>
</head>
<body class="font-body-md text-body-md overflow-x-hidden">

<!-- TopNavBar Section -->
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
            <h1 class="font-headline-xl text-headline-xl uppercase text-primary">Admin Dashboard</h1>
        </div>
        <p class="text-on-surface-variant font-body-lg">Dobrodošli nazad. Pregledajte performanse vašeg shopa i upravljajte narudžbama.</p>
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
                <h2 class="font-display-lg-mobile text-display-lg-mobile text-primary leading-none"><?php echo $products_count; ?></h2>
                <p class="text-on-surface-variant font-label-bold uppercase mt-xs">Proizvoda</p>
            </div>
        </div>
        
        <!-- Stat 2 -->
        <div class="stat-card bg-surface-container border border-outline-variant/30 rounded-xl p-md flex flex-col justify-between hover:border-primary-fixed/50 transition-all duration-300">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-primary-fixed text-[32px]">group</span>
                <span class="text-on-surface-variant text-[12px] font-label-bold uppercase tracking-wider">Aktivnih</span>
            </div>
            <div class="mt-lg">
                <h2 class="font-display-lg-mobile text-display-lg-mobile text-primary leading-none"><?php echo $customers_count; ?></h2>
                <p class="text-on-surface-variant font-label-bold uppercase mt-xs">Kupaca</p>
            </div>
        </div>
        
        <!-- Stat 3 -->
        <div class="stat-card bg-surface-container border border-outline-variant/30 rounded-xl p-md flex flex-col justify-between hover:border-primary-fixed/50 transition-all duration-300">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-error text-[32px]">pending_actions</span>
                <span class="text-on-surface-variant text-[12px] font-label-bold uppercase tracking-wider">Hitno</span>
            </div>
            <div class="mt-lg">
                <h2 class="font-display-lg-mobile text-display-lg-mobile text-primary leading-none"><?php echo $pending_orders; ?></h2>
                <p class="text-on-surface-variant font-label-bold uppercase mt-xs">Narudžbi na čekanju</p>
            </div>
        </div>
        
        <!-- Stat 4 -->
        <div class="stat-card bg-primary-fixed rounded-xl p-md flex flex-col justify-between shadow-[0_0_30px_rgba(195,244,0,0.2)]">
            <div class="flex justify-between items-start">
                <span class="material-symbols-outlined text-on-primary-fixed text-[32px]">payments</span>
                <span class="text-on-primary-fixed-variant text-[12px] font-label-bold uppercase tracking-wider">Prihod</span>
            </div>
            <div class="mt-lg">
                <h2 class="font-display-lg-mobile text-display-lg-mobile text-on-primary-fixed leading-none"><?php echo number_format($monthly_sales, 2); ?> <span class="text-[20px]">KM</span></h2>
                <p class="text-on-primary-fixed-variant font-label-bold uppercase mt-xs">Prodaja ovog mjeseca</p>
            </div>
        </div>
    </div>

    <!-- Main Table Container -->
    <section class="bg-surface-container-low border border-outline-variant/20 rounded-xl overflow-hidden shadow-2xl">
        <div class="p-md border-b border-outline-variant/30 flex justify-between items-center flex-wrap gap-sm">
            <h3 class="font-headline-md text-headline-md uppercase text-primary">Sve narudžbe</h3>
            <div class="flex gap-sm">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                    <input id="searchInput" class="bg-background border-outline-variant text-on-surface rounded-full pl-10 pr-md py-xs focus:ring-primary-fixed focus:border-primary-fixed text-sm w-64" placeholder="Pretraži kupca..." type="text"/>
                </div>
                <button class="bg-surface-container-high border border-outline-variant p-2 rounded-lg hover:text-primary-fixed transition-colors">
                    <span class="material-symbols-outlined">filter_list</span>
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-high text-on-surface-variant uppercase text-[12px] font-label-bold tracking-widest">
                        <th class="px-md py-sm">ID</th>
                        <th class="px-md py-sm">Kupac</th>
                        <th class="px-md py-sm">Datum</th>
                        <th class="px-md py-sm">Status narudžbe</th>
                        <th class="px-md py-sm">Status plaćanja</th>
                        <th class="px-md py-sm">Iznos</th>
                        <th class="px-md py-sm text-center">Akcije</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10" id="ordersTable">
                    <?php foreach ($orders as $order): ?>
                    <tr class="table-row hover:bg-surface-container-highest/50 transition-colors order-row" data-customer="<?php echo strtolower(htmlspecialchars($order['kupac'])); ?>">
                        <td class="px-md py-md font-label-bold text-on-surface">#<?php echo $order['id_narudzbe']; ?> </td>
                        <td class="px-md py-md font-bold text-primary order-customer"><?php echo htmlspecialchars($order['kupac']); ?> </td>
                        <td class="px-md py-md text-on-surface-variant"><?php echo $order['datum']; ?> </td>
                        <td class="px-md py-md">
                            <form method="POST" class="status-form">
                                <input type="hidden" name="order_id" value="<?php echo $order['id_narudzbe']; ?>">
                                <select name="status_narudzbe" onchange="this.form.submit()" class="bg-background border-outline-variant text-on-surface text-sm rounded-lg focus:ring-primary-fixed py-1 w-full max-w-[160px]">
                                    <option value="Na cekanju" <?php echo $order['status_narudzbe'] == 'Na cekanju' ? 'selected' : ''; ?>>Na čekanju</option>
                                    <option value="Potvrdjeno" <?php echo $order['status_narudzbe'] == 'Potvrdjeno' ? 'selected' : ''; ?>>Potvrđeno</option>
                                    <option value="Isporuceno" <?php echo $order['status_narudzbe'] == 'Isporuceno' ? 'selected' : ''; ?>>Isporučeno</option>
                                    <option value="Otkazano" <?php echo $order['status_narudzbe'] == 'Otkazano' ? 'selected' : ''; ?>>Otkazano</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                         </td>
                        <td class="px-md py-md">
                            <form method="POST" class="payment-form">
                                <input type="hidden" name="order_id" value="<?php echo $order['id_narudzbe']; ?>">
                                <select name="status_placanja" onchange="this.form.submit()" class="bg-background border-outline-variant text-on-surface text-sm rounded-lg focus:ring-primary-fixed py-1 w-full max-w-[160px]">
                                    <option value="Na cekanju" <?php echo $order['status_placanja'] == 'Na cekanju' ? 'selected' : ''; ?>>Na čekanju</option>
                                    <option value="Potvrdjeno" <?php echo $order['status_placanja'] == 'Potvrdjeno' ? 'selected' : ''; ?>>Potvrđeno</option>
                                    <option value="Odbijeno" <?php echo $order['status_placanja'] == 'Odbijeno' ? 'selected' : ''; ?>>Odbijeno</option>
                                </select>
                                <input type="hidden" name="update_payment" value="1">
                            </form>
                         </td>
                        <td class="px-md py-md font-bold text-primary"><?php echo number_format($order['ukupan_iznos'], 2); ?> KM</td>
                        <td class="px-md py-md text-center">
                            <a href="order_details.php?id=<?php echo $order['id_narudzbe']; ?>" class="bg-primary-fixed text-on-primary-fixed font-label-bold px-md py-1.5 rounded uppercase text-[12px] hover:brightness-110 active:scale-95 transition-all inline-block">
                                Detalji
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-md bg-surface-container-low border-t border-outline-variant/30 flex justify-between items-center flex-wrap gap-sm">
            <p class="text-[12px] text-on-surface-variant font-label-bold uppercase">Prikazano <?php echo count($orders); ?> narudžbi</p>
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
// Pretraga po kupcu
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchValue = this.value.toLowerCase();
    let rows = document.querySelectorAll('.order-row');
    
    rows.forEach(row => {
        let customer = row.querySelector('.order-customer').textContent.toLowerCase();
        if (customer.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

</body>
</html>