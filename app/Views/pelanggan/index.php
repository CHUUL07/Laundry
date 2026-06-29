<div class="page-header flex items-center justify-between">
    <div>
        <h1 class="page-title">Data Pelanggan</h1>
        <p class="page-subtitle">Kelola data pelanggan laundry Anda.</p>
    </div>
    <a href="/pelanggan/create" class="btn btn-primary">
        <i class="ph-bold ph-user-plus"></i>
        Tambah Pelanggan
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Daftar Pelanggan Aktif</h2>
        <span class="badge badge-success"><?= count($pelanggan) ?> pelanggan</span>
    </div>
    <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        <?php if (empty($pelanggan)): ?>
            <div class="empty-state">
                <i class="ph-bold ph-users"></i>
                <div class="empty-state-title">Tidak Ada Pelanggan</div>
                <p class="empty-state-text">Belum ada pelanggan yang terdaftar.</p>
                <a href="/pelanggan/create" class="btn btn-primary">
                    <i class="ph-bold ph-user-plus"></i>
                    Tambah Pelanggan Pertama
                </a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Pelanggan</th>
                        <th>No. Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pelanggan as $i => $item): ?>
                        <tr>
                            <td class="text-secondary"><?= $i + 1 ?></td>
                            <td class="font-medium"><?= htmlspecialchars($item['nama_pelanggan']) ?></td>
                            <td class="text-secondary"><?= htmlspecialchars($item['no_telp']) ?></td>
                            <td class="text-secondary"><?= htmlspecialchars($item['email'] ?? '-') ?></td>
                            <td class="text-secondary" style="max-width: 200px;">
                                <?= htmlspecialchars($item['alamat'] ?? '-') ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="/pelanggan/edit/<?= $item['id'] ?>"
                                        class="btn btn-ghost btn-sm"
                                        title="Edit pelanggan">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-ghost btn-sm"
                                        style="color: var(--color-danger);"
                                        data-delete-trigger
                                        data-item-name="<?= htmlspecialchars($item['nama_pelanggan']) ?>"
                                        data-form-action="/pelanggan/delete/<?= $item['id'] ?>"
                                        title="Hapus pelanggan">
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