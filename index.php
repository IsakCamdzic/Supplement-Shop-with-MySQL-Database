<?php
session_start(); 
require_once 'config/database.php';
require_once 'includes/image_helper.php';

// Dohvati sve proizvode
$stmt = $pdo->query("
    SELECT 
        p.proizvod_id as id_proizvoda,
        p.naziv,
        p.cijena,
        COALESCE((SELECT SUM(zt.kolicina_promjena) FROM zaliha_transakcija zt WHERE zt.proizvod_id = p.proizvod_id), 0) as kolicina_na_stanju,
        pr.naziv_proizvodjaca as proizvodjac,
        k.naziv as naziv_kategorije
    FROM proizvod p
    JOIN kategorija k ON p.kategorija_id = k.kategorija_id
    JOIN proizvodjac pr ON p.proizvodjac_id = pr.proizvodjac_id
    ORDER BY p.proizvod_id ASC
");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html class="dark" lang="bs">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
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
        .product-card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .product-card-hover:hover {
            transform: translateY(-8px);
            border-color: #c3f400;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #1a1d10;
        }
        ::-webkit-scrollbar-thumb {
            background: #444933;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #8e9379;
        }
    </style>
</head>
<body class="bg-background text-on-background selection:bg-primary-fixed selection:text-on-primary-fixed">

<!-- TopNavBar -->
<nav class="fixed top-0 w-full z-50 bg-background/95 backdrop-blur-md border-b border-outline-variant shadow-md">
    <div class="flex justify-between items-center h-20 px-gutter max-w-container_max mx-auto">
        <!-- Logo -->
        <a href="/supplementShop/index.php" class="font-display-lg-mobile text-display-lg-mobile font-black text-primary-fixed tracking-tighter uppercase">
            SUPP.SCIENCE
        </a>
        
        <!-- Desktop Navigation Links -->
        <div class="hidden md:flex items-center gap-md">
            <a class="text-primary-fixed font-label-bold border-b-2 border-primary-fixed pb-1" href="/supplementShop/index.php">Početna</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] == 'kupac'): ?>
                    <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="/supplementShop/kupac/dashboard.php">Moja korpa</a>
                    <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="/supplementShop/kupac/narudzbe.php">Moje narudžbe</a>
                <?php endif; ?>
                
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a class="text-primary-fixed font-label-bold hover:brightness-110 transition-colors" href="/supplementShop/admin/dashboard.php">Admin Dashboard</a>
                    <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="/supplementShop/admin/proizvodi.php">Proizvodi</a>
                    <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="/supplementShop/admin/kupci.php">Kupci</a>
                <?php endif; ?>
                
                <?php if ($_SESSION['role'] == 'skladistar'): ?>
                    <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="/supplementShop/skladistar/dashboard.php">Skladište</a>
                <?php endif; ?>
                
                <?php if ($_SESSION['role'] == 'dostavljac'): ?>
                    <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="/supplementShop/dostavljac/dashboard.php">Dostave</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- User Menu -->
        <div class="flex items-center gap-sm">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="hidden sm:block text-right mr-sm">
                    <p class="text-[12px] font-label-bold text-on-surface-variant uppercase tracking-widest leading-none">Prijavljeni ste kao</p>
                    <p class="text-on-surface font-bold leading-tight">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        <span class="text-primary-fixed text-xs ml-1">(<?php echo ucfirst($_SESSION['role']); ?>)</span>
                    </p>
                </div>
                <a href="/supplementShop/logout.php" class="bg-surface-container-highest px-md py-sm rounded-full text-on-surface font-label-bold hover:bg-primary-fixed hover:text-on-primary-fixed transition-all duration-200 active:scale-95 flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">logout</span>
                    Odjava
                </a>
            <?php else: ?>
                <a href="/supplementShop/login.php" class="text-primary-fixed font-label-bold hover:brightness-110 transition-colors">Prijava</a>
                <a href="/supplementShop/register.php" class="bg-primary-fixed text-on-primary-fixed font-label-bold px-md py-sm rounded-lg hover:brightness-110 transition-all">Registracija</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="mt-20 pt-xl pb-lg px-gutter max-w-container_max mx-auto" style="padding-top: 80px; padding-bottom: 48px; padding-left: 24px; padding-right: 24px; margin-top: 80px;">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-lg items-center">
        <div class="lg:col-span-7 space-y-md">
            <span class="inline-block bg-primary-fixed text-on-primary-fixed font-label-bold px-sm py-xs rounded-full uppercase tracking-widest text-[12px]">Elite Performance</span>
            <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg text-primary leading-none">NAUČNO DOKAZANA <br/><span class="text-primary-fixed">SNAGA.</span></h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl">Najbolji svjetski suplementi za vrhunske sportiste. Optimizujte svoj trening uz Supp.Science formulacije dizajnirane za maksimalne rezultate.</p>
            <div class="flex flex-wrap gap-md pt-base">
                <button onclick="document.getElementById('products').scrollIntoView({behavior: 'smooth'})" class="bg-primary-fixed text-on-primary-fixed font-label-bold px-lg py-md rounded-lg active:scale-95 transition-transform flex items-center gap-sm">
                    ISTRAŽI PONUDU
                    <span class="material-symbols-outlined">trending_flat</span>
                </button>
                <button class="border border-outline text-primary font-label-bold px-lg py-md rounded-lg hover:bg-surface-variant transition-colors">
                    NAŠA MISIJA
                </button>
            </div>
        </div>
        <div class="lg:col-span-5 relative">
            <div class="absolute inset-0 bg-primary-fixed/20 blur-[100px] rounded-full"></div>
            <img alt="High Performance Supplements" class="relative z-10 w-full rounded-xl shadow-2xl" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLutO4Lwx0JVPpMRU2K23GfrTO40MgJQwbYT3hT86sP4SRhOwx7Xp0ZMfzaN3F7phaCRf-eIXWouulcK_ewM19SsJEnfoaKhJ1imFg4y7czq5XIE_SzFdCdHIjdYDjIDjR_PIRcdAqv7uMcAc_6BQNHX6tB9hPNw5mzRDCirRAmTsljTSDpqnQWkVMiK4drpY-nNLaewWgWUhC1J84ZufvHyjDWMjanGJEe0mLaYPs_VzyQOGC5zdbWKK3HRirCAdIhCRyEIEeF0sz"/>
        </div>
    </div>
</section>

<!-- Product Grid Section -->
<main id="products" class="py-xl px-gutter max-w-container_max mx-auto" style="padding-top: 80px; padding-bottom: 80px; padding-left: 24px; padding-right: 24px;">
    <div class="flex flex-col md:flex-row justify-between items-end mb-lg gap-md">
        <div>
            <h2 class="font-headline-xl text-headline-xl text-primary mb-xs">Naši proizvodi</h2>
            <div class="h-1.5 w-24 bg-primary-fixed rounded-full"></div>
        </div>
        <div class="text-on-surface-variant text-body-md">
            Ukupno proizvoda: <?php echo count($products); ?>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
        <?php foreach ($products as $product): ?>
        <div class="product-card-hover bg-surface-container-high border border-outline-variant rounded-xl p-md flex flex-col transition-all duration-300" style="padding: 24px;">
            <div class="relative bg-surface-dim rounded-lg p-lg mb-md overflow-hidden group" style="padding: 48px; margin-bottom: 24px;">
                <div class="w-full h-48 flex items-center justify-center bg-surface-variant rounded-lg overflow-hidden">
                <?php 
                $img_path = getProductImage($product['id_proizvoda'], $product['naziv']);
                ?>
                <img src="<?php echo $img_path; ?>" 
                    alt="<?php echo htmlspecialchars($product['naziv']); ?>" 
                    class="w-full h-full object-contain p-2">
                </div>
                <span class="absolute top-3 right-3 bg-primary-fixed text-on-primary-fixed font-label-bold text-[10px] px-sm py-1 rounded-full">
                    <?php echo htmlspecialchars($product['naziv_kategorije']); ?>
                </span>
            </div>
            <div class="flex-grow">
                <p class="text-on-surface-variant font-label-bold text-xs uppercase tracking-wider mb-xs"><?php echo htmlspecialchars($product['proizvodjac']); ?></p>
                <h3 class="font-headline-md text-headline-md text-primary leading-tight mb-base"><?php echo htmlspecialchars($product['naziv']); ?></h3>
                <div class="flex items-center gap-base mb-md">
                    <span class="text-primary-fixed font-headline-md"><?php echo number_format($product['cijena'], 2); ?> KM</span>
                </div>
                <div class="flex items-center gap-xs mb-lg">
                    <span class="material-symbols-outlined text-primary-fixed text-[18px]" style="font-variation-settings: 'FILL' 1;">inventory_2</span>
                    <span class="font-body-md text-on-surface">Na stanju: <?php echo $product['kolicina_na_stanju']; ?> kom</span>
                </div>
            </div>
            <div class="space-y-sm mt-auto">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'kupac'): ?>
                <form method="POST" action="kupac/dashboard.php" class="flex items-center gap-base">
                    <input type="hidden" name="product_id" value="<?php echo $product['id_proizvoda']; ?>">
                    <input type="hidden" name="cijena" value="<?php echo $product['cijena']; ?>">
                    <input type="number" name="kolicina" value="1" min="1" class="w-20 bg-background border border-outline-variant rounded-lg px-sm py-xs text-center font-label-bold focus:border-primary-fixed focus:ring-0 focus:outline-none">
                    <button type="submit" name="add_to_cart" class="flex-grow bg-primary-fixed text-on-primary-fixed font-label-bold py-xs px-md rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-xs">
                        <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                        DODAJ U KORPU
                    </button>
                </form>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="flex-grow bg-primary-fixed text-on-primary-fixed font-label-bold py-xs px-md rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-xs text-center">
                    <span class="material-symbols-outlined text-[20px]">login</span>
                    PRIJAVI SE ZA KUPOVINU
                </a>
                <?php else: ?>
                <div class="text-center text-on-surface-variant text-sm py-xs">
                    Samo za kupce
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Info Banner -->
<section class="py-xl px-gutter max-w-container_max mx-auto" style="padding-top: 80px; padding-bottom: 80px; padding-left: 24px; padding-right: 24px;">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-lg">
        <div class="bg-surface-container border border-outline-variant p-lg rounded-xl flex items-center gap-md transition-all duration-300 hover:border-primary-fixed" style="padding: 24px;">
            <span class="material-symbols-outlined text-primary-fixed text-5xl">local_shipping</span>
            <div>
                <h4 class="font-headline-md text-primary mb-xs">Besplatna Dostava</h4>
                <p class="text-on-surface-variant text-body-md">Za sve narudžbe preko 100 KM.</p>
            </div>
        </div>
        <div class="bg-surface-container border border-outline-variant p-lg rounded-xl flex items-center gap-md transition-all duration-300 hover:border-primary-fixed" style="padding: 24px;">
            <span class="material-symbols-outlined text-primary-fixed text-5xl">verified</span>
            <div>
                <h4 class="font-headline-md text-primary mb-xs">100% Originalno</h4>
                <p class="text-on-surface-variant text-body-md">Direktno od proizvođača.</p>
            </div>
        </div>
        <div class="bg-surface-container border border-outline-variant p-lg rounded-xl flex items-center gap-md transition-all duration-300 hover:border-primary-fixed" style="padding: 24px;">
            <span class="material-symbols-outlined text-primary-fixed text-5xl">support_agent</span>
            <div>
                <h4 class="font-headline-md text-primary mb-xs">Stručna Podrška</h4>
                <p class="text-on-surface-variant text-body-md">Pomažemo u odabiru suplemenata.</p>
            </div>
        </div>
    </div>
</section>

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