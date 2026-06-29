<div class="page-header">
    <div class="flex items-center gap-3 mb-2">
        <a href="/pelanggan" class="btn btn-ghost btn-sm">
            <i class="ph-bold ph-arrow-left"></i>
            Kembali ke Pelanggan
        </a>
    </div>
    <h1 class="page-title">Arsip Pelanggan</h1>
    <p class="page-subtitle">Pelanggan yang telah dihapus. Dapat dipulihkan kapan saja.</p>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Pelanggan Diarsipkan</h2>
        <span class="badge badge-warning"><?= count($archived) ?> arsip</span>
    </div>
    <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        <?php if (empty($archived)): ?>
            <div class="empty-state">
                <i class="ph-bold ph-archive"></i>
                <div class="empty-state-title">Arsip Kosong</div>
                <p class="empty-state-text">Tidak ada pelanggan yang diarsipkan saat ini.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Pelanggan</th>
                        <th>No. Telepon</th>
                        <th>Tanggal Dihapus</th>
                        <th style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archived as $i => $item): ?>
                        <tr>
                            <td class="text-secondary"><?= $i + 1 ?></td>
                            <td class="font-medium"><?= htmlspecialchars($item['nama_pelanggan']) ?></td>
                            <td class="text-secondary"><?= htmlspecialchars($item['no_telp']) ?></td>
                            <td class="text-secondary">
                                <?= date('d/m/Y H:i', strtotime($item['deleted_at'])) ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <form method="POST" action="/pelanggan/restore/<?= $item['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                                        <button type="submit" class="btn btn-success btn-sm" title="Pulihkan pelanggan" onclick="this.disabled=true; this.form.submit();">
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