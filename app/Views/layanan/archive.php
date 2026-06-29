<div class="page-header">
    <div class="flex items-center gap-3 mb-2">
        <a href="/layanan" class="btn btn-ghost btn-sm">
            <i class="ph-bold ph-arrow-left"></i>
            Kembali ke Layanan
        </a>
    </div>
    <h1 class="page-title">Arsip Layanan</h1>
    <p class="page-subtitle">Layanan yang telah dihapus. Dapat dipulihkan kapan saja.</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Layanan Diarsipkan</h2>
        <span class="badge badge-warning"><?= count($archived) ?> arsip</span>
    </div>
    <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        <?php if (empty($archived)): ?>
            <div class="empty-state">
                <i class="ph-bold ph-archive"></i>
                <div class="empty-state-title">Arsip Kosong</div>
                <p class="empty-state-text">Tidak ada layanan yang diarsipkan saat ini.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Layanan</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Tanggal Dihapus</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archived as $i => $item): ?>
                        <tr>
                            <td class="text-secondary"><?= $i + 1 ?></td>
                            <td class="font-medium"><?= htmlspecialchars($item['nama_layanan']) ?></td>
                            <td>
                                <span class="badge badge-<?= $item['kategori'] ?>">
                                    <?= ucfirst($item['kategori']) ?>
                                </span>
                            </td>
                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td class="text-secondary">
                                <?= date('d/m/Y H:i', strtotime($item['deleted_at'])) ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <form method="POST" action="/layanan/restore/<?= $item['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                                        <button type="submit" class="btn btn-success btn-sm" title="Pulihkan layanan" onclick="this.disabled=true; this.form.submit();">
                                            <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                            Pulihkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>