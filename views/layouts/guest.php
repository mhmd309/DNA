<?php

/**
 * @var string $baseUrl
 * @var array $config
 * @var string $title
 * @var string $content
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
  </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
  <?= $content ?>
  <script src="<?= $baseUrl ?>/public/assets/js/app.js"></script>
  
  <!-- Floating WhatsApp Button -->
  <a href="https://wa.me/201024704900" target="_blank" class="whatsapp-float" title="تواصل معنا">
    <i class="fab fa-whatsapp text-3xl"></i>
  </a>
</body>

</html>