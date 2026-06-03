<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/image_helper.php';
requireRole('skladistar');

// Dodavanje novog proizvoda
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $naziv = $_POST['naziv'];
    $cijena = $_POST['cijena'];
    $kolicina = $_POST['kolicina'];
    $proizvodjac = $_POST['proizvodjac'];
    $id_kategorije = $_POST['id_kategorije'];
    $opis = $_POST['opis'];
    
    $stmt = $pdo->prepare("
        INSERT INTO proizvod (naziv, cijena, kolicina_na_stanju, proizvodjac, id_kategorije, opis) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$naziv, $cijena, $kolicina, $proizvodjac, $id_kategorije, $opis]);
    
    $success = "Proizvod uspješno dodat!";
}

// Ažuriranje količine
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['quantity'];
    
    $stmt = $pdo->prepare("UPDATE proizvod SET kolicina_na_stanju = ? WHERE id_proizvoda = ?");
    $stmt->execute([$new_quantity, $product_id]);
    $success = "Zalihe ažurirane!";
}

// Brisanje proizvoda (samo ako nema narudžbi)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stavke_narudzbe WHERE id_proizvoda = ?");
    $stmt->execute([$id]);
    $has_orders = $stmt->fetchColumn();
    
    if ($has_orders == 0) {
        $stmt = $pdo->prepare("DELETE FROM proizvod WHERE id_proizvoda = ?");
        $stmt->execute([$id]);
        $success = "Proizvod obrisan!";
    } else {
        $error = "Ne možete obrisati proizvod koji je već naručen!";
    }
    header('Location: dashboard.php');
    exit();
}

// Dohvati kategorije za dropdown
$stmt = $pdo->query("SELECT * FROM kategorija ORDER BY naziv_kategorije");
$categories = $stmt->fetchAll();

// Dohvati sve proizvode sa statistikom prodaje
$stmt = $pdo->query("
    SELECT 
        p.*,
        k.naziv_kategorije,
        COALESCE(SUM(sn.kolicina), 0) as prodano
    FROM proizvod p
    JOIN kategorija k ON p.id_kategorije = k.id_kategorije
    LEFT JOIN stavke_narudzbe sn ON p.id_proizvoda = sn.id_proizvoda
    GROUP BY p.id_proizvoda
    ORDER BY p.id_proizvoda
");
$products = $stmt->fetchAll();

$total_products = count($products);
$low_stock_count = count(array_filter($products, function($p) { return $p['kolicina_na_stanju'] < 5; }));
?>
<!DOCTYPE html>
<html class="dark" lang="bs">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&amp;family=Inter:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            background-color: #111508;
            color: #e2e4cf;
        }
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #111508;
        }
        ::-webkit-scrollbar-thumb {
            background: #333627;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #abd600;
        }
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
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-bright": "#373b2c",
                        "on-secondary-fixed": "#1c1b1b",
                        "error-container": "#93000a",
                        "secondary-fixed": "#e5e2e1",
                        "secondary-fixed-dim": "#c8c6c5",
                        "on-tertiary-fixed-variant": "#44474e",
                        "tertiary-fixed": "#e0e2eb",
                        "on-error": "#690005",
                        "on-tertiary-fixed": "#191c22",
                        "surface-container-high": "#282b1d",
                        "on-primary": "#283500",
                        "primary-fixed-dim": "#abd600",
                        "on-secondary-container": "#bab8b7",
                        "surface-container-highest": "#333627",
                        "inverse-surface": "#e2e4cf",
                        "surface-tint": "#abd600",
                        "surface": "#111508",
                        "secondary-container": "#4a4949",
                        "error": "#ffb4ab",
                        "surface-container-lowest": "#0c0f04",
                        "on-tertiary": "#2d3037",
                        "background": "#111508",
                        "primary-container": "#c3f400",
                        "tertiary-fixed-dim": "#c4c6cf",
                        "surface-variant": "#333627",
                        "surface-dim": "#111508",
                        "inverse-on-surface": "#2f3223",
                        "on-surface": "#e2e4cf",
                        "on-primary-fixed-variant": "#3c4d00",
                        "surface-container": "#1e2113",
                        "on-secondary-fixed-variant": "#474646",
                        "on-primary-container": "#556d00",
                        "inverse-primary": "#506600",
                        "surface-container-low": "#1a1d10",
                        "primary": "#ffffff",
                        "secondary": "#c8c6c5",
                        "outline-variant": "#444933",
                        "on-primary-fixed": "#161e00",
                        "tertiary-container": "#e0e2eb",
                        "on-secondary": "#313030",
                        "tertiary": "#ffffff",
                        "on-error-container": "#ffdad6",
                        "on-background": "#e2e4cf",
                        "on-tertiary-container": "#62646c",
                        "on-surface-variant": "#c4c9ac",
                        "primary-fixed": "#c3f400",
                        "outline": "#8e9379"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    spacing: {
                        sm: "12px",
                        gutter: "24px",
                        base: "8px",
                        xs: "4px",
                        "container-max": "1280px",
                        lg: "48px",
                        xl: "80px",
                        md: "24px"
                    },
                    fontFamily: {
                        "headline-md": ["Montserrat"],
                        "display-lg-mobile": ["Montserrat"],
                        "headline-xl-mobile": ["Montserrat"],
                        "body-lg": ["Inter"],
                        "headline-xl": ["Montserrat"],
                        "display-lg": ["Montserrat"],
                        "body-md": ["Inter"],
                        "label-bold": ["Inter"]
                    },
                    fontSize: {
                        "headline-md": ["24px", {"lineHeight": "1.3", "fontWeight": "700"}],
                        "display-lg-mobile": ["40px", {"lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "900"}],
                        "headline-xl-mobile": ["32px", {"lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "800"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "headline-xl": ["40px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "800"}],
                        "display-lg": ["64px", {"lineHeight": "1.1", "letterSpacing": "-0.04em", "fontWeight": "900"}],
                        "body-md": ["16px", {"lineHeight": "1.5", "fontWeight": "400"}],
                        "label-bold": ["14px", {"lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "700"}]
                    }
                }
            }
        }
    </script>
</head>
<body class="font-body-md text-body-md">

<!-- TopNavBar -->
<header class="fixed top-0 w-full z-50 bg-background/95 backdrop-blur-md border-b border-outline-variant shadow-md">
    <div class="flex justify-between items-center h-20 px-gutter max-w-container-max mx-auto">
        <div class="font-display-lg-mobile text-display-lg-mobile font-black text-primary-fixed tracking-tighter">
            SUPP.SCIENCE
        </div>
        <nav class="hidden md:flex gap-md items-center">
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="../index.php">Početna</a>
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="#">Moja korpa</a>
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="#">Moje narudžbe</a>
            <div class="h-6 w-px bg-outline-variant mx-sm"></div>
            <a href="../logout.php" class="flex items-center gap-xs text-primary-fixed font-label-bold active:scale-95 transition-transform">
                <span class="material-symbols-outlined">logout</span>
                Logout
            </a>
        </nav>
    </div>
</header>

<main class="pt-32 pb-xl px-gutter max-w-container-max mx-auto">
    
    <!-- Page Title -->
    <div class="mb-xl">
        <h1 class="font-headline-xl text-headline-xl text-on-surface tracking-tighter uppercase">Upravljanje zalihama</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant mt-xs">Dodajte nove artikle i pratite stanje skladišta u realnom vremenu.</p>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($success)): ?>
        <div class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6 border border-green-500/30">
            ✅ <?php echo $success; ?>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6 border border-red-500/30">
            ❌ <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-xl">
        
        <!-- Add New Product Form Section -->
        <section class="lg:col-span-4 h-fit">
            <div class="bg-surface-container p-md border border-outline-variant rounded-lg shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-base">
                    <span class="material-symbols-outlined text-primary-fixed/20 text-6xl"></span>
                </div>
                <h2 class="font-headline-md text-headline-md text-on-surface mb-md">Dodaj novi proizvod</h2>
                <form method="POST" class="space-y-md relative z-10">
                    <div class="space-y-xs">
                        <label class="font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Naziv proizvoda</label>
                        <input name="naziv" class="w-full bg-black border border-outline-variant p-sm text-on-surface focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed outline-none transition-all rounded" placeholder="npr. Whey Isolate 2kg" type="text" required/>
                    </div>
                    <div class="grid grid-cols-2 gap-sm">
                        <div class="space-y-xs">
                            <label class="font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Cijena (KM)</label>
                            <input name="cijena" class="w-full bg-black border border-outline-variant p-sm text-on-surface focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed outline-none transition-all rounded" placeholder="0.00" type="number" step="0.01" required/>
                        </div>
                        <div class="space-y-xs">
                            <label class="font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Količina na stanju</label>
                            <input name="kolicina" class="w-full bg-black border border-outline-variant p-sm text-on-surface focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed outline-none transition-all rounded" placeholder="0" type="number" required/>
                        </div>
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Proizvođač</label>
                        <input name="proizvodjac" class="w-full bg-black border border-outline-variant p-sm text-on-surface focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed outline-none transition-all rounded" placeholder="npr. Optimum Nutrition" type="text" required/>
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Kategorija</label>
                        <select name="id_kategorije" class="w-full bg-black border border-outline-variant p-sm text-on-surface focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed outline-none transition-all rounded appearance-none" required>
                            <option value="">Izaberi kategoriju</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id_kategorije']; ?>"><?php echo htmlspecialchars($cat['naziv_kategorije']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-xs">
                        <label class="font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Opis</label>
                        <textarea name="opis" class="w-full bg-black border border-outline-variant p-sm text-on-surface focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed outline-none transition-all rounded resize-none" placeholder="Detaljne specifikacije proizvoda..." rows="4" required></textarea>
                    </div>
                    <button type="submit" name="add_product" class="w-full bg-primary-fixed text-on-primary-fixed font-label-bold py-sm rounded-lg active:scale-95 transition-transform hover:shadow-[0_0_20px_rgba(195,244,0,0.3)] flex justify-center items-center gap-sm">
                        <span class="material-symbols-outlined">add_circle</span>
                        DODAJ PROIZVOD
                    </button>
                </form>
            </div>
        </section>

        <!-- Stock List Table Section -->
        <section class="lg:col-span-8">
            <div class="bg-surface-container border border-outline-variant rounded-lg overflow-hidden">
                <div class="p-md border-b border-outline-variant flex justify-between items-center bg-surface-container-high">
                    <h2 class="font-headline-md text-headline-md text-on-surface">Lista zaliha</h2>
                    <div class="flex gap-sm">
                        <div class="relative">
                            <input id="searchInput" class="bg-black border border-outline-variant pl-10 pr-sm py-xs text-sm text-on-surface rounded-full focus:border-primary-fixed outline-none" placeholder="Pretraži artikle..." type="text"/>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container-highest border-b border-outline-variant">
                                <th class="p-md font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">ID</th>
                                <th class="p-md font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Proizvod</th>
                                <th class="p-md font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Kategorija</th>
                                <th class="p-md font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Trenutna količina</th>
                                <th class="p-md font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Prodano</th>
                                <th class="p-md font-label-bold text-on-surface-variant uppercase tracking-widest text-[10px]">Akcije</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant" id="productsTable">
                            <?php foreach ($products as $product): 
                                $is_low_stock = $product['kolicina_na_stanju'] < 5;
                                $stock_class = $is_low_stock ? 'border-error/50 text-error' : 'border-outline-variant';
                                $img_path = getProductImage($product['id_proizvoda'], $product['naziv']);
                            ?>
                            <tr class="product-row hover:bg-surface-container-high transition-colors product-row" data-name="<?php echo strtolower(htmlspecialchars($product['naziv'])); ?>">
                                <td class="p-md font-label-bold text-on-surface-variant">#<?php echo $product['id_proizvoda']; ?> </td>
                                <td class="p-md">
                                    <div class="flex items-center gap-sm">
                                        <div class="w-12 h-12 bg-black rounded border border-outline-variant overflow-hidden flex-shrink-0">
                                            <?php if ($img_path): ?>
                                                <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($product['naziv']); ?>" class="w-full h-full object-cover" onerror="this.style.display='none'">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <span class="material-symbols-outlined text-on-surface-variant">fitness_center</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="font-label-bold text-on-surface"><?php echo htmlspecialchars($product['naziv']); ?></p>
                                            <p class="text-[12px] text-on-surface-variant"><?php echo htmlspecialchars($product['proizvodjac']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-md">
                                    <span class="bg-surface-container-highest px-sm py-xs rounded-full text-xs font-label-bold text-on-surface"><?php echo htmlspecialchars($product['naziv_kategorije']); ?></span>
                                </td>
                                <td class="p-md">
                                    <form method="POST" class="flex items-center gap-xs">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id_proizvoda']; ?>">
                                        <input name="quantity" value="<?php echo $product['kolicina_na_stanju']; ?>" class="w-16 bg-black border <?php echo $stock_class; ?> p-xs text-center text-sm rounded outline-none focus:border-primary-fixed" type="number" min="0"/>
                                        <button type="submit" name="update_stock" class="p-xs bg-outline-variant hover:bg-primary-fixed hover:text-on-primary-fixed rounded transition-colors" title="Ažuriraj">
                                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                                        </button>
                                    </form>
                                    <?php if ($is_low_stock): ?>
                                        <p class="text-[10px] text-error mt-xs font-bold uppercase tracking-tighter">⚠️ Niske zalihe!</p>
                                    <?php endif; ?>
                                </td>
                                <td class="p-md text-on-surface-variant"><?php echo $product['prodano']; ?></td>
                                <td class="p-md">
                                    <?php if ($product['prodano'] == 0): ?>
                                        <a href="?delete=<?php echo $product['id_proizvoda']; ?>" class="text-error hover:bg-error/10 p-xs rounded transition-colors inline-block" onclick="return confirm('Sigurno želite obrisati ovaj proizvod?')">
                                            <span class="material-symbols-outlined">delete</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-500 cursor-not-allowed p-xs inline-block" title="Ne može se obrisati - proizvod je naručen">
                                            <span class="material-symbols-outlined">block</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-md border-t border-outline-variant bg-surface-container-low flex flex-col md:flex-row justify-between items-center gap-md">
                    <p class="text-sm text-on-surface-variant">Prikazano <?php echo $total_products; ?> od <?php echo $total_products; ?> artikala</p>
                    <div class="flex items-center gap-xs">
                        <button class="p-xs border border-outline-variant rounded hover:bg-surface-container-highest transition-colors">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <span class="px-md py-xs bg-primary-fixed text-on-primary-fixed font-bold rounded text-sm">1</span>
                        <button class="px-md py-xs hover:bg-surface-container-highest rounded text-sm transition-colors text-on-surface">2</button>
                        <button class="px-md py-xs hover:bg-surface-container-highest rounded text-sm transition-colors text-on-surface">3</button>
                        <button class="p-xs border border-outline-variant rounded hover:bg-surface-container-highest transition-colors">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        
    </div>
</main>

<!-- Footer -->
<footer class="w-full py-lg mt-xl bg-surface-container-lowest border-t border-outline-variant">
    <div class="flex flex-col md:flex-row justify-between items-center px-gutter max-w-container-max mx-auto gap-md">
        <div>
            <div class="font-headline-md text-headline-md font-bold text-on-surface">SUPP.SCIENCE</div>
            <p class="text-on-surface-variant font-body-md text-body-md mt-xs">© 2024 SUPP.SCIENCE. Sva prava pridržana.</p>
        </div>
        <nav class="flex gap-md">
            <a class="text-on-surface-variant font-body-md text-body-md hover:text-primary-fixed transition-colors" href="#">O nama</a>
            <a class="text-on-surface-variant font-body-md text-body-md hover:text-primary-fixed transition-colors" href="#">Dostava</a>
            <a class="text-on-surface-variant font-body-md text-body-md hover:text-primary-fixed transition-colors" href="#">Uslovi korištenja</a>
            <a class="text-on-surface-variant font-body-md text-body-md hover:text-primary-fixed transition-colors" href="#">Kontakt</a>
        </nav>
        <div class="flex gap-sm">
            <div class="w-10 h-10 rounded-full border border-outline-variant flex items-center justify-center hover:bg-primary-fixed hover:text-on-primary-fixed transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-sm">share</span>
            </div>
            <div class="w-10 h-10 rounded-full border border-outline-variant flex items-center justify-center hover:bg-primary-fixed hover:text-on-primary-fixed transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-sm">mail</span>
            </div>
        </div>
    </div>
</footer>

<script>
// Pretraga proizvoda
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchValue = this.value.toLowerCase();
    let rows = document.querySelectorAll('.product-row');
    
    rows.forEach(row => {
        let productName = row.getAttribute('data-name');
        if (productName && productName.includes(searchValue)) {
            row.style.display = '';
        } else if (productName && !productName.includes(searchValue)) {
            row.style.display = 'none';
        }
    });
});
</script>

</body>
</html>