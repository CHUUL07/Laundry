<div class="page-header">
    <div class="flex items-center gap-3 mb-2">
        <a href="/pelanggan" class="btn btn-ghost btn-sm">
            <i class="ph-bold ph-arrow-left"></i>
            Kembali
        </a>
    </div>
    <h1 class="page-title">Tambah Pelanggan Baru</h1>
    <p class="page-subtitle">Isi data pelanggan yang ingin didaftarkan ke sistem.</p>
</div>

<div class="card" style="max-width: 680px;">
    <div class="card-body">
        <form method="POST" action="/pelanggan/store" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

            <!-- Nama Pelanggan -->
            <div class="form-group">
                <label class="form-label" for="nama_pelanggan">
                    Nama Pelanggan <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="nama_pelanggan"
                    name="nama_pelanggan"
                    class="form-control <?= isset($errors['nama_pelanggan']) ? 'form-control-error' : '' ?>"
                    placeholder="Misal: Ahmad Fauzi"
                    value="<?= htmlspecialchars($old['nama_pelanggan'] ?? '') ?>"
                    maxlength="100"
                    required>
                <?php if (isset($errors['nama_pelanggan'])): ?>
                    <p class="form-error-msg">
                        <i class="ph-bold ph-warning-circle"></i>
                        <?= htmlspecialchars($errors['nama_pelanggan']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- No. Telepon + Email (2 columns) -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="no_telp">
                        No. Telepon <span class="required">*</span>
                    </label>
                    <input
                        type="tel"
                        id="no_telp"
                        name="no_telp"
                        class="form-control <?= isset($errors['no_telp']) ? 'form-control-error' : '' ?>"
                        placeholder="Contoh: 081234567890"
                        value="<?= htmlspecialchars($old['no_telp'] ?? '') ?>"
                        maxlength="15"
                        required>
                    <?php if (isset($errors['no_telp'])): ?>
                        <p class="form-error-msg">
                            <i class="ph-bold ph-warning-circle"></i>
                            <?= htmlspecialchars($errors['no_telp']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">
                        Email <span class="text-muted">(opsional)</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control <?= isset($errors['email']) ? 'form-control-error' : '' ?>"
                        placeholder="Contoh: ahmad@email.com"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        maxlength="100">
                    <?php if (isset($errors['email'])): ?>
                        <p class="form-error-msg">
                            <i class="ph-bold ph-warning-circle"></i>
                            <?= htmlspecialchars($errors['email']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Alamat -->
            <div class="form-group">
                <label class="form-label" for="alamat">Alamat <span class="text-muted">(opsional)</span></label>
                <textarea
                    id="alamat"
                    name="alamat"
                    class="form-control"
                    placeholder="Masukkan alamat lengkap pelanggan..."
                    rows="3"><?= htmlspecialchars($old['alamat'] ?? '') ?></textarea>
                <p class="form-hint">Alamat lengkap akan memudahkan pengantaran.</p>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center gap-3" style="margin-top: var(--space-6); padding-top: var(--space-5); border-top: 1px solid var(--color-border);">
                <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.form.submit();">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Simpan Pelanggan
                </button>
                <a href="/pelanggan" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>