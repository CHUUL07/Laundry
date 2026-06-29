<div class="page-header">
    <div class="flex items-center gap-3 mb-2">
        <a href="/layanan" class="btn btn-ghost btn-sm">
            <i class="ph-bold ph-arrow-left"></i>
            Kembali
        </a>
    </div>
    <h1 class="page-title">Edit Layanan</h1>
    <p class="page-subtitle">Ubah detail layanan yang sudah ada di sistem.</p>
</div>

<div class="card" style="max-width: 720px;">
    <div class="card-body">
        <form method="POST" action="/layanan/update/<?= $layanan['id'] ?>" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

            <!-- Nama Layanan -->
            <div class="form-group">
                <label class="form-label" for="nama_layanan">
                    Nama Layanan <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="nama_layanan"
                    name="nama_layanan"
                    class="form-control <?= isset($errors['nama_layanan']) ? 'form-control-error' : '' ?>"
                    placeholder="Misal: Cuci Express"
                    value="<?= htmlspecialchars($old['nama_layanan'] ?? $layanan['nama_layanan']) ?>"
                    maxlength="100"
                    required>
                <?php if (isset($errors['nama_layanan'])): ?>
                    <p class="form-error-msg">
                        <i class="ph-bold ph-warning-circle"></i>
                        <?= htmlspecialchars($errors['nama_layanan']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Kategori + Satuan (2 columns) -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="kategori">
                        Kategori <span class="required">*</span>
                    </label>
                    <select
                        id="kategori"
                        name="kategori"
                        class="form-control <?= isset($errors['kategori']) ? 'form-control-error' : '' ?>"
                        required>
                        <option value="">— Pilih Kategori —</option>
                        <option value="express" <?= ($old['kategori'] ?? $layanan['kategori']) === 'express' ? 'selected' : '' ?>>Express</option>
                        <option value="reguler" <?= ($old['kategori'] ?? $layanan['kategori']) === 'reguler' ? 'selected' : '' ?>>Reguler</option>
                    </select>
                    <?php if (isset($errors['kategori'])): ?>
                        <p class="form-error-msg">
                            <i class="ph-bold ph-warning-circle"></i>
                            <?= htmlspecialchars($errors['kategori']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="satuan_harga">
                        Satuan Harga <span class="required">*</span>
                    </label>
                    <select
                        id="satuan_harga"
                        name="satuan_harga"
                        class="form-control <?= isset($errors['satuan_harga']) ? 'form-control-error' : '' ?>"
                        required>
                        <option value="kg" <?= ($old['satuan_harga'] ?? $layanan['satuan_harga']) === 'kg' ? 'selected' : '' ?>>Per Kg</option>
                        <option value="item" <?= ($old['satuan_harga'] ?? $layanan['satuan_harga']) === 'item' ? 'selected' : '' ?>>Per Item</option>
                        <option value="paket" <?= ($old['satuan_harga'] ?? $layanan['satuan_harga']) === 'paket' ? 'selected' : '' ?>>Per Paket</option>
                    </select>
                    <?php if (isset($errors['satuan_harga'])): ?>
                        <p class="form-error-msg">
                            <i class="ph-bold ph-warning-circle"></i>
                            <?= htmlspecialchars($errors['satuan_harga']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Harga + Estimasi (2 columns) -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="harga">
                        Harga (Rp) <span class="required">*</span>
                    </label>
                    <input
                        type="number"
                        id="harga"
                        name="harga"
                        class="form-control <?= isset($errors['harga']) ? 'form-control-error' : '' ?>"
                        placeholder="Contoh: 8000"
                        value="<?= htmlspecialchars($old['harga'] ?? $layanan['harga']) ?>"
                        min="1"
                        required>
                    <?php if (isset($errors['harga'])): ?>
                        <p class="form-error-msg">
                            <i class="ph-bold ph-warning-circle"></i>
                            <?= htmlspecialchars($errors['harga']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estimasi_durasi">
                        Estimasi Durasi <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="estimasi_durasi"
                        name="estimasi_durasi"
                        class="form-control <?= isset($errors['estimasi_durasi']) ? 'form-control-error' : '' ?>"
                        placeholder="Misal: 2-3 Jam, 1 Hari"
                        value="<?= htmlspecialchars($old['estimasi_durasi'] ?? $layanan['estimasi_durasi']) ?>"
                        required>
                    <?php if (isset($errors['estimasi_durasi'])): ?>
                        <p class="form-error-msg">
                            <i class="ph-bold ph-warning-circle"></i>
                            <?= htmlspecialchars($errors['estimasi_durasi']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label class="form-label" for="deskripsi">Deskripsi <span class="text-muted">(opsional)</span></label>
                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    class="form-control"
                    placeholder="Jelaskan layanan ini secara singkat..."
                    rows="3"><?= htmlspecialchars($old['deskripsi'] ?? $layanan['deskripsi'] ?? '') ?></textarea>
                <p class="form-hint">Deskripsi membantu pelanggan memahami layanan ini.</p>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center gap-3" style="margin-top: var(--space-6); padding-top: var(--space-5); border-top: 1px solid var(--color-border);">
                <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.form.submit();">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Perbarui Layanan
                </button>
                <a href="/layanan" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>