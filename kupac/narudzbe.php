<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('kupac');

// Dohvati sve narudžbe kupca
$stmt = $pdo->prepare("
    SELECT 
        n.id_narudzbe,
        n.datum,
        n.status_narudzbe,
        n.adresa_dostave,
        r.status_placanja,
        r.ukupan_iznos,
        r.datum_izdavanja
    FROM narudzba n
    LEFT JOIN racun r ON n.id_narudzbe = r.id_narudzbe
    WHERE n.id_kupca = ?
    ORDER BY n.datum DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Status badge helper function
function getStatusBadge($status) {
    switch ($status) {
        case 'Isporuceno':
            return ['bg-green-500/20 text-green-400 border-green-500/30', 'check_circle', 'Isporučeno'];
        case 'Potvrdjeno':
            return ['bg-blue-500/20 text-blue-400 border-blue-500/30', 'verified', 'Potvrđeno'];
        case 'Na cekanju':
            return ['bg-yellow-500/20 text-yellow-400 border-yellow-500/30', 'pending', 'Na čekanju'];
        case 'Otkazano':
            return ['bg-red-500/20 text-red-400 border-red-500/30', 'cancel', 'Otkazano'];
        case 'Odbijeno':
            return ['bg-red-500/20 text-red-400 border-red-500/30', 'block', 'Odbijeno'];
        default:
            return ['bg-gray-500/20 text-gray-400 border-gray-500/30', 'help', $status];
    }
}

function getPaymentBadge($status) {
    switch ($status) {
        case 'Potvrdjeno':
            return ['bg-green-500/20 text-green-400 border-green-500/30', 'paid', 'Plaćeno'];
        case 'Na cekanju':
            return ['bg-yellow-500/20 text-yellow-400 border-yellow-500/30', 'pending', 'Na čekanju'];
        case 'Odbijeno':
            return ['bg-red-500/20 text-red-400 border-red-500/30', 'block', 'Odbijeno'];
        default:
            return ['bg-gray-500/20 text-gray-400 border-gray-500/30', 'help', 'Nije kreiran'];
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="bs">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Moje Narudžbe - SUPP.SCIENCE</title>
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
        .order-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .order-card:hover {
            transform: translateY(-2px);
            border-color: #c3f400;
        }
        .details-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out;
        }
        .details-content.open {
            max-height: 1000px;
            transition: max-height 0.6s ease-in;
        }
        .rotate-icon {
            transition: transform 0.3s ease;
        }
        .rotate-icon.open {
            transform: rotate(180deg);
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
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="dashboard.php">Moja korpa</a>
            <a class="text-primary-fixed font-label-bold border-b-2 border-primary-fixed pb-1" href="narudzbe.php">Moje narudžbe</a>
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
        <h1 class="font-headline-xl text-headline-xl text-primary mb-xs">Moje Narudžbe</h1>
        <div class="h-1.5 w-24 bg-primary-fixed rounded-full"></div>
        <p class="text-on-surface-variant text-body-lg mt-sm">Pregledajte istoriju vaših narudžbi</p>
    </div>

    <!-- Success Message -->
    <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-500/20 border border-green-500/30 rounded-xl p-md mb-lg flex items-center gap-sm" style="padding: 24px;">
        <span class="material-symbols-outlined text-green-400">check_circle</span>
        <p class="text-green-400">Vaša narudžba je uspješno kreirana! Hvala na povjerenju.</p>
    </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
    <!-- Empty State -->
    <div class="bg-surface-container-high border border-outline-variant rounded-xl p-xl text-center" style="padding: 80px;">
        <span class="material-symbols-outlined text-7xl text-on-surface-variant mb-md">receipt_long</span>
        <h3 class="font-headline-md text-primary mb-xs">Još nema narudžbi</h3>
        <p class="text-on-surface-variant mb-lg">Vaša istorija narudžbi je prazna. Započnite svoju prvu kupovinu!</p>
        <a href="../index.php" class="inline-flex items-center gap-sm bg-primary-fixed text-on-primary-fixed font-label-bold px-lg py-md rounded-lg hover:brightness-110 transition-all">
            <span class="material-symbols-outlined">shopping_bag</span>
            ZAPOČNITE KUPOVINU
        </a>
    </div>
    <?php else: ?>
    <!-- Orders List -->
    <div class="space-y-md">
        <?php foreach ($orders as $index => $order): 
            $orderId = $order['id_narudzbe'];
            
            // Dohvati stavke narudžbe
            $stmt = $pdo->prepare("
                SELECT 
                    p.id_proizvoda,
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
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll();
            
            $statusBadge = getStatusBadge($order['status_narudzbe']);
            $paymentBadge = getPaymentBadge($order['status_placanja']);
        ?>
        <div class="order-card bg-surface-container-high border border-outline-variant rounded-xl overflow-hidden transition-all duration-300">
            <!-- Order Header -->
            <div class="p-md cursor-pointer flex flex-wrap items-center justify-between gap-md" style="padding: 24px;" onclick="toggleDetails(<?php echo $index; ?>)">
                <div class="flex items-center gap-md">
                    <span class="material-symbols-outlined text-primary-fixed">receipt</span>
                    <div>
                        <p class="font-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Narudžba #<?php echo $orderId; ?></p>
                        <p class="text-body-md text-on-surface-variant"><?php echo date('d.m.Y H:i', strtotime($order['datum'])); ?></p>
                    </div>
                </div>
                
                <div class="flex items-center gap-md">
                    <!-- Status Badges -->
                    <div class="flex items-center gap-sm">
                        <span class="inline-flex items-center gap-xs px-sm py-xs rounded-full border text-xs font-label-bold <?php echo $statusBadge[0]; ?>">
                            <span class="material-symbols-outlined text-sm"><?php echo $statusBadge[1]; ?></span>
                            <?php echo $statusBadge[2]; ?>
                        </span>
                        <span class="inline-flex items-center gap-xs px-sm py-xs rounded-full border text-xs font-label-bold <?php echo $paymentBadge[0]; ?>">
                            <span class="material-symbols-outlined text-sm"><?php echo $paymentBadge[1]; ?></span>
                            <?php echo $paymentBadge[2]; ?>
                        </span>
                    </div>
                    
                    <!-- Total Amount -->
                    <div class="text-right">
                        <p class="font-headline-md text-primary-fixed"><?php echo number_format($order['ukupan_iznos'] ?? 0, 2); ?> KM</p>
                        <p class="text-on-surface-variant text-body-md text-sm"><?php echo count($items); ?> proizvoda</p>
                    </div>
                    
                    <!-- Expand Icon -->
                    <span class="material-symbols-outlined text-on-surface-variant rotate-icon" id="icon-<?php echo $index; ?>">expand_more</span>
                </div>
            </div>
            
            <!-- Order Details (Collapsible) -->
            <div id="details-<?php echo $index; ?>" class="details-content border-t border-outline-variant bg-surface-container">
                <div class="p-md" style="padding: 24px;">
                    <!-- Address Info -->
                    <div class="mb-md">
                        <p class="font-label-bold text-on-surface-variant text-xs uppercase tracking-wider mb-xs">ADRESA DOSTAVE</p>
                        <p class="text-body-md text-on-surface">
                            <?php echo htmlspecialchars($order['adresa_dostave'] ?? 'Adresa iz profila'); ?>
                        </p>
                    </div>
                    
                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-outline-variant">
                                    <th class="text-left py-sm font-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Proizvod</th>
                                    <th class="text-left py-sm font-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Kategorija</th>
                                    <th class="text-center py-sm font-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Količina</th>
                                    <th class="text-right py-sm font-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Cijena</th>
                                    <th class="text-right py-sm font-label-bold text-on-surface-variant text-xs uppercase tracking-wider">Ukupno</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr class="border-b border-outline-variant/50">
                                    <td class="py-sm">
                                        <p class="font-headline-md text-headline-md text-primary text-sm"><?php echo htmlspecialchars($item['naziv']); ?></p>
                                        <p class="text-on-surface-variant text-body-md text-xs"><?php echo htmlspecialchars($item['proizvodjac']); ?></p>
                                    </td>
                                    <td class="py-sm text-on-surface-variant text-body-md"><?php echo htmlspecialchars($item['naziv_kategorije']); ?></td>
                                    <td class="py-sm text-center text-on-surface text-body-md"><?php echo $item['kolicina']; ?>x</td>
                                    <td class="py-sm text-right text-on-surface-variant text-body-md"><?php echo number_format($item['cijena'], 2); ?> KM</td>
                                    <td class="py-sm text-right text-on-surface text-body-md font-bold"><?php echo number_format($item['ukupno'], 2); ?> KM</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="pt-md text-right font-headline-md text-primary">Ukupno:</td>
                                    <td class="pt-md text-right font-headline-md text-primary-fixed"><?php echo number_format($order['ukupan_iznos'] ?? 0, 2); ?> KM</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Invoice Info if available -->
                    <?php if ($order['datum_izdavanja']): ?>
                    <div class="mt-md pt-md border-t border-outline-variant">
                        <p class="text-on-surface-variant text-body-md text-sm">
                            <span class="font-label-bold">Račun izdat:</span> <?php echo date('d.m.Y H:i', strtotime($order['datum_izdavanja'])); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Continue Shopping Button -->
    <div class="mt-xl text-center">
        <a href="../index.php" class="inline-flex items-center gap-sm text-on-surface-variant hover:text-primary-fixed transition-colors text-body-md">
            <span class="material-symbols-outlined">arrow_back</span>
            Nastavi kupovinu
        </a>
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

<script>
function toggleDetails(index) {
    const details = document.getElementById(`details-${index}`);
    const icon = document.getElementById(`icon-${index}`);
    
    details.classList.toggle('open');
    icon.classList.toggle('open');
}
</script>

</body>
</html>