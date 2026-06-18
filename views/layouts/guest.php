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
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/tailwind.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/vendor/font-awesome/css/all.min.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/app.css">
  <script>
    if (localStorage.getItem('dna_theme') === 'dark') document.documentElement.classList.add('dark');
  </script>
  <script>window.DNA_BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_UNICODE) ?>;</script>
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
  <?= $content ?>
  <script src="<?= $baseUrl ?>/public/assets/js/app.js"></script>
</body>

</html>