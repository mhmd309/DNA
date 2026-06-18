<?php

/**
 * @var string $baseUrl
 * @var string $title
 * @var array $family
 */
require_once dirname(__DIR__) . '/init.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>شجرة عائلة <?= e($family['family_name']) ?></title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/tailwind.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/vendor/font-awesome/css/all.min.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/public/assets/css/app.css">
  <script>
    if (localStorage.getItem('dna_theme') === 'dark') document.documentElement.classList.add('dark');
  </script>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen p-8 transition-colors">
  <div class="text-center mb-8">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">شجرة عائلة <?= e($family['family_name']) ?></h1>
    <p class="text-gray-500 dark:text-gray-400 font-mono"><?= e($family['family_code']) ?></p>
  </div>

  <div class="family-tree">
    <div class="tree-couple">
      <?php if ($family['father']): ?>
        <div class="tree-node">
          <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400"><i class="fas fa-mars text-xl"></i></div>
          <div class="font-bold"><?= e($family['father']['name']) ?></div>
          <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">الأب</div>
          <?php if ($family['father']['birth_date']): ?><div class="text-xs text-gray-400 dark:text-gray-500"><?= calcAge($family['father']['birth_date']) ?> سنة</div><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ($family['mother']): ?>
        <div class="tree-node">
          <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center text-pink-600 dark:text-pink-400"><i class="fas fa-venus text-xl"></i></div>
          <div class="font-bold"><?= e($family['mother']['name']) ?></div>
          <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">الأم</div>
          <?php if ($family['mother']['birth_date']): ?><div class="text-xs text-gray-400 dark:text-gray-500"><?= calcAge($family['mother']['birth_date']) ?> سنة</div><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($family['children'])): ?>
      <div class="tree-children">
        <?php foreach ($family['children'] as $child): ?>
          <div class="tree-node tree-child-node">
            <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
              <i class="fas <?= $child['gender'] === 'female' ? 'fa-venus' : 'fa-mars' ?>"></i>
            </div>
            <div class="font-bold text-sm"><?= e($child['name']) ?></div>
            <?php if ($child['birth_date']): ?><div class="text-xs text-gray-400 dark:text-gray-500"><?= calcAge($child['birth_date']) ?> سنة</div><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="text-center mt-8">
    <button onclick="window.print()" class="px-6 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition">
      <i class="fas fa-print ml-2"></i> طباعة
    </button>
  </div>
</body>

</html>
