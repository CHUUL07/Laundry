<div class="page-header flex items-center justify-between">
    <div>
        <h1 class="page-title">Pesanan</h1>
        <p class="page-subtitle">Kelola semua pesanan dari pelanggan.</p>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="card" style="margin-bottom: var(--space-6);">
    <div class="card-body" style="padding: var(--space-3) var(--space-5);">
        <div class="flex items-center gap-2" style="flex-wrap: wrap;">
            <a href="/pesanan" class="btn btn-sm <?= $statusNow === '' ? 'btn-primary' : 'btn-ghost' ?>">
                Semua (<?= $counts['all'] ?>)
            </a>
            <?php
            $statusLabels = [
                'diterima' => 'Diterima',
                'dibuat'   => 'Dibuat',
                'siap'     => 'Siap',
                'selesai'  => 'Selesai',
            ];
            $statusColors = [
                'diterima' => 'badge-warning',
                'dibuat'   => 'badge-info',
                'siap'     => 'badge-express',
                'selesai'  => 'badge-success',
            ];
            foreach ($statusLabels as $key => $label):
            ?>
                <a href="/pesanan?status=<?= $key ?>" class="btn btn-sm <?= $statusNow === $key ? 'btn-primary' : 'btn-ghost' ?>">
                    <?= $label ?> (<?= $counts[$key] ?? 0 ?>)
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">
            <i class="ph-bold ph-clipboard-text"></i>
            Daftar Pesanan
            <?php if ($statusNow !== ''): ?>
                <span class="badge <?= $statusColors[$statusNow] ?? 'badge-success' ?>" style="margin-left: 8px;">
                    <?= $statusLabels[$statusNow] ?? $statusNow ?>
                </span>
            <?php endif; ?>
        </span>
    </div>
    <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        <?php if (empty($pesanan)): ?>
            <div class="empty-state">
                <i class="ph-bold ph-note-blank"></i>
                <div class="empty-state-title">Tidak Ada Pesanan</div>
                <p class="empty-state-text">Belum ada pesanan yang masuk.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th style="width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($pesanan as $p): ?>
                        <tr>
                            <td class="text-secondary"><?= $no++ ?></td>
                            <td class="font-medium"><?= htmlspecialchars($p['kode_pesanan']) ?></td>
                            <td><?= htmlspecialchars($p['nama_user'] ?? 'User #' . $p['user_id']) ?></td>
                            <td class="text-sm text-secondary">
                                <?= date('d/m/Y H:i', strtotime($p['tanggal_pesan'] ?? $p['created_at'])) ?>
                            </td>
                            <td style="font-weight:600;">Rp <?= number_format((int)$p['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge <?= $statusColors[$p['status']] ?? 'badge-warning' ?>">
                                    <?= $statusLabels[$p['status']] ?? $p['status'] ?>
                                </span>
                            </td>
                            <td class="text-secondary">
                                <?= ucfirst($p['metode_pengiriman'] ?? 'diambil') ?>
                            </td>
                            <td>
                                <a href="/pesanan/<?= (int)$p['id'] ?>" class="btn btn-ghost btn-sm">
                                    <i class="ph-bold ph-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>