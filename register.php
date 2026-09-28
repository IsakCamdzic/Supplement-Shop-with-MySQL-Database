<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Ako je već prijavljen, preusmjeri
if (isLoggedIn()) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['role'] == 'skladistar') {
        header('Location: skladistar/dashboard.php');
    } elseif ($_SESSION['role'] == 'dostavljac') {
        header('Location: dostavljac/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ime = trim($_POST['ime']);
    $prezime = trim($_POST['prezime']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $adresa = trim($_POST['adresa']);
    $terms = isset($_POST['terms']);
    
    // Validacija
    if (empty($ime) || empty($prezime) || empty($email) || empty($password)) {
        $error = 'Sva polja su obavezna!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Unesite ispravnu email adresu!';
    } elseif (strlen($password) < 4) {
        $error = 'Lozinka mora imati najmanje 4 karaktera!';
    } elseif (!$terms) {
        $error = 'Morate prihvatiti uslove korištenja!';
    } else {
        // Provjeri da li email već postoji
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM kupac WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email adresa je već registrovana!';
        } else {
            // Ubaci novog kupca (u demo verziji ne hash-ujemo lozinku)
            $stmt = $pdo->prepare("
                INSERT INTO kupac (ime, prezime, email, adresa) 
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt->execute([$ime, $prezime, $email, $adresa])) {
                $success = 'Registracija uspješna! Sada se možete prijaviti.';
                // Opciono: automatski prijavi korisnika
                // header('Location: login.php');
                // exit();
            } else {
                $error = 'Greška pri registraciji. Pokušajte ponovo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="bs">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&amp;family=Inter:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "tertiary": "#ffffff",
                        "secondary": "#c8c6c5",
                        "on-background": "#e2e4cf",
                        "tertiary-fixed": "#e0e2eb",
                        "tertiary-container": "#e0e2eb",
                        "on-tertiary-fixed-variant": "#44474e",
                        "on-surface-variant": "#c4c9ac",
                        "tertiary-fixed-dim": "#c4c6cf",
                        "outline": "#8e9379",
                        "surface-container-highest": "#333627",
                        "surface-container": "#1e2113",
                        "on-tertiary": "#2d3037",
                        "secondary-fixed-dim": "#c8c6c5",
                        "on-primary-fixed-variant": "#3c4d00",
                        "error": "#ffb4ab",
                        "on-secondary-container": "#bab8b7",
                        "surface-dim": "#111508",
                        "primary-container": "#c3f400",
                        "on-secondary-fixed-variant": "#474646",
                        "background": "#111508",
                        "surface-container-high": "#282b1d",
                        "surface-bright": "#373b2c",
                        "primary-fixed-dim": "#abd600",
                        "on-surface": "#e2e4cf",
                        "primary": "#ffffff",
                        "inverse-surface": "#e2e4cf",
                        "on-secondary": "#313030",
                        "inverse-on-surface": "#2f3223",
                        "primary-fixed": "#c3f400",
                        "on-tertiary-fixed": "#191c22",
                        "surface": "#111508",
                        "on-error-container": "#ffdad6",
                        "surface-tint": "#abd600",
                        "surface-container-low": "#1a1d10",
                        "secondary-container": "#4a4949",
                        "on-tertiary-container": "#62646c",
                        "on-primary-container": "#556d00",
                        "surface-container-lowest": "#0c0f04",
                        "outline-variant": "#444933",
                        "on-error": "#690005",
                        "error-container": "#93000a",
                        "surface-variant": "#333627",
                        "on-secondary-fixed": "#1c1b1b",
                        "inverse-primary": "#506600",
                        "on-primary-fixed": "#161e00",
                        "secondary-fixed": "#e5e2e1",
                        "on-primary": "#283500"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    spacing: {
                        xs: "4px",
                        md: "24px",
                        lg: "48px",
                        xl: "80px",
                        base: "8px",
                        gutter: "24px",
                        sm: "12px",
                        "container-max": "1280px"
                    },
                    fontFamily: {
                        "headline-xl": ["Montserrat"],
                        "headline-xl-mobile": ["Montserrat"],
                        "display-lg": ["Montserrat"],
                        "label-bold": ["Inter"],
                        "display-lg-mobile": ["Montserrat"],
                        "body-md": ["Inter"],
                        "headline-md": ["Montserrat"],
                        "body-lg": ["Inter"]
                    },
                    fontSize: {
                        "headline-xl": ["40px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "800"}],
                        "headline-xl-mobile": ["32px", {"lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "800"}],
                        "display-lg": ["64px", {"lineHeight": "1.1", "letterSpacing": "-0.04em", "fontWeight": "900"}],
                        "label-bold": ["14px", {"lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "display-lg-mobile": ["40px", {"lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "900"}],
                        "body-md": ["16px", {"lineHeight": "1.5", "fontWeight": "400"}],
                        "headline-md": ["24px", {"lineHeight": "1.3", "fontWeight": "700"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            background-color: #111508;
            color: #e2e4cf;
        }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col">

<!-- TopNavBar -->
<header class="fixed top-0 w-full z-50 bg-background/95 backdrop-blur-md border-b border-outline-variant shadow-md h-20">
    <div class="flex justify-between items-center h-20 px-gutter max-w-container-max mx-auto">
        <div class="font-display-lg-mobile text-display-lg-mobile font-black text-primary-fixed tracking-tighter">
            <a href="index.php" class="hover:opacity-80 transition-opacity">SUPP.SCIENCE</a>
        </div>
        <nav class="hidden md:flex items-center gap-md">
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="index.php">Početna</a>
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="kupac/dashboard.php">Moja korpa</a>
            <a class="text-on-surface-variant font-label-bold hover:text-primary-fixed transition-colors" href="kupac/narudzbe.php">Moje narudžbe</a>
        </nav>
        <div class="flex items-center gap-sm">
            <div class="hidden lg:flex items-center bg-surface-container-low border border-outline-variant px-sm py-xs rounded">
                <span class="material-symbols-outlined text-on-surface-variant mr-xs">search</span>
                <input class="bg-transparent border-none focus:ring-0 text-body-md w-40 text-on-surface" placeholder="Traži..." type="text"/>
            </div>
            <a href="login.php" class="text-primary-fixed font-label-bold hover:bg-surface-container-highest transition-all duration-200 px-sm py-xs rounded active:scale-95">
                Prijava
            </a>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="flex-grow pt-32 pb-xl flex items-center justify-center px-gutter relative overflow-hidden">
    <!-- Abstract Background Decorative Elements -->
    <div class="absolute top-1/4 -left-20 w-80 h-80 bg-primary-fixed/5 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-1/4 -right-20 w-80 h-80 bg-primary-fixed/5 rounded-full blur-[100px]"></div>
    
    <div class="w-full max-w-[560px] z-10">
        <!-- Registration Card -->
        <div class="bg-[#2A2D34] border border-white/5 rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden">
            <div class="p-lg md:p-xl">
                <div class="text-center mb-lg">
                    <h1 class="font-headline-xl text-headline-xl text-primary-fixed mb-xs uppercase tracking-tighter">Pridruži se Eliti</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant">Kreiraj nalog i optimizuj svoje performanse uz naučno podržane suplemente.</p>
                </div>
                
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="bg-error-container border border-error rounded-lg p-md mb-md flex items-center gap-sm">
                        <span class="material-symbols-outlined text-error">error</span>
                        <p class="text-on-error-container text-body-md"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Success Message -->
                <?php if ($success): ?>
                    <div class="bg-green-500/20 border border-green-500 rounded-lg p-md mb-md flex items-center gap-sm">
                        <span class="material-symbols-outlined text-green-400">check_circle</span>
                        <p class="text-green-400"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                    <div class="text-center mt-4">
                        <a href="login.php" class="text-primary-fixed font-label-bold hover:underline">Idi na prijavu →</a>
                    </div>
                <?php endif; ?>
                
                <?php if (!$success): ?>
                <form method="POST" class="space-y-md">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                        <div class="space-y-xs">
                            <label class="font-label-bold text-label-bold text-secondary uppercase tracking-widest">Ime</label>
                            <input name="ime" value="<?php echo isset($_POST['ime']) ? htmlspecialchars($_POST['ime']) : ''; ?>" class="w-full bg-black border border-[#2A2D34] focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed/20 rounded px-md py-sm text-on-surface transition-all" placeholder="Unesite ime" type="text" required/>
                        </div>
                        <div class="space-y-xs">
                            <label class="font-label-bold text-label-bold text-secondary uppercase tracking-widest">Prezime</label>
                            <input name="prezime" value="<?php echo isset($_POST['prezime']) ? htmlspecialchars($_POST['prezime']) : ''; ?>" class="w-full bg-black border border-[#2A2D34] focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed/20 rounded px-md py-sm text-on-surface transition-all" placeholder="Unesite prezime" type="text" required/>
                        </div>
                    </div>
                    
                    <div class="space-y-xs">
                        <label class="font-label-bold text-label-bold text-secondary uppercase tracking-widest">Email adresa</label>
                        <input name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" class="w-full bg-black border border-[#2A2D34] focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed/20 rounded px-md py-sm text-on-surface transition-all" placeholder="vas@email.com" type="email" required/>
                    </div>
                    
                    <div class="space-y-xs">
                        <label class="font-label-bold text-label-bold text-secondary uppercase tracking-widest">Lozinka</label>
                        <div class="relative">
                            <input name="password" class="w-full bg-black border border-[#2A2D34] focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed/20 rounded px-md py-sm text-on-surface transition-all" placeholder="••••••••" type="password" id="password" required/>
                            <span class="material-symbols-outlined absolute right-md top-1/2 -translate-y-1/2 text-on-surface-variant cursor-pointer" onclick="togglePassword()">visibility</span>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-1">Minimalno 4 karaktera</p>
                    </div>
                    
                    <div class="space-y-xs">
                        <label class="font-label-bold text-label-bold text-secondary uppercase tracking-widest">Adresa stanovanja</label>
                        <textarea name="adresa" class="w-full bg-black border border-[#2A2D34] focus:border-primary-fixed focus:ring-1 focus:ring-primary-fixed/20 rounded px-md py-sm text-on-surface transition-all resize-none" placeholder="Ulica i broj, grad, poštanski broj" rows="2"><?php echo isset($_POST['adresa']) ? htmlspecialchars($_POST['adresa']) : ''; ?></textarea>
                    </div>
                    
                    <div class="flex items-center gap-sm py-xs">
                        <input name="terms" class="w-5 h-5 bg-black border-outline text-primary-fixed focus:ring-primary-fixed rounded" id="terms" type="checkbox" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>/>
                        <label class="font-body-md text-body-md text-on-surface-variant" for="terms">Prihvatam <a class="text-primary-fixed underline underline-offset-2" href="#">Uslove korištenja</a> i polisu privatnosti.</label>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary-fixed text-on-primary-fixed font-label-bold text-label-bold py-md rounded active:scale-[0.98] transition-transform uppercase tracking-widest shadow-[0_0_20px_rgba(195,244,0,0.3)] hover:shadow-[0_0_30px_rgba(195,244,0,0.5)]">
                        Registruj se
                    </button>
                </form>
                
                <div class="mt-lg text-center">
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Već imate nalog? 
                        <a class="text-primary-fixed font-label-bold hover:underline underline-offset-4 ml-xs" href="login.php">Prijavite se ovdje</a>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Trust Badges -->
        <div class="mt-md flex justify-between px-sm gap-md overflow-x-auto">
            <div class="flex items-center gap-xs whitespace-nowrap">
                <span class="material-symbols-outlined text-primary-fixed">verified_user</span>
                <span class="text-label-bold font-label-bold uppercase text-on-surface-variant">Sigurna plaćanja</span>
            </div>
            <div class="flex items-center gap-xs whitespace-nowrap">
                <span class="material-symbols-outlined text-primary-fixed">local_shipping</span>
                <span class="text-label-bold font-label-bold uppercase text-on-surface-variant">Brza dostava</span>
            </div>
            <div class="flex items-center gap-xs whitespace-nowrap">
                <span class="material-symbols-outlined text-primary-fixed">science</span>
                <span class="text-label-bold font-label-bold uppercase text-on-surface-variant">Laboratorijski testirano</span>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="w-full py-lg mt-xl bg-surface-container-lowest border-t border-outline-variant">
    <div class="flex flex-col md:flex-row justify-between items-center px-gutter max-w-container-max mx-auto gap-md">
        <div class="font-headline-md text-headline-md font-bold text-on-surface">
            SUPP.SCIENCE
        </div>
        <div class="flex flex-wrap justify-center gap-md">
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">O nama</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">Dostava</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">Uslovi korištenja</a>
            <a class="text-on-surface-variant font-body-md hover:text-primary-fixed transition-colors" href="#">Kontakt</a>
        </div>
        <div class="font-body-md text-body-md text-on-surface-variant">
            © 2024 SUPP.SCIENCE. Sva prava pridržana.
        </div>
    </div>
</footer>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
    }
</script>

</body>
</html>