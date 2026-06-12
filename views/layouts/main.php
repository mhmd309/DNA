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
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#F0F0FF',
              100: '#E1E1FF',
              200: '#C2C3FF',
              300: '#A4A5FF',
              400: '#8586FF',
              500: '#6367FF',
              600: '#4D51E6',
              700: '#363AC0',
              800: '#1F2299',
              900: '#080B73'
            }
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/app.css">
  <script>
    if (localStorage.getItem('dna_theme') === 'dark') document.documentElement.classList.add('dark');
    (function() {
      const open = localStorage.getItem('dna_sidebar') !== 'closed';
      document.documentElement.classList.toggle('sidebar-collapsed', !open);
    })();
  </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
  <?php require __DIR__ . '/../partials/navbar.php'; ?>
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden"></div>

  <main id="mainContent" class="pt-16 min-h-screen transition-all duration-300 sidebar-open-main">
    <div class="p-4 md:p-6 lg:p-8 fade-in">
      <?= $content ?>
    </div>
  </main>

  <script src="<?= $baseUrl ?>/public/assets/js/app.js"></script>
  <?php if ($scripts !== ''): ?><?= $scripts ?><?php endif; ?>
</body>

</html>