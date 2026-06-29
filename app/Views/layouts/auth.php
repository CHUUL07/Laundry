<!DOCTYPE html>
<html lang="id" class="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Laundry-IN</title>
    <script>
        (function() {
            var t = localStorage.getItem('laundry-in-theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (t === 'dark' || (!t && d)) document.documentElement.classList.add('dark');
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/reset.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/utilities.css') ?>">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--color-bg-base);
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: var(--space-4);
        }

        .auth-card {
            background-color: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            padding: var(--space-10);
            box-shadow: var(--shadow-lg);
        }

        .auth-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: var(--space-8);
        }

        .auth-brand-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--color-primary), #0F766E);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: var(--space-4);
        }

        .auth-brand-name {
            font-family: var(--font-display);
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--color-text-primary);
            letter-spacing: -0.03em;
        }

        .auth-brand-sub {
            font-size: var(--text-sm);
            color: var(--color-text-secondary);
            margin-top: 4px;
        }

        .auth-footer {
            text-align: center;
            margin-top: var(--space-6);
            font-size: var(--text-xs);
            color: var(--color-text-muted);
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <?= $content ?? '' ?>
        <div class="auth-footer">Laundry-IN &copy; <?= date('Y') ?> — Sistem Manajemen Laundry</div>
    </div>
    <script src="<?= base_url('assets/js/theme.js') ?>"></script>
</body>

</html>