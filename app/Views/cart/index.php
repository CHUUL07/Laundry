<?php // app/Views/cart/index.php 
?>

<div class="page-header flex items-center justify-between">
    <div>
        <h1 class="page-title">Keranjang Belanja</h1>
        <p class="page-subtitle">
            <?= $count > 0 ? "{$count} item dalam keranjang" : 'Keranjang kosong' ?>
        </p>
    </div>
    <a href="/layanan" class="btn btn-ghost">
        <i class="ph-bold ph-arrow-left"></i>
        Lanjut Belanja
    </a>
</div>

<?php if (!empty($flash['flash_success'])): ?>
    <div class="alert alert-success">
        <i class="ph-bold ph-check-circle"></i>
        <span><?= htmlspecialchars($flash['flash_success'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($flash['flash_error'])): ?>
    <div class="alert alert-danger">
        <i class="ph-bold ph-warning-circle"></i>
        <span><?= htmlspecialchars($flash['flash_error'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
<?php endif; ?>

<?php if (empty($items)): ?>
    <!-- Empty cart state -->
    <div class="card">
        <div class="empty-state" style="padding: 60px 20px;">
            <i class="ph-bold ph-shopping-cart" style="font-size: 4rem; opacity: 0.2;"></i>
            <div class="empty-state-title">Keranjang Masih Kosong</div>
            <p class="empty-state-text">Tambahkan layanan dari halaman Daftar Layanan.</p>
            <a href="/layanan" class="btn btn-primary">
                <i class="ph-bold ph-plus"></i>
                Tambah Layanan
            </a>
        </div>
    </div>

<?php else: ?>
    <!-- Cart items table -->
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-header">
            <span class="card-title">
                <i class="ph-bold ph-shopping-cart"></i>
                Item dalam Keranjang
            </span>

            <!-- Tombol Kosongkan Semua (destroy) -->
            <form method="POST" action="/cart/destroy" style="display:inline;">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-danger btn-sm"
                    onclick="if(!confirm('Kosongkan semua keranjang?')) return false; this.disabled=true; this.form.submit();">
                    <i class="ph-bold ph-trash"></i>
                    Kosongkan Semua
                </button>
            </form>
        </div>

        <div class="table-wrapper" style="border: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No.</th>
                        <th>Nama Layanan</th>
                        <th>Harga Satuan</th>
                        <th>Satuan</th>
                        <th style="width: 130px;">Jumlah</th>
                        <th>Subtotal</th>
                        <th style="width: 60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td class="text-secondary"><?= $index + 1 ?></td>
                            <td class="font-medium"><?= htmlspecialchars($item['nama_layanan'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>Rp <?= number_format((int)$item['harga'], 0, ',', '.') ?></td>
                            <td class="text-secondary"><?= htmlspecialchars(strtoupper($item['satuan_harga']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <!-- Form untuk update quantity -->
                                <form method="POST"
                                    action="/cart/update/<?= (int)$item['id_layanan'] ?>"
                                    style="display:flex; align-items:center; gap:6px;">
                                    <input type="hidden" name="csrf_token"
                                        value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="number"
                                        name="quantity"
                                        value="<?= (int)$item['quantity'] ?>"
                                        min="0"
                                        max="99"
                                        class="form-control"
                                        style="width:60px; padding:4px 8px; text-align:center;"
                                        onchange="this.form.submit()">
                                </form>
                            </td>
                            <td style="font-weight: 600;">
                                Rp <?= number_format((int)$item['subtotal'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <!-- Form untuk remove item -->
                                <form method="POST"
                                    action="/cart/remove/<?= (int)$item['id_layanan'] ?>"
                                    style="display:inline;">
                                    <input type="hidden" name="csrf_token"
                                        value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                        style="color: var(--color-danger);"
                                        onclick="this.disabled=true; this.form.submit();"
                                        title="Hapus dari keranjang">
                                        <i class="ph-bold ph-x"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: right; font-weight: 600; padding: var(--space-4) var(--space-5);">
                            TOTAL KESELURUHAN:
                        </td>
                        <td style="font-weight: 700; font-size: var(--text-lg); color: var(--color-primary); padding: var(--space-4) var(--space-5);">
                            Rp <?= number_format($total, 0, ',', '.') ?>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Ringkasan Pesanan -->
    <form method="POST" action="/cart/checkout">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
        <div class="card" style="max-width: 400px; margin-left: auto;">
            <div class="card-header">
                <span class="card-title">
                    <i class="ph-bold ph-receipt"></i>
                    Ringkasan Pesanan
                </span>
            </div>
            <div class="card-body">
                <table style="width: 100%; font-size: var(--text-sm);">
                    <tr>
                        <td class="text-secondary">Total Item</td>
                        <td style="text-align:right; font-weight:500;"><?= $count ?> item</td>
                    </tr>
                    <tr>
                        <td class="text-secondary">Total Layanan</td>
                        <td style="text-align:right; font-weight:500;"><?= count($items) ?> jenis</td>
                    </tr>
                </table>

                <div class="form-group" style="margin-top: var(--space-4);">
                    <label class="form-label">Metode Pengiriman</label>
                    <select name="metode_pengiriman" class="form-control" required>
                        <option value="diambil">Diambil Sendiri</option>
                        <option value="diantar">Diantar (antar jemput)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="catatan" class="form-label">Catatan (opsional)</label>
                    <textarea name="catatan" id="catatan" class="form-control" rows="2" placeholder="Catatan untuk pesanan..."></textarea>
                </div>

                <table style="width: 100%; font-size: var(--text-sm);">
                    <tr style="border-top: 2px solid var(--color-border); font-weight:700; font-size:var(--text-base);">
                        <td style="padding-top: var(--space-3);">Total Harga</td>
                        <td style="text-align:right; padding-top: var(--space-3); color: var(--color-primary);">
                            Rp <?= number_format($total, 0, ',', '.') ?>
                        </td>
                    </tr>
                </table>

                <div style="margin-top: var(--space-6);">
                    <button type="submit" class="btn btn-primary" style="width:100%;"
                        onclick="this.disabled=true; this.form.submit();">
                        <i class="ph-bold ph-check-circle"></i>
                        Pesan Sekarang
                    </button>
                </div>
            </div>
        </div>
    </form>

<?php endif; ?>