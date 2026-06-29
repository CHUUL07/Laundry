<div class="auth-card">
    <div class="auth-brand">
        <div class="auth-brand-icon">
            <i class="ph-bold ph-washing-machine"></i>
        </div>
        <div class="auth-brand-title">Laundry-IN</div>
        <div class="auth-brand-sub">Daftar Akun Baru</div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="ph-bold ph-warning-circle"></i>
            <span>Periksa kembali data yang dimasukkan.</span>
        </div>
    <?php endif; ?>

    <form method="POST" action="/daftar" class="auth-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

        <div class="form-group">
            <label for="nama" class="form-label">
                Nama Lengkap <span class="required">*</span>
            </label>
            <input type="text"
                id="nama"
                name="nama"
                class="form-control <?= isset($errors['nama']) ? 'form-control-error' : '' ?>"
                value="<?= htmlspecialchars($old['nama'] ?? '') ?>"
                placeholder="Masukkan nama lengkap"
                required>
            <?php if (isset($errors['nama'])): ?>
                <div class="form-error-msg">
                    <i class="ph-bold ph-warning-circle"></i>
                    <?= htmlspecialchars($errors['nama']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">
                Email <span class="required">*</span>
            </label>
            <input type="email"
                id="email"
                name="email"
                class="form-control <?= isset($errors['email']) ? 'form-control-error' : '' ?>"
                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                placeholder="contoh@email.com"
                required>
            <?php if (isset($errors['email'])): ?>
                <div class="form-error-msg">
                    <i class="ph-bold ph-warning-circle"></i>
                    <?= htmlspecialchars($errors['email']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">
                Password <span class="required">*</span>
            </label>
            <input type="password"
                id="password"
                name="password"
                class="form-control <?= isset($errors['password']) ? 'form-control-error' : '' ?>"
                placeholder="Minimal 6 karakter"
                required>
            <?php if (isset($errors['password'])): ?>
                <div class="form-error-msg">
                    <i class="ph-bold ph-warning-circle"></i>
                    <?= htmlspecialchars($errors['password']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="no_telp" class="form-label">No. Telepon</label>
                <input type="tel"
                    id="no_telp"
                    name="no_telp"
                    class="form-control <?= isset($errors['no_telp']) ? 'form-control-error' : '' ?>"
                    value="<?= htmlspecialchars($old['no_telp'] ?? '') ?>"
                    placeholder="081234567890">
                <?php if (isset($errors['no_telp'])): ?>
                    <div class="form-error-msg">
                        <i class="ph-bold ph-warning-circle"></i>
                        <?= htmlspecialchars($errors['no_telp']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea id="alamat"
                name="alamat"
                class="form-control"
                placeholder="Jl. Contoh No. 123, Kota"><?= htmlspecialchars($old['alamat'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-full"
            onclick="this.disabled=true; this.form.submit();">
            <i class="ph-bold ph-user-plus"></i>
            Daftar Akun
        </button>
    </form>

    <div class="auth-alt">
        Sudah punya akun?
        <a href="/masuk">Masuk di sini</a>
    </div>
</div>