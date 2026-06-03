<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/image_helper.php';
requireRole('admin');

// Provjeri da li je ID narudžbe proslijeđen
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$order_id = (int)$_GET['id'];

// Dohvati podatke o narudžbi
$stmt = $pdo->prepare("
    SELECT 
        n.id_narudzbe,
        n.datum,
        n.status_narudzbe,
        n.adresa_dostave,
        k.id_kupca,
        CONCAT(k.ime, ' ', k.prezime) as kupac_ime,
        k.email as kupac_email,
        k.adresa as kupac_adresa,
        r.id_racuna,
        r.status_placanja,
        r.datum_izdavanja,
        r.ukupan_iznos,
        z.id_zaposlenika,
        CONCAT(z.ime, ' ', z.prezime) as zaposlenik_ime,
        z.uloga as zaposlenik_uloga
    FROM narudzba n
    JOIN kupac k ON n.id_kupca = k.id_kupca
    LEFT JOIN racun r ON n.id_narudzbe = r.id_narudzbe
    LEFT JOIN zaposlenik z ON n.id_zaposlenika = z.id_zaposlenika
    WHERE n.id_narudzbe = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Narudžba nije pronađena.");
}

// Dohvati stavke narudžbe
$stmt = $pdo->prepare("
    SELECT 
        sn.id_proizvoda,
        p.naziv,
        sn.kolicina,
        sn.cijena,
        (sn.kolicina * sn.cijena) as ukupno,
        p.proizvodjac,
        k.naziv_kategorije
    FROM stavke_narudzbe sn
    JOIN proizvod p ON sn.id_proizvoda = p.id_proizvoda
    JOIN kategorija k ON p.id_kategorije = k.id_kategorije
    WHERE sn.id_narudzbe = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Dohvati sve zaposlenike za dodjelu
$stmt = $pdo->query("SELECT id_zaposlenika, ime, prezime, uloga, email FROM zaposlenik");
$employees = $stmt->fetchAll();

// Ukupan iznos
$total_amount = array_sum(array_column($items, 'ukupno'));

// Obrada POST zahtjeva
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $new_status = $_POST['status_narudzbe'];
        $stmt = $pdo->prepare("UPDATE narudzba SET status_narudzbe = ? WHERE id_narudzbe = ?");
        $stmt->execute([$new_status, $order_id]);
        header("Location: order_details.php?id=$order_id");
        exit();
    }
    
    if (isset($_POST['update_payment'])) {
        $new_payment = $_POST['status_placanja'];
        if ($order['id_racuna']) {
            $stmt = $pdo->prepare("UPDATE racun SET status_placanja = ? WHERE id_narudzbe = ?");
            $stmt->execute([$new_payment, $order_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO racun (id_narudzbe, status_placanja, ukupan_iznos) VALUES (?, ?, ?)");
            $stmt->execute([$order_id, $new_payment, $total_amount]);
        }
        header("Location: order_details.php?id=$order_id");
        exit();
    }
    
    if (isset($_POST['assign_employee'])) {
        $employee_id = $_POST['id_zaposlenika'] ?: null;
        $stmt = $pdo->prepare("UPDATE narudzba SET id_zaposlenika = ? WHERE id_narudzbe = ?");
        $stmt->execute([$employee_id, $order_id]);
        header("Location: order_details.php?id=$order_id");
        exit();
    }
    
    if (isset($_POST['cancel_order'])) {
        $stmt = $pdo->prepare("UPDATE narudzba SET status_narudzbe = 'Otkazano' WHERE id_narudzbe = ?");
        $stmt->execute([$order_id]);
        header("Location: order_details.php?id=$order_id");
        exit();
    }
}

// Status badge helper function
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Isporuceno': return 'bg-green-500/20 text-green-400 border-green-500/30';
        case 'Potvrdjeno': return 'bg-blue-500/20 text-blue-400 border-blue-500/30';
        case 'Na cekanju': return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
        case 'Otkazano': return 'bg-red-500/20 text-red-400 border-red-500/30';
        default: return 'bg-gray-500/20 text-gray-400 border-gray-500/30';
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="bs">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;800;900&amp;family=Inter:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "error": "#ffb4ab",
                        "surface": "#111508",
                        "tertiary-container": "#e0e2eb",
                        "surface-container": "#1e2113",
                        "secondary-fixed-dim": "#c8c6c5",
                        "on-tertiary-fixed-variant": "#44474e",
                        "on-secondary-fixed": "#1c1b1b",
                        "on-primary-container": "#556d00",
                        "surface-dim": "#111508",
                        "on-tertiary-fixed": "#191c22",
                        "tertiary-fixed": "#e0e2eb",
                        "secondary-container": "#4a4949",
                        "primary-fixed-dim": "#abd600",
                        "inverse-on-surface": "#2f3223",
                        "on-error": "#690005",
                        "background": "#111508",
                        "on-surface-variant": "#c4c9ac",
                        "on-tertiary": "#2d3037",
                        "on-primary-fixed-variant": "#3c4d00",
                        "surface-tint": "#abd600",
                        "on-secondary-fixed-variant": "#474646",
                        "secondary-fixed": "#e5e2e1",
                        "surface-container-high": "#282b1d",
                        "error-container": "#93000a",
                        "surface-variant": "#333627",
                        "surface-container-lowest": "#0c0f04",
                        "surface-container-highest": "#333627",
                        "outline-variant": "#444933",
                        "on-tertiary-container": "#62646c",
                        "on-primary-fixed": "#161e00",
                        "tertiary": "#ffffff",
                        "primary-container": "#c3f400",
                        "on-background": "#e2e4cf",
                        "primary-fixed": "#c3f400",
                        "on-surface": "#e2e4cf",
                        "inverse-surface": "#e2e4cf",
                        "inverse-primary": "#506600",
                        "tertiary-fixed-dim": "#c4c6cf",
                        "on-secondary": "#313030",
                        "primary": "#ffffff",
                        "secondary": "#c8c6c5",
                        "outline": "#8e9379",
                        "on-secondary-container": "#bab8b7",
                        "surface-container-low": "#1a1d10",
                        "on-primary": "#283500",
                        "surface-bright": "#373b2c",
                        "on-error-container": "#ffdad6"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    spacing: {
                        xl: "80px",
                        md: "24px",
                        base: "8px",
                        sm: "12px",
                        container_max: "1280px",
                        xs: "4px",
                        lg: "48px",
                        gutter: "24px"
                    },
                    fontFamily: {
                        "headline-md": ["Montserrat"],
                        "display-lg": ["Montserrat"],
                        "body-md": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-xl-mobile": ["Montserrat"],
                        "display-lg-mobile": ["Montserrat"],
                        "headline-xl": ["Montserrat"],
                        "label-bold": ["Inter"]
                    },
                    fontSize: {
                        "headline-md": ["24px", {"lineHeight": "1.3", "fontWeight": "700"}],
                        "display-lg": ["64px", {"lineHeight": "1.1", "letterSpacing": "-0.04em", "fontWeight": "900"}],
                        "body-md": ["16px", {"lineHeight": "1.5", "fontWeight": "400"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "headline-xl-mobile": ["32px", {"lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "800"}],
                        "display-lg-mobile": ["40px", {"lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "900"}],
                        "headline-xl": ["40px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "800"}],
                        "label-bold": ["14px", {"lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "700"}]
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
        .elevation-1 {
            box-shadow: 0 1px 2px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #444933; border-radius: 10px; }
    </style>
</head>
<body class="bg-background text-on-background selection:bg-primary-fixed selection:text-on-primary-fixed min-h-screen">

<header class="fixed top-0 w-full z-50 bg-background/95 backdrop-blur-md border-b border-outline-variant shadow-md">
    <div class="flex justify-between items-center h-20 px-gutter max-w-container_max mx-auto">
        <a href="dashboard.php" class="font-display-lg-mobile text-display-lg-mobile font-black text-primary-fixed tracking-tighter">
            SUPP.SCIENCE
        </a>
        <nav class="hidden md:flex gap-md">
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="../index.php">Početna</a>
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="#">Moje narudžbe</a>
            <a class="text-primary-fixed font-label-bold border-b-2 border-primary-fixed pb-1" href="dashboard.php">Admin Panel</a>
        </nav>
        <div class="flex items-center gap-sm">
            <span class="font-label-bold text-on-surface-variant hidden md:inline"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="../logout.php" class="flex items-center gap-xs px-sm py-2 rounded-lg bg-surface-container-highest hover:bg-surface-variant transition-all active:scale-95 text-on-surface">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-label-bold">Logout</span>
            </a>
        </div>
    </div>
</header>

<main class="pt-32 pb-xl px-gutter max-w-container_max mx-auto">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-md mb-lg">
        <div>
            <nav class="flex items-center gap-xs mb-sm">
                <a class="text-on-surface-variant hover:text-primary-fixed flex items-center gap-1 transition-colors" href="dashboard.php">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    <span class="font-label-bold text-xs">NAZAD NA DASHBOARD</span>
                </a>
            </nav>
            <h1 class="font-headline-xl text-headline-xl text-primary-fixed tracking-tight">Detalji narudžbe #<?php echo $order_id; ?></h1>
            <p class="text-on-surface-variant font-body-md mt-1 italic">Kreirano: <?php echo date('d.m.Y H:i:s', strtotime($order['datum'])); ?></p>
        </div>
        <div class="flex gap-sm">
            <?php if ($order['status_narudzbe'] != 'Otkazano' && $order['status_narudzbe'] != 'Isporuceno'): ?>
            <form method="POST" onsubmit="return confirm('Sigurno želite otkazati ovu narudžbu?')">
                <button type="submit" name="cancel_order" class="px-md py-3 bg-error text-on-error font-label-bold rounded-lg hover:brightness-110 active:scale-95 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined">cancel</span>
                    OTKAŽI NARUDŽBU
                </button>
            </form>
            <?php endif; ?>
            <button onclick="window.print()" class="px-md py-3 bg-primary-fixed text-on-primary-fixed font-label-bold rounded-lg hover:brightness-110 active:scale-95 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined">print</span>
                ŠTAMPAJ RAČUN
            </button>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-md">
        
        <!-- Left Column -->
        <div class="md:col-span-6 flex flex-col gap-md">
            <!-- Order Info Card -->
            <div class="bg-surface-container-low elevation-1 rounded-xl p-md">
                <div class="flex items-center gap-2 mb-md text-primary-fixed">
                    <span class="material-symbols-outlined">info</span>
                    <h2 class="font-headline-md text-headline-md tracking-tight">Informacije o narudžbi</h2>
                </div>
                <div class="space-y-md">
                    <div>
                        <label class="block font-label-bold text-on-surface-variant mb-2">STATUS NARUDŽBE</label>
                        <form method="POST" class="relative group">
                            <select name="status_narudzbe" onchange="this.form.submit()" class="w-full bg-surface-container-highest border border-outline-variant text-on-surface py-3 px-4 rounded-lg focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed appearance-none transition-all">
                                <option value="Na cekanju" <?php echo $order['status_narudzbe'] == 'Na cekanju' ? 'selected' : ''; ?>>Na čekanju</option>
                                <option value="Potvrdjeno" <?php echo $order['status_narudzbe'] == 'Potvrdjeno' ? 'selected' : ''; ?>>Potvrđeno</option>
                                <option value="Isporuceno" <?php echo $order['status_narudzbe'] == 'Isporuceno' ? 'selected' : ''; ?>>Isporučeno</option>
                                <option value="Otkazano" <?php echo $order['status_narudzbe'] == 'Otkazano' ? 'selected' : ''; ?>>Otkazano</option>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </div>
                    <div>
                        <p class="font-label-bold text-on-surface-variant mb-1">ADRESA DOSTAVE</p>
                        <p class="font-body-lg text-on-surface"><?php echo htmlspecialchars($order['adresa_dostave'] ?? $order['kupac_adresa']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Invoice Card -->
            <div class="bg-surface-container-low elevation-1 rounded-xl p-md">
                <div class="flex items-center gap-2 mb-md text-primary-fixed">
                    <span class="material-symbols-outlined">receipt_long</span>
                    <h2 class="font-headline-md text-headline-md tracking-tight">Račun</h2>
                </div>
                <div class="grid grid-cols-2 gap-md">
                    <div>
                        <p class="font-label-bold text-on-surface-variant mb-1">BROJ RAČUNA</p>
                        <p class="font-body-md text-on-surface"><?php echo $order['id_racuna'] ?? 'Nije kreiran'; ?></p>
                    </div>
                    <div>
                        <p class="font-label-bold text-on-surface-variant mb-1">UKUPAN IZNOS</p>
                        <p class="font-headline-md text-headline-md text-primary-fixed"><?php echo number_format($order['ukupan_iznos'] ?? $total_amount, 2); ?> KM</p>
                    </div>
                    <div>
                        <p class="font-label-bold text-on-surface-variant mb-1">DATUM IZDAVANJA</p>
                        <p class="font-body-md text-on-surface"><?php echo $order['datum_izdavanja'] ? date('d.m.Y H:i', strtotime($order['datum_izdavanja'])) : 'Nije izdat'; ?></p>
                    </div>
                    <div>
                        <p class="font-label-bold text-on-surface-variant mb-1">STATUS PLAĆANJA</p>
                        <form method="POST">
                            <select name="status_placanja" onchange="this.form.submit()" class="w-full bg-surface-container-highest border border-outline-variant text-on-surface py-2 px-3 rounded-lg focus:border-primary-fixed transition-all text-sm">
                                <option value="Na cekanju" <?php echo ($order['status_placanja'] ?? '') == 'Na cekanju' ? 'selected' : ''; ?>>Na čekanju</option>
                                <option value="Potvrdjeno" <?php echo ($order['status_placanja'] ?? '') == 'Potvrdjeno' ? 'selected' : ''; ?>>Plaćeno</option>
                                <option value="Odbijeno" <?php echo ($order['status_placanja'] ?? '') == 'Odbijeno' ? 'selected' : ''; ?>>Odbijeno</option>
                            </select>
                            <input type="hidden" name="update_payment" value="1">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="md:col-span-6 flex flex-col gap-md">
            <!-- Customer Card -->
            <div class="bg-surface-container-low elevation-1 rounded-xl p-md">
                <div class="flex items-center gap-2 mb-md text-primary-fixed">
                    <span class="material-symbols-outlined">person</span>
                    <h2 class="font-headline-md text-headline-md tracking-tight">Kupac</h2>
                </div>
                <div class="flex items-center gap-md">
                    <div class="w-16 h-16 rounded-full bg-surface-container-highest border-2 border-primary-fixed flex items-center justify-center overflow-hidden">
                        <span class="material-symbols-outlined text-4xl text-on-surface-variant">account_circle</span>
                    </div>
                    <div>
                        <h3 class="font-headline-md text-on-surface"><?php echo htmlspecialchars($order['kupac_ime']); ?></h3>
                        <p class="font-body-md text-primary-fixed-dim"><?php echo htmlspecialchars($order['kupac_email']); ?></p>
                    </div>
                </div>
                <div class="mt-md pt-md border-t border-outline-variant">
                    <p class="font-label-bold text-on-surface-variant mb-1">ADRESA</p>
                    <p class="font-body-md text-on-surface"><?php echo htmlspecialchars($order['kupac_adresa']); ?></p>
                </div>
            </div>

            <!-- Employee Card -->
            <div class="bg-surface-container-low elevation-1 rounded-xl p-md flex-grow">
                <div class="flex items-center gap-2 mb-md text-primary-fixed">
                    <span class="material-symbols-outlined">badge</span>
                    <h2 class="font-headline-md text-headline-md tracking-tight">Zaposlenik</h2>
                </div>
                <p class="font-label-bold text-on-surface-variant mb-2">TRENUTNI</p>
                <div class="bg-surface-container-highest p-3 rounded-lg border border-outline-variant mb-md">
                    <?php if ($order['zaposlenik_ime']): ?>
                        <p class="text-on-surface"><?php echo htmlspecialchars($order['zaposlenik_ime']); ?></p>
                        <p class="text-on-surface-variant text-sm"><?php echo htmlspecialchars($order['zaposlenik_uloga']); ?></p>
                    <?php else: ?>
                        <p class="text-on-surface-variant italic">-- Nije dodijeljen --</p>
                    <?php endif; ?>
                </div>
                <form method="POST" class="space-y-sm">
                    <label class="block font-label-bold text-on-surface-variant">DODIJELI NOVOG</label>
                    <div class="flex gap-sm">
                        <select name="id_zaposlenika" class="flex-grow bg-surface-container-highest border border-outline-variant text-on-surface py-3 px-4 rounded-lg focus:border-primary-fixed transition-all">
                            <option value="">-- Izaberi zaposlenika --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['id_zaposlenika']; ?>" <?php echo ($order['id_zaposlenika'] ?? '') == $emp['id_zaposlenika'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($emp['ime'] . ' ' . $emp['prezime'] . ' (' . $emp['uloga'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="assign_employee" class="bg-primary-fixed text-on-primary-fixed px-md py-3 rounded-lg font-label-bold hover:brightness-110 active:scale-95 transition-all">
                            DODIJELI
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Order Items Table -->
        <div class="md:col-span-12">
            <div class="bg-surface-container-low elevation-1 rounded-xl overflow-hidden">
                <div class="p-md border-b border-outline-variant flex items-center gap-2 text-primary-fixed">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <h2 class="font-headline-md text-headline-md tracking-tight">Stavke narudžbe</h2>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left">
                        <thead class="bg-surface-container-highest text-on-surface-variant font-label-bold uppercase text-xs tracking-widest">
                            <tr>
                                <th class="px-md py-4">ID</th>
                                <th class="px-md py-4">Proizvod</th>
                                <th class="px-md py-4">Proizvođač</th>
                                <th class="px-md py-4 text-right">Cijena</th>
                                <th class="px-md py-4 text-center">Količina</th>
                                <th class="px-md py-4 text-right">Ukupno</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant font-body-md">
                            <?php foreach ($items as $item): ?>
                            <tr class="hover:bg-surface-container transition-colors group">
                                <td class="px-md py-5 text-on-surface-variant"><?php echo $item['id_proizvoda']; ?></td>
                                <td class="px-md py-5 font-bold text-on-surface">
                                    <div class="flex items-center gap-3">
                                       <div class="w-12 h-12 rounded bg-surface border border-outline-variant p-1 flex items-center justify-center overflow-hidden">
                                            <?php 
                                            $img_path = getProductImage($item['id_proizvoda'], $item['naziv']);
                                            ?>
                                            <img src="<?php echo $img_path; ?>" 
                                                alt="<?php echo htmlspecialchars($item['naziv']); ?>" 
                                                class="w-full h-full object-contain">
                                        </div>
                                        <?php echo htmlspecialchars($item['naziv']); ?>
                                    </div>
                                </td>
                                <td class="px-md py-5 text-on-surface-variant"><?php echo htmlspecialchars($item['proizvodjac']); ?></td>
                                <td class="px-md py-5 text-right"><?php echo number_format($item['cijena'], 2); ?> KM</td>
                                <td class="px-md py-5 text-center">
                                    <span class="bg-surface-container-highest px-3 py-1 rounded-full font-label-bold"><?php echo $item['kolicina']; ?></span>
                                </td>
                                <td class="px-md py-5 text-right font-black text-primary-fixed"><?php echo number_format($item['ukupno'], 2); ?> KM</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-surface-container-lowest">
                            <tr>
                                <td class="px-md py-6 text-right font-label-bold text-on-surface-variant" colspan="5">SVEUKUPNO ZA NAPLATU:</td>
                                <td class="px-md py-6 text-right font-headline-md text-headline-md text-primary-fixed"><?php echo number_format($total_amount, 2); ?> KM</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Actions -->
    <div class="mt-lg flex justify-between items-center">
        <a href="dashboard.php" class="px-lg py-3 bg-surface-container-highest border border-outline-variant text-on-surface font-label-bold rounded-lg hover:bg-surface-variant active:scale-95 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">arrow_back_ios_new</span>
            NAZAD NA SVE NARUDŽBE
        </a>
        <div class="flex items-center gap-md">
            <p class="font-label-bold text-on-surface-variant hidden lg:block">Status: <span class="text-primary-fixed"><?php echo $order['status_narudzbe']; ?></span></p>
            <a href="dashboard.php" class="px-lg py-3 bg-primary-fixed text-on-primary-fixed font-label-bold rounded-lg hover:brightness-110 active:scale-95 shadow-lg transition-all flex items-center gap-2">
                SAČUVAJ IZMJENE
                <span class="material-symbols-outlined">save</span>
            </a>
        </div>
    </div>

</main>

<!-- Footer -->
<footer class="w-full py-lg mt-xl bg-surface-container-lowest border-t border-outline-variant">
    <div class="flex flex-col md:flex-row justify-between items-center px-gutter max-w-container_max mx-auto gap-md">
        <div class="font-headline-md text-headline-md font-bold text-on-surface">SUPP.SCIENCE</div>
        <div class="flex flex-wrap justify-center gap-md">
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">O nama</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">Dostava</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">Uslovi korištenja</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">Kontakt</a>
        </div>
        <p class="text-on-surface-variant font-body-md opacity-60">© 2024 SUPP.SCIENCE. Sva prava pridržana.</p>
    </div>
</footer>

</body>
</html> 