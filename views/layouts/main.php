<?php

/**
 * @var string $baseUrl
 * @var array $config
 * @var array|null $user
 * @var string $title
 * @var string $content
 * @var string $scripts
 */
require_once dirname(__DIR__) . '/init.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'نظام DNA') ?> - <?= e($config['name']) ?></title>
  <link rel="icon" type="image/jpeg" href="<?= $baseUrl ?>/public/dnalogofavicon.jpg">
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/tailwind.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/vendor/font-awesome/css/all.min.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/app.css">
  <script>
    if (localStorage.getItem('dna_theme') === 'dark') document.documentElement.classList.add('dark');
    (function() {
      const open = localStorage.getItem('dna_sidebar') !== 'closed';
      document.documentElement.classList.toggle('sidebar-collapsed', !open);
    })();
  </script>
  <script>window.DNA_BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_UNICODE) ?>;</script>
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
  <?php require __DIR__ . '/../partials/navbar.php'; ?>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden"></div>

  <!-- Image Preview Popup -->
  <div id="imagePreviewPopup" class="fixed inset-0 bg-black/80 z-[9999] hidden flex items-center justify-center p-4">
    <button id="closeImagePreview" class="absolute top-4 left-4 text-white text-3xl hover:text-gray-300 transition">&times;</button>
    <img id="previewImage" src="" alt="معاينة" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
  </div>

  <main id="mainContent" class="pt-16 flex-1 transition-all duration-300 sidebar-open-main">
    <div class="p-4 md:p-6 lg:p-8 fade-in">
      <?= $content ?>
    </div>
  </main>

  <footer class="relative bg-slate-900 px-6 py-3 text-center transition-all duration-300 sidebar-open-main mt-auto border-t border-slate-800">
    <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-primary-500/50 to-transparent"></div>
    <div class="flex flex-col items-center justify-center gap-1.5 relative z-10">
      <div lang="ar" dir="rtl" class="text-[13px] text-slate-300">
        جميع الحقوق محفوظة لدى <span class="text-primary-400 font-semibold">شركة انتشار للتسويق والبرمجيات</span> <span dir="ltr" class="text-slate-400"><?= date('Y') ?></span> <span class="text-primary-500">©</span>
      </div>
      <div lang="en" dir="ltr" class="text-[11px] text-slate-500 tracking-wider font-medium">
        All rights reserved to <span class="text-slate-400">Enteshar for Marketing and Software</span> <?= date('Y') ?> ©
      </div>
    </div>
  </footer>


  <script src="<?= $baseUrl ?>/public/assets/js/app.js"></script>
  <script src="<?= $baseUrl ?>/public/assets/js/searchable-select.js"></script>
  <?php if ($scripts !== ''): ?><?= $scripts ?><?php endif; ?>
</body>

</html>