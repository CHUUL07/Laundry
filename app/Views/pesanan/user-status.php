<div class="page-header" style="text-align:center; padding: var(--space-8) 0;">
    <div style="width:64px; height:64px; border-radius:50%; background-color:var(--color-success-soft); display:flex; align-items:center; justify-content:center; margin: 0 auto var(--space-4); font-size:1.75rem; color:var(--color-success);">
        <i class="ph-bold ph-check-circle"></i>
    </div>
    <h1 class="page-title">Pesanan Berhasil Dibuat!</h1>
    <p class="page-subtitle">Terima kasih, pesanan Anda telah tercatat.</p>
</div>

<?php if (!empty($flash['flash_success'])): ?>
    <div class="alert alert-success">
        <i class="ph-bold ph-check-circle"></i>
        <span><?= htmlspecialchars($flash['flash_success']) ?></span>
    </div>
<?php endif; ?>

<div class="grid-2" style="margin-bottom: var(--space-6);">
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
                    <td class="text-secondary" style="width:110px;">Kode</td>
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
                    <td class="text-secondary">Total</td>
                    <td style="font-weight:700; color:var(--color-primary);">Rp <?= number_format((int)$pesanan['total_harga'], 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Status Progress -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                <i class="ph-bold ph-arrows-clockwise"></i>
                Status Pesanan
            </span>
        </div>
        <div class="card-body">
            <?php
            $statusOrder = ['diterima', 'dibuat', 'siap', 'selesai'];
            $statusLabels = ['diterima' => 'Diterima', 'dibuat' => 'Diproses', 'siap' => 'Siap', 'selesai' => 'Selesai'];
            $currentIdx = array_search($pesanan['status'], $statusOrder);
            ?>
            <div style="display:flex; flex-direction:column; gap: var(--space-3);">
                <?php foreach ($statusOrder as $idx => $st): ?>
                    <?php
                    $isDone = $idx <= $currentIdx;
                    $isCurrent = $idx === $currentIdx;
                    ?>
                    <div style="display:flex; align-items:center; gap: var(--space-3);">
                        <div style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.9rem;
                            <?= $isDone ? "background-color:var(--color-primary-soft); color:var(--color-primary);" : "background-color:var(--color-bg-elevated); color:var(--color-text-muted);" ?>
                            <?= $isCurrent ? 'border: 2px solid var(--color-primary);' : '' ?>
                        ">
                            <?php if ($isDone && !$isCurrent): ?>
                                <i class="ph-bold ph-check"></i>
                            <?php else: ?>
                                <?= $idx + 1 ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-weight: <?= $isCurrent ? '700' : '500' ?>; font-size: var(--text-sm); color: <?= $isDone ? 'var(--color-text-primary)' : 'var(--color-text-muted)' ?>;">
                                <?= $statusLabels[$st] ?>
                            </div>
                            <?php if ($isCurrent): ?>
                                <div style="font-size: var(--text-xs); color: var(--color-primary); font-weight:500;">
                                    <?php
                                    $msgs = [
                                        'diterima' => 'Pesanan sedang menunggu diproses',
                                        'dibuat' => 'Pesanan sedang dikerjakan',
                                        'siap' => 'Pesanan siap untuk diambil/diantar',
                                        'selesai' => 'Pesanan sudah selesai',
                                    ];
                                    echo $msgs[$st] ?? '';
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($idx < count($statusOrder) - 1): ?>
                        <div style="width:2px; height:16px; background-color: <?= $idx < $currentIdx ? 'var(--color-primary)' : 'var(--color-border)'; ?>; margin-left: 15px;"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
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
                    <td colspan="4" style="text-align:right; font-weight:700; padding: var(--space-4) var(--space-5);">TOTAL:</td>
                    <td style="font-weight:700; color: var(--color-primary); padding: var(--space-4) var(--space-5);">
                        Rp <?= number_format($total, 0, ',', '.') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="text-align:center; margin-top: var(--space-8);">
    <a href="/" class="btn btn-primary">
        <i class="ph-bold ph-house"></i>
        Kembali ke Beranda
    </a>
</div>