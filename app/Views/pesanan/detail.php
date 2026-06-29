<div class="page-header flex items-center justify-between">
    <div class="flex items-center gap-3 mb-2">
        <a href="/pesanan" class="btn btn-ghost btn-sm">
            <i class="ph-bold ph-arrow-left"></i>
        </a>
        <h1 class="page-title" style="font-size: var(--text-xl);">Detail Pesanan</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="/pesanan/export-pdf/<?= (int)$pesanan['id'] ?>" class="btn btn-secondary btn-sm" target="_blank">
            <i class="ph-bold ph-file-pdf"></i>
            Export PDF
        </a>
        <a href="/pesanan/print-struk/<?= (int)$pesanan['id'] ?>" class="btn btn-primary btn-sm" target="_blank">
            <i class="ph-bold ph-printer"></i>
            Print Struk
        </a>
    </div>
</div>

<?php if (!empty($flash['flash_success'])): ?>
    <div class="alert alert-success">
        <i class="ph-bold ph-check-circle"></i>
        <span><?= htmlspecialchars($flash['flash_success']) ?></span>
    </div>
<?php endif; ?>
<?php if (!empty($flash['flash_error'])): ?>
    <div class="alert alert-danger">
        <i class="ph-bold ph-warning-circle"></i>
        <span><?= htmlspecialchars($flash['flash_error']) ?></span>
    </div>
<?php endif; ?>

<div class="grid-2" style="margin-bottom: var(--space-6);">
    <!-- Info Pelanggan -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                <i class="ph-bold ph-user-circle"></i>
                Informasi Pelanggan
            </span>
        </div>
        <div class="card-body">
            <table style="width:100%; font-size: var(--text-sm);">
                <tr>
                    <td class="text-secondary" style="width:100px;">Nama</td>
                    <td>: <strong><?= htmlspecialchars($pesanan['nama'] ?? '-') ?></strong></td>
                </tr>
                <tr>
                    <td class="text-secondary">Email</td>
                    <td>: <?= htmlspecialchars($pesanan['email_user'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="text-secondary">No. Telp</td>
                    <td>: <?= htmlspecialchars($pesanan['no_telp'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="text-secondary">Alamat</td>
                    <td>: <?= htmlspecialchars($pesanan['alamat'] ?? '-') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Info Pesanan -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                <i class="ph-bold ph-receipt"></i>
                Informasi Pesanan
            </span>
        </div>
        <div class="card-body">
            <table style="width:100%; font-size: var(--text-sm);">
                <tr>
                    <td class="text-secondary" style="width:120px;">Kode</td>
                    <td>: <strong><?= htmlspecialchars($pesanan['kode_pesanan']) ?></strong></td>
                </tr>
                <tr>
                    <td class="text-secondary">Tanggal</td>
                    <td>: <?= date('d/m/Y H:i', strtotime($pesanan['tanggal_pesan'] ?? $pesanan['created_at'])) ?> WIB</td>
                </tr>
                <tr>
                    <td class="text-secondary">Metode</td>
                    <td>: <?= ucfirst($pesanan['metode_pengiriman'] ?? 'diambil') ?></td>
                </tr>
                <tr>
                    <td class="text-secondary">Status</td>
                    <td>:
                        <span class="badge <?php
                                            $sc = ['diterima' => 'badge-warning', 'dibuat' => 'badge-info', 'siap' => 'badge-express', 'selesai' => 'badge-success'];
                                            echo $sc[$pesanan['status']] ?? 'badge-warning';
                                            ?>">
                            <?php
                            $sl = ['diterima' => 'Diterima', 'dibuat' => 'Dibuat', 'siap' => 'Siap', 'selesai' => 'Selesai'];
                            echo $sl[$pesanan['status']] ?? $pesanan['status'];
                            ?>
                        </span>
                    </td>
                </tr>
                <?php if (!empty($pesanan['catatan'])): ?>
                    <tr>
                        <td class="text-secondary">Catatan</td>
                        <td>: <?= htmlspecialchars($pesanan['catatan']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Daftar Item -->
<div class="card" style="margin-bottom: var(--space-6);">
    <div class="card-header">
        <span class="card-title">
            <i class="ph-bold ph-list-bullets"></i>
            Item Pesanan
        </span>
    </div>
    <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>Layanan</th>
                    <th>Harga Satuan</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                $total = 0; ?>
                <?php foreach ($detail as $item): ?>
                    <tr>
                        <td class="text-secondary"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($item['nama_layanan']) ?></td>
                        <td>Rp <?= number_format((int)$item['harga_satuan'], 0, ',', '.') ?></td>
                        <td><?= (int)$item['quantity'] ?> <?= htmlspecialchars($item['satuan_harga'] ?? '') ?></td>
                        <td style="font-weight:600;">Rp <?= number_format((int)$item['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php $total += (int)$item['subtotal']; ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right; font-weight:700; padding: var(--space-4) var(--space-5);">
                        TOTAL:
                    </td>
                    <td style="font-weight:700; color: var(--color-primary); padding: var(--space-4) var(--space-5);">
                        Rp <?= number_format($total, 0, ',', '.') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Workflow Actions -->
<div class="card">
    <div class="card-header">
        <span class="card-title">
            <i class="ph-bold ph-arrows-clockwise"></i>
            Update Status
        </span>
    </div>
    <div class="card-body">
        <div class="flex items-center gap-3" style="flex-wrap: wrap;">
            <?php
            $nextBtn = [
                'diterima' => ['label' => 'Proses ke Dibuat', 'color' => 'btn-primary'],
                'dibuat'   => ['label' => 'Tandai Siap', 'color' => 'btn-success'],
                'siap'     => ['label' => 'Tandai Selesai', 'color' => 'btn-success'],
            ];
            $current = $pesanan['status'];
            ?>
            <?php if (isset($nextBtn[$current])): ?>
                <form method="POST" action="/pesanan/update-status/<?= (int)$pesanan['id'] ?>" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                    <button type="submit" class="btn <?= $nextBtn[$current]['color'] ?>"
                        onclick="this.disabled=true; this.form.submit();">
                        <i class="ph-bold ph-check-circle"></i>
                        <?= $nextBtn[$current]['label'] ?>
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-success" style="margin-bottom:0; padding: var(--space-3) var(--space-4);">
                    <i class="ph-bold ph-check-circle"></i>
                    <span>Pesanan sudah selesai. Tidak ada tindakan lebih lanjut.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>