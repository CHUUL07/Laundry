<?php
$pageTitle  = $pageTitle ?? 'Laundry-IN';
$activeNav  = $activeNav ?? '';
$userNama   = $_SESSION['user_nama'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="id" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Laundry-IN</title>

    <!-- Anti-FOUC -->
    <script>
        (function() {
            var t = localStorage.getItem('laundry-in-theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) document.documentElement.classList.add('dark');
        })();
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>

    <!-- Laundry-IN CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/reset.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/utilities.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/landing.css') ?>">

    <style>
        .user-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-4) var(--space-8);
            background-color: var(--color-bg-surface);
            border-bottom: 1px solid var(--color-border);
            position: sticky;
            top: 0;
            z-index: 50;
            transition: background-color var(--transition-slow), border-color var(--transition-slow);
        }

        .user-topbar-left {
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .user-topbar-logo {
            font-family: var(--font-display);
            font-size: var(--text-lg);
            font-weight: 700;
            color: var(--color-primary);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .user-topbar-logo i {
            font-size: 1.25rem;
        }

        .user-topbar-right {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .user-topbar-user {
            font-size: var(--text-sm);
            color: var(--color-text-secondary);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .user-content {
            max-width: var(--content-max-width);
            margin: 0 auto;
            padding: var(--space-8);
            width: 100%;
        }

        @media (max-width: 767px) {
            .user-topbar {
                padding: var(--space-3) var(--space-4);
            }

            .user-content {
                padding: var(--space-4);
            }
        }
    </style>
</head>

<body>

    <!-- User Topbar -->
    <header class="user-topbar">
        <div class="user-topbar-left">
            <a href="/" class="user-topbar-logo">
                <i class="ph-bold ph-washing-machine"></i>
                Laundry-IN
            </a>
        </div>
        <div class="user-topbar-right">
            <?php
            require_once __DIR__ . '/../../Libraries/Cart.php';
            $cartInst = new Cart();
            $cartCount = $cartInst->count();
            ?>
            <a href="/cart" class="btn btn-ghost btn-sm" style="position:relative;">
                <i class="ph-bold ph-shopping-cart"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="badge-counter" style="position:absolute;top:-4px;right:-4px;"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <div class="user-topbar-user">
                <i class="ph-bold ph-user-circle"></i>
                <span><?= htmlspecialchars($userNama) ?></span>
            </div>
            <a href="/keluar" class="btn btn-ghost btn-sm" title="Keluar">
                <i class="ph-bold ph-sign-out"></i>
            </a>
        </div>
    </header>

    <!-- Flash Messages -->
    <div style="max-width: var(--content-max-width); margin: 0 auto; padding: var(--space-4) var(--space-8) 0; width: 100%;">
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <i class="ph-bold ph-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <i class="ph-bold ph-warning-circle"></i>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    </div>

    <!-- Page Content -->
    <main class="user-content">
        <?= $content ?? '' ?>
    </main>

    <script src="<?= base_url('assets/js/theme.js') ?>"></script>
</body>

</html>