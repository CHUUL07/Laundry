<?php
$pageTitle  = $pageTitle ?? 'Beranda';
$activeNav  = $activeNav ?? 'beranda';
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
    <link rel="stylesheet" href="/laundry-in/public/assets/css/variables.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/reset.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/layout.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/components.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/utilities.css">
    <link rel="stylesheet" href="/laundry-in/public/assets/css/landing.css">
</head>

<body>

    <!-- ============ PUBLIC HEADER ============ -->
    <header class="landing-header" id="landing-header">
        <div class="landing-header-inner">
            <div class="landing-header-left">
                <a href="/laundry-in/" class="landing-logo">
                    <div class="landing-logo-icon">
                        <i class="ph-bold ph-washing-machine"></i>
                    </div>
                    <span class="landing-logo-text">Laundry-IN</span>
                </a>
            </div>

            <!-- Desktop Nav -->
            <nav class="landing-nav" id="landing-nav">
                <a href="/laundry-in/#hero" class="landing-nav-link active">Beranda</a>
                <a href="/laundry-in/#layanan" class="landing-nav-link">Layanan</a>
                <a href="/laundry-in/#tentang" class="landing-nav-link">Tentang</a>
            </nav>

            <div class="landing-header-right">
                <button class="theme-toggle landing-theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <i class="ph-bold ph-sun hidden" id="icon-sun"></i>
                    <i class="ph-bold ph-moon" id="icon-moon"></i>
                </button>
                <a href="/laundry-in/login" class="btn btn-primary btn-sm landing-admin-btn">
                    <i class="ph-bold ph-sign-in"></i>
                    Admin
                </a>
                <button class="landing-hamburger" id="landing-hamburger" aria-label="Toggle menu">
                    <i class="ph-bold ph-list"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Nav Drawer -->
        <div class="landing-mobile-nav" id="landing-mobile-nav">
            <a href="/laundry-in/#hero" class="landing-nav-link">Beranda</a>
            <a href="/laundry-in/#layanan" class="landing-nav-link">Layanan</a>
            <a href="/laundry-in/#tentang" class="landing-nav-link">Tentang</a>
            <div class="landing-mobile-divider"></div>
            <a href="/laundry-in/login" class="landing-nav-link landing-nav-login">
                <i class="ph-bold ph-sign-in"></i>
                Masuk Admin
            </a>
        </div>
    </header>

    <!-- ============ HERO SECTION ============ -->
    <section class="landing-hero" id="hero">
        <div class="landing-hero-bg"></div>
        <div class="landing-hero-content">
            <div class="landing-hero-text">
                <span class="landing-hero-badge">Manajemen Laundry Digital</span>
                <h1 class="landing-hero-title">Kelola Layanan Laundry <span class="text-brand">Lebih Mudah</span></h1>
                <p class="landing-hero-subtitle">
                    Laundry-IN membantu Anda mengelola berbagai jenis layanan laundry
                    secara digital — cepat, rapi, dan profesional.
                </p>
                <div class="landing-hero-actions">
                    <a href="/laundry-in/#layanan" class="btn btn-primary btn-lg">
                        <i class="ph-bold ph-list-bullets"></i>
                        Lihat Layanan
                    </a>
                </div>
            </div>
            <div class="landing-hero-visual">
                <div class="landing-hero-illustration">
                    <img src="/laundry-in/public/assets/images/Gambar-Laundry.png" alt="Laundry Illustration" class="landing-hero-img">
                </div>
            </div>
        </div>
    </section>

    <!-- ============ LAYANAN SECTION ============ -->
    <section class="landing-section" id="layanan">
        <div class="landing-section-header">
            <span class="landing-section-badge">Layanan Kami</span>
            <h2 class="landing-section-title">Jenis Layanan Laundry</h2>
            <p class="landing-section-subtitle">
                Berbagai pilihan layanan laundry dengan kualitas terbaik untuk memenuhi kebutuhan Anda.
            </p>
        </div>

        <div class="landing-services">
            <?php if (empty($layanan)): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="ph-bold ph-note-blank"></i>
                    <div class="empty-state-title">Belum Ada Layanan</div>
                    <p class="empty-state-text">Belum ada layanan yang tersedia saat ini.</p>
                </div>
            <?php else: ?>
                <?php foreach ($layanan as $item): ?>
                    <div class="landing-service-card">
                        <div class="landing-service-card-header">
                            <div class="landing-service-icon">
                                <i class="ph-bold ph-drop"></i>
                            </div>
                            <span class="badge badge-<?= $item['kategori'] ?>">
                                <?= ucfirst($item['kategori']) ?>
                            </span>
                        </div>
                        <h3 class="landing-service-name"><?= htmlspecialchars($item['nama_layanan']) ?></h3>
                        <div class="landing-service-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                        <div class="landing-service-meta">
                            <span><i class="ph-bold ph-tag"></i> <?= htmlspecialchars($item['satuan_harga']) ?></span>
                            <span><i class="ph-bold ph-clock"></i> <?= htmlspecialchars($item['estimasi_durasi']) ?></span>
                        </div>
                        <?php if (!empty($item['deskripsi'])): ?>
                            <p class="landing-service-desc"><?= htmlspecialchars($item['deskripsi']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============ TENTANG SECTION ============ -->
    <section class="landing-section landing-section-alt" id="tentang">
        <div class="landing-section-header">
            <span class="landing-section-badge">Tentang</span>
            <h2 class="landing-section-title">Mengapa Laundry-IN?</h2>
            <p class="landing-section-subtitle">
                Solusi digital untuk mengelola layanan laundry Anda dengan lebih efisien.
            </p>
        </div>

        <div class="landing-features">
            <div class="landing-feature-card">
                <div class="landing-feature-icon" style="--feature-color: var(--color-primary);">
                    <i class="ph-bold ph-folders"></i>
                </div>
                <h3 class="landing-feature-title">Data Terpusat</h3>
                <p class="landing-feature-desc">Semua data layanan tersimpan rapi di satu tempat, mudah diakses kapan saja.</p>
            </div>
            <div class="landing-feature-card">
                <div class="landing-feature-icon" style="--feature-color: var(--color-accent);">
                    <i class="ph-bold ph-shield-check"></i>
                </div>
                <h3 class="landing-feature-title">Aman & Terpercaya</h3>
                <p class="landing-feature-desc">Data aman dengan sistem soft-delete dan perlindungan CSRF.</p>
            </div>
            <div class="landing-feature-card">
                <div class="landing-feature-icon" style="--feature-color: var(--color-info);">
                    <i class="ph-bold ph-monitor"></i>
                </div>
                <h3 class="landing-feature-title">Responsive Design</h3>
                <p class="landing-feature-desc">Akses dari mana saja — desktop, tablet, maupun smartphone.</p>
            </div>
        </div>
    </section>

    <!-- ============ FOOTER ============ -->
    <footer class="landing-footer">
        <div class="landing-footer-inner">
            <div class="landing-footer-brand">
                <div class="landing-footer-logo">
                    <i class="ph-bold ph-washing-machine"></i>
                    Laundry-IN
                </div>
                <p class="landing-footer-desc">Sistem Manajemen Layanan Laundry berbasis web.</p>
            </div>
            <div class="landing-footer-links">
                <h4>Navigasi</h4>
                <a href="/laundry-in/#hero">Beranda</a>
                <a href="/laundry-in/#layanan">Layanan</a>
                <a href="/laundry-in/#tentang">Tentang</a>
            </div>
            <div class="landing-footer-links">
                <h4>Akses</h4>
                <a href="/laundry-in/login">Masuk Admin</a>
            </div>
        </div>
        <div class="landing-footer-bottom">
            <p>&copy; <?= date('Y') ?> Laundry-IN. All rights reserved.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="/laundry-in/public/assets/js/landing.js"></script>
    <script src="/laundry-in/public/assets/js/theme.js"></script>

</body>

</html>