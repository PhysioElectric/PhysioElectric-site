<?php
/**
 * Admin login page (standalone layout — no sidebar).
 * Expects: $error (string|null), $email (optional)
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e(t('admin.login.title')) ?> | <?= e(setting('site_name', 'PhysioElectric')) ?></title>
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/assets/fonts/fonts.css">
<script src="/assets/js/tailwind.js"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            fontFamily: { sans: ['Inter', 'Vazirmatn', 'sans-serif'] },
            colors: {
                physio: {
                    50: '#f0f9ff', 100: '#e0f2fe', 200: '#bae6fd', 300: '#7dd3fc',
                    400: '#38bdf8', 500: '#0ea5e9', 600: '#0284c7', 900: '#0f172a', 950: '#020617',
                }
            }
        }
    }
}
</script>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="antialiased font-sans">
<div class="min-h-screen flex items-center justify-center bg-slate-950 relative overflow-hidden px-4">
    <div class="absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 15% 20%, rgba(14,165,233,0.25) 0, transparent 42%), radial-gradient(circle at 85% 80%, rgba(59,130,246,0.22) 0, transparent 45%);"></div>

    <div class="relative w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-physio-500 to-physio-900 flex items-center justify-center text-white font-bold text-3xl shadow-lg">P</div>
            <h1 class="mt-5 text-2xl font-bold text-white"><?= e(t('admin.login.title')) ?></h1>
            <p class="mt-2 text-sm text-slate-400"><?= e(t('admin.login.sub')) ?></p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <?php if (!empty($error)): ?>
                <div class="flash-banner flash-error mb-5">
                    <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="/admin/login" class="space-y-5">
                <?= Csrf::field() ?>
                <div>
                    <label for="email" class="admin-label"><?= e(t('admin.login.email')) ?></label>
                    <input type="email" id="email" name="email" value="<?= e($email ?? '') ?>"
                           class="admin-input" placeholder="you@example.com" dir="ltr"
                           autocomplete="username" required>
                </div>
                <div>
                    <label for="password" class="admin-label"><?= e(t('admin.login.pass')) ?></label>
                    <input type="password" id="password" name="password"
                           class="admin-input" placeholder="••••••••" dir="ltr"
                           autocomplete="current-password" required>
                </div>
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-physio-900 hover:bg-physio-950 rounded-xl transition-all hover:-translate-y-0.5">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <?= e(t('admin.login.submit')) ?>
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            <a href="/" class="hover:text-slate-300 transition-colors"><?= e(t('admin.viewSite')) ?> ←</a>
        </p>
    </div>
</div>

<script src="/assets/js/lucide.min.js"></script>
<script>document.addEventListener('DOMContentLoaded', () => lucide.createIcons());</script>
</body>
</html>
