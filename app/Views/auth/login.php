<div class="auth-card">
    <a href="/laundry-in/" class="btn btn-ghost btn-sm" style="margin-bottom: var(--space-4);">
        <i class="ph-bold ph-arrow-left"></i>
        Kembali ke Beranda
    </a>
    <div class="auth-brand">
        <div class="auth-brand-icon">
            <i class="ph-bold ph-washing-machine"></i>
        </div>
        <div class="auth-brand-name">Laundry-IN</div>
        <div class="auth-brand-sub">Masuk ke panel admin</div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: var(--space-5);">
            <i class="ph-bold ph-warning-circle"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="/laundry-in/login" novalidate>
        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                class="form-control"
                placeholder="Masukkan username"
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                required>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label class="form-label" for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required>
        </div>

        <button type="submit" class="btn btn-primary w-full btn-lg">
            <i class="ph-bold ph-sign-in"></i>
            Masuk
        </button>
    </form>
</div>