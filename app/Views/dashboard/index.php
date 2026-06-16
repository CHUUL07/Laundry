<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Selamat Datang, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></h1>
    <p class="page-subtitle">Kelola semua jenis layanan laundry dari satu tempat.</p>
</div>

<!-- Summary Cards -->
<div class="grid-4">

    <!-- Total Aktif -->
    <div class="summary-card" style="--summary-accent: var(--color-primary-soft); --summary-icon-color: var(--color-primary);">
        <div class="summary-card-icon">
            <i class="ph-bold ph-stack"></i>
        </div>
        <div class="summary-card-number"><?= $totalAktif ?></div>
        <div class="summary-card-label">Total Layanan Aktif</div>
        <div class="summary-card-divider"></div>
        <div class="summary-card-meta">Semua layanan yang tersedia</div>
    </div>

    <!-- Total Express -->
    <div class="summary-card" style="--summary-accent: var(--color-accent-soft); --summary-icon-color: var(--color-accent);">
        <div class="summary-card-icon">
            <i class="ph-bold ph-lightning"></i>
        </div>
        <div class="summary-card-number"><?= $totalExpress ?></div>
        <div class="summary-card-label">Layanan Express</div>
        <div class="summary-card-divider"></div>
        <div class="summary-card-meta">Prioritas & cepat selesai</div>
    </div>

    <!-- Total Reguler -->
    <div class="summary-card" style="--summary-accent: var(--color-info-soft); --summary-icon-color: var(--color-info);">
        <div class="summary-card-icon">
            <i class="ph-bold ph-clock"></i>
        </div>
        <div class="summary-card-number"><?= $totalReguler ?></div>
        <div class="summary-card-label">Layanan Reguler</div>
        <div class="summary-card-divider"></div>
        <div class="summary-card-meta">Standar waktu normal</div>
    </div>

    <!-- Total Arsip -->
    <div class="summary-card" style="--summary-accent: var(--color-danger-soft); --summary-icon-color: var(--color-danger);">
        <div class="summary-card-icon">
            <i class="ph-bold ph-archive"></i>
        </div>
        <div class="summary-card-number"><?= $totalArsip ?></div>
        <div class="summary-card-label">Layanan Diarsipkan</div>
        <div class="summary-card-divider"></div>
        <div class="summary-card-meta">Dapat dipulihkan kapan saja</div>
    </div>

</div>

<!-- Quick Actions -->
<div class="section-gap">
    <h2 class="card-title mb-4">Akses Cepat</h2>
    <div class="shortcut-grid">
        <a href="/laundry-in/layanan/create" class="shortcut-card">
            <i class="ph-bold ph-plus-circle"></i>
            <span class="shortcut-card-label">Tambah Layanan</span>
        </a>
        <a href="/laundry-in/layanan" class="shortcut-card">
            <i class="ph-bold ph-list-bullets"></i>
            <span class="shortcut-card-label">Lihat Semua Layanan</span>
        </a>
        <a href="/laundry-in/layanan/archive" class="shortcut-card">
            <i class="ph-bold ph-archive"></i>
            <span class="shortcut-card-label">Lihat Arsip</span>
        </a>
    </div>
</div>

<!-- Recent Services -->
<div class="section-gap">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Layanan Terbaru</h2>
            <a href="/laundry-in/layanan" class="btn btn-ghost btn-sm">
                Lihat Semua
                <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
        <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
            <?php if (empty($recentLayanan)): ?>
                <div class="empty-state">
                    <i class="ph-bold ph-note-blank"></i>
                    <div class="empty-state-title">Belum Ada Layanan</div>
                    <p class="empty-state-text">Tambahkan layanan pertama Anda sekarang.</p>
                    <a href="/laundry-in/layanan/create" class="btn btn-primary">
                        <i class="ph-bold ph-plus-circle"></i>
                        Tambah Layanan
                    </a>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Layanan</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Estimasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLayanan as $item): ?>
                            <tr>
                                <td class="font-medium"><?= htmlspecialchars($item['nama_layanan']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $item['kategori'] ?>">
                                        <?= ucfirst($item['kategori']) ?>
                                    </span>
                                </td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?> / <?= $item['satuan_harga'] ?></td>
                                <td class="text-secondary"><?= htmlspecialchars($item['estimasi_durasi']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>