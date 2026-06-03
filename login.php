<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    // Redirekcija prema ulozi
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Prvo provjeri u tabeli kupac
    $stmt = $pdo->prepare("SELECT id_kupca as id, ime, prezime, email, 'kupac' as role FROM kupac WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Provjeri u zaposlenik
        $stmt = $pdo->prepare("SELECT id_zaposlenika as id, ime, prezime, email, LOWER(uloga) as role FROM zaposlenik WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    }
    
    // Demo lozinka je 'password' za sve korisnike
    if ($user && $password == 'password') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['ime'] . ' ' . $user['prezime'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // Redirekcija prema ulozi
        switch ($user['role']) {
            case 'admin':
                header('Location: admin/dashboard.php');
                break;
            case 'skladistar':
                header('Location: skladistar/dashboard.php');
                break;
            case 'dostavljac':
                header('Location: dostavljac/dashboard.php');
                break;
            default:
                header('Location: index.php');
        }
        exit();
    } else {
        $error = 'Pogrešan email ili lozinka!';
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="bs">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Prijava - SUPP.SCIENCE</title>
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
        .login-card {
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .input-group {
            transition: all 0.3s ease;
        }
        .input-group:focus-within {
            transform: translateX(4px);
        }
        input:focus {
            box-shadow: 0 0 0 3px rgba(195, 244, 0, 0.2);
        }
    </style>
</head>
<body class="bg-background text-on-background selection:bg-primary-fixed selection:text-on-primary-fixed">

<!-- Simple Nav -->
<nav class="fixed top-0 w-full z-50 bg-background/95 backdrop-blur-md border-b border-outline-variant shadow-lg">
    <div class="flex justify-between items-center h-20 px-gutter max-w-container_max mx-auto" style="padding-left: 24px; padding-right: 24px;">
        <a href="index.php" class="font-display-lg-mobile text-display-lg-mobile font-black text-primary-fixed tracking-tighter hover:brightness-110 transition-all">
            SUPP.SCIENCE
        </a>
        <div class="flex items-center gap-sm">
            <a href="register.php" class="text-on-surface-variant hover:text-primary-fixed transition-colors font-label-bold">
                Registracija
            </a>
        </div>
    </div>
</nav>

<main class="min-h-screen flex items-center justify-center px-gutter" style="padding-top: 80px; padding-bottom: 80px;">
    
    <div class="login-card w-full max-w-md">
        <!-- Branding -->
        <div class="text-center mb-lg">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-primary-fixed/10 rounded-full mb-md">
                <span class="material-symbols-outlined text-5xl text-primary-fixed">fitness_center</span>
            </div>
            <h1 class="font-headline-xl text-headline-xl text-primary mb-xs">Dobrodošli nazad</h1>
            <p class="text-on-surface-variant text-body-lg">Prijavite se na svoj nalog</p>
        </div>
        
        <!-- Login Form Card -->
        <div class="bg-surface-container-high border border-outline-variant rounded-xl p-md" style="padding: 32px;">
            
            <?php if ($error): ?>
            <div class="bg-error-container border border-error rounded-lg p-md mb-md flex items-center gap-sm" style="padding: 16px;">
                <span class="material-symbols-outlined text-error">error</span>
                <p class="text-on-error-container text-body-md"><?php echo htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-md">
                <!-- Email Field -->
                <div class="input-group">
                    <label class="font-label-bold text-on-surface-variant text-xs uppercase tracking-wider block mb-xs">
                        Email adresa
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base">mail</span>
                        <input 
                            type="email" 
                            name="email" 
                            required 
                            class="w-full bg-background border border-outline-variant rounded-lg pl-10 pr-4 py-sm text-body-md focus:border-primary-fixed focus:ring-0 focus:outline-none transition-all"
                            placeholder="vas@email.com"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        >
                    </div>
                </div>
                
                <!-- Password Field -->
                <div class="input-group">
                    <label class="font-label-bold text-on-surface-variant text-xs uppercase tracking-wider block mb-xs">
                        Lozinka
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-base">lock</span>
                        <input 
                            type="password" 
                            name="password" 
                            required 
                            class="w-full bg-background border border-outline-variant rounded-lg pl-10 pr-4 py-sm text-body-md focus:border-primary-fixed focus:ring-0 focus:outline-none transition-all"
                            placeholder="••••••••"
                        >
                    </div>
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="flex justify-between items-center">
                    <label class="flex items-center gap-sm cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 rounded border-outline-variant bg-background text-primary-fixed focus:ring-primary-fixed focus:ring-offset-0">
                        <span class="text-on-surface-variant text-body-md">Zapamti me</span>
                    </label>
                    <a href="#" class="text-primary-fixed text-body-md hover:brightness-110 transition-all">Zaboravili ste lozinku?</a>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="w-full bg-primary-fixed text-on-primary-fixed font-label-bold py-md px-lg rounded-lg hover:brightness-110 transition-all flex items-center justify-center gap-sm mt-md">
                    <span class="material-symbols-outlined">login</span>
                    PRIJAVI SE
                </button>
            </form>
            
            <!-- Divider -->
            <div class="relative my-md">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-outline-variant"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-surface-container-high px-sm text-on-surface-variant">ili</span>
                </div>
            </div>
            
            <!-- Demo Credentials Info -->
            <div class="bg-surface-container border border-outline-variant rounded-lg p-md" style="padding: 16px;">
                <p class="font-label-bold text-on-surface-variant text-xs uppercase tracking-wider mb-xs text-center">
                    DEMO NALOZI
                </p>
                <div class="space-y-sm text-sm">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Admin:</span>
                        <span class="text-primary-fixed">admin@shop.com</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Skladištar:</span>
                        <span class="text-primary-fixed">skladistar@shop.com</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Dostavljač:</span>
                        <span class="text-primary-fixed">dostavljac@shop.com</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Kupac:</span>
                        <span class="text-primary-fixed">isak.camdzic@gmail.com</span>
                    </div>
                    <div class="text-center mt-xs">
                        <span class="text-on-surface-variant text-xs">Lozinka za sve naloge: </span>
                        <span class="text-primary-fixed font-label-bold">password</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Register Link -->
        <div class="text-center mt-md">
            <p class="text-on-surface-variant text-body-md">
                Nemate nalog? 
                <a href="register.php" class="text-primary-fixed hover:brightness-110 transition-all font-label-bold">
                    Registrujte se
                </a>
            </p>
        </div>
        
        <!-- Back to Home -->
        <div class="text-center mt-md">
            <a href="index.php" class="inline-flex items-center gap-xs text-on-surface-variant hover:text-primary-fixed transition-colors text-body-md">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Nazad na početnu
            </a>
        </div>
    </div>
    
</main>

<!-- Footer -->
<footer class="w-full py-lg bg-surface-container-lowest border-t border-outline-variant" style="padding-top: 48px; padding-bottom: 48px;">
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