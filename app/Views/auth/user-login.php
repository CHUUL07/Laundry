<div class="auth-card">
    <div class="auth-brand">
        <div class="auth-brand-icon">
            <i class="ph-bold ph-washing-machine"></i>
        </div>
        <div class="auth-brand-title">Laundry-IN</div>
        <div class="auth-brand-sub">Masuk ke Akun Anda</div>
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

    <form method="POST" action="/masuk" class="auth-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email"
                id="email"
                name="email"
                class="form-control"
                placeholder="contoh@email.com"
                required
                autofocus>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Masukkan password"
                required>
        </div>

        <button type="submit" class="btn btn-primary w-full"
            onclick="this.disabled=true; this.form.submit();">
            <i class="ph-bold ph-sign-in"></i>
            Masuk
        </button>
    </form>

    <div class="auth-alt">
        Belum punya akun?
        <a href="/daftar">Daftar di sini</a>
    </div>

    <div class="auth-alt" style="margin-top: 8px;">
        <a href="/">&larr; Kembali ke Beranda</a>
    </div>
</div>