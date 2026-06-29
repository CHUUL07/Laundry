<?php
// $pageTitle — set by each view before including layout
// $activePage — 'dashboard', 'layanan', 'pelanggan', 'arsip'
$pageTitle   = $pageTitle ?? 'Dashboard';
$activePage  = $activePage ?? 'dashboard';
$adminUser   = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — Laundry-IN</title>

    <!-- Anti-FOUC: Apply theme immediately before render -->
    <script>
        (function() {
            var t = localStorage.getItem('laundry-in-theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) document.documentElement.classList.add('dark');
        })();
    </script>

    <!-- Google Fonts: Inter + Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <!-- Phosphor Icons CDN -->
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>

    <!-- Laundry-IN CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/reset.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/utilities.css') ?>">
</head>

<body>

    <div class="app-shell">

        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <!-- ============ SIDEBAR ============ -->
        <aside class="sidebar" id="sidebar">
            <!-- Brand -->
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="ph-bold ph-washing-machine"></i>
                </div>
                <div>
                    <div class="sidebar-brand-name">Laundry-IN</div>
                    <div class="sidebar-brand-sub">Management System</div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <div class="sidebar-section-label">Menu Utama</div>

                <a href="/dashboard"
                    class="sidebar-nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                    <i class="ph-bold ph-house"></i>
                    Dashboard
                </a>

                <a href="/layanan"
                    class="sidebar-nav-item <?= $activePage === 'layanan' ? 'active' : '' ?>">
                    <i class="ph-bold ph-list-bullets"></i>
                    Jenis Layanan
                </a>
                <a href="/pelanggan"
                    class="sidebar-nav-item <?= $activePage === 'pelanggan' ? 'active' : '' ?>">
                    <i class="ph-bold ph-users"></i>
                    Pelanggan
                </a>
                <?php
                require_once __DIR__ . '/../../Models/PesananModel.php';
                $pesananModel = new PesananModel();
                $pesananBaru = $pesananModel->countNew();
                ?>
                <a href="/pesanan"
                    class="sidebar-nav-item <?= $activePage === 'pesanan' ? 'active' : '' ?>">
                    <i class="ph-bold ph-clipboard-text"></i>
                    <span>Pesanan</span>
                    <?php if ($pesananBaru > 0): ?>
                        <span class="badge-counter"><?= $pesananBaru ?></span>
                    <?php endif; ?>
                </a>
                <div class="sidebar-section-label">Data</div>

                <a href="/layanan/archive"
                    class="sidebar-nav-item <?= $activePage === 'arsip' ? 'active' : '' ?>">
                    <i class="ph-bold ph-archive"></i>
                    Arsip Terhapus
                </a>
            </nav>

            <!-- Footer: User Info + Logout -->
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        <?= strtoupper(substr($adminUser, 0, 1)) ?>
                    </div>
                    <div>
                        <div class="sidebar-user-name"><?= htmlspecialchars($adminUser) ?></div>
                        <div class="sidebar-user-role">Administrator</div>
                    </div>
                </div>
                <a href="/logout" class="sidebar-logout">
                    <i class="ph-bold ph-sign-out"></i>
                    Keluar
                </a>
            </div>
        </aside>

        <!-- ============ MAIN CONTENT ============ -->
        <div class="main-content">

            <!-- Topbar -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="topbar-hamburger" id="sidebar-toggle" aria-label="Toggle sidebar">
                        <i class="ph-bold ph-list" style="font-size:1.3rem;"></i>
                    </button>
                    <nav class="topbar-breadcrumb" aria-label="Breadcrumb">
                        <a href="/dashboard">Beranda</a>
                        <?php if ($activePage !== 'dashboard'): ?>
                            <i class="ph-bold ph-caret-right" style="font-size:0.75rem;"></i>
                            <span class="current"><?= htmlspecialchars($pageTitle) ?></span>
                        <?php endif; ?>
                    </nav>
                </div>
                <div class="topbar-right">
                    <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                        <i class="ph-bold ph-sun hidden" id="icon-sun"></i>
                        <i class="ph-bold ph-moon" id="icon-moon"></i>
                    </button>
                </div>
            </header>

            <!-- Flash Messages -->
            <div style="padding: 0 var(--space-8); padding-top: var(--space-6);">
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

            <!-- Page Content Slot -->
            <main class="page-content">
                <?= $content ?? '' ?>
            </main>

        </div><!-- /.main-content -->

    </div><!-- /.app-shell -->

    <!-- Delete Confirmation Modal -->
    <div class="modal-backdrop" id="modal-backdrop">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
            <div class="modal-icon">
                <i class="ph-bold ph-trash"></i>
            </div>
            <h2 class="modal-title" id="modal-title">Hapus Data?</h2>
            <p class="modal-body">
                <strong id="modal-item-name"></strong> akan dipindahkan ke arsip.
                Data tidak akan hilang dan dapat dipulihkan kapan saja.
            </p>
            <div class="modal-actions">
                <button class="btn btn-secondary" id="modal-cancel">Batal</button>
                <form id="delete-form" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                    <button type="submit" class="btn btn-danger" id="modal-confirm" onclick="this.disabled=true; this.form.submit();">
                        <i class="ph-bold ph-trash"></i>
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="<?= base_url('assets/js/theme.js') ?>"></script>
    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>
    <script src="<?= base_url('assets/js/modal.js') ?>"></script>

</body>

</html>