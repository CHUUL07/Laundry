<div class="page-header flex items-center justify-between">
    <div>
        <h1 class="page-title">Jenis Layanan</h1>
        <p class="page-subtitle">Kelola semua tipe layanan laundry yang tersedia.</p>
    </div>
    <a href="/layanan/create" class="btn btn-primary">
        <i class="ph-bold ph-plus-circle"></i>
        Tambah Layanan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Layanan Aktif</h2>
        <span class="badge badge-success"><?= count($layanan) ?> layanan</span>
    </div>
    <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        <?php if (empty($layanan)): ?>
            <div class="empty-state">
                <i class="ph-bold ph-note-blank"></i>
                <div class="empty-state-title">Tidak Ada Layanan</div>
                <p class="empty-state-text">Belum ada layanan yang ditambahkan.</p>
                <a href="/layanan/create" class="btn btn-primary">
                    <i class="ph-bold ph-plus-circle"></i>
                    Tambah Layanan Pertama
                </a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Layanan</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Satuan</th>
                        <th>Estimasi Durasi</th>
                        <th>Deskripsi</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($layanan as $i => $item): ?>
                        <tr>
                            <td class="text-secondary"><?= $i + 1 ?></td>
                            <td class="font-medium"><?= htmlspecialchars($item['nama_layanan']) ?></td>
                            <td>
                                <span class="badge badge-<?= $item['kategori'] ?>">
                                    <?= ucfirst($item['kategori']) ?>
                                </span>
                            </td>
                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td class="text-secondary"><?= htmlspecialchars($item['satuan_harga']) ?></td>
                            <td class="text-secondary"><?= htmlspecialchars($item['estimasi_durasi']) ?></td>
                            <td class="text-secondary" style="max-width: 200px;">
                                <?= htmlspecialchars($item['deskripsi'] ?? '-') ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="/layanan/edit/<?= $item['id'] ?>"
                                        class="btn btn-ghost btn-sm"
                                        title="Edit layanan">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-ghost btn-sm"
                                        style="color: var(--color-danger);"
                                        data-delete-trigger
                                        data-item-name="<?= htmlspecialchars($item['nama_layanan']) ?>"
                                        data-form-action="/layanan/delete/<?= $item['id'] ?>"
                                        title="Hapus layanan">
                                        <i class="ph-bold ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>