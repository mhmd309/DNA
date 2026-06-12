<?php

/**
 * @var string $baseUrl
 * @var array $config
 * @var array|null $user
 * @var string $title
 * @var array $stats
 */
require_once dirname(__DIR__) . '/init.php';
?>
<div class="mb-8">
  <h1 class="text-2xl font-bold mb-2">لوحة التحكم</h1>
  <?php if ($user): ?>
    <div class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
      <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
        <i class="fas fa-user text-primary-600 dark:text-primary-400"></i>
      </div>
      <div>
        <p class="text-sm">نوع الحساب: <strong style="color: #3b82f6"><?= e($user['role'] === 'admin' ? 'مدير النظام' : ($user['role'] === 'editor' ? 'إدخال البيانات' : 'رؤية السجلات فقط')) ?></strong></p>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Intro -->
<div class="bg-gradient-to-l from-primary-600 to-indigo-700 rounded-2xl p-6 md:p-8 text-white mb-8 shadow-xl">
  <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
    <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center flex-shrink-0">
      <i class="fas fa-dna text-3xl"></i>
    </div>
    <div class="flex-1">
      <h2 class="text-xl font-bold mb-2">نظام إدارة العائلات وفحوصات DNA</h2>
      <p class="text-white/80 text-sm leading-relaxed">
        منصة متكاملة لإدارة بيانات العائلات والأفراد ونتائج فحوصات الحمض النووي للمفقودين ومجهولي الهوية والمتوفين.
        يوفر النظام أدوات بحث متقدمة، شجرة عائلة تفاعلية، وإدارة صلاحيات متعددة المستويات لضمان أمان البيانات.
      </p>
    </div>
  </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
  <?php
  $statCards = [
    ['key' => 'families', 'label' => 'إجمالي العائلات', 'icon' => 'fa-people-roof', 'color' => 'blue'],
    ['key' => 'dna_tests', 'label' => 'إجمالي فحوصات DNA', 'icon' => 'fa-dna', 'color' => 'indigo'],
    ['key' => 'individuals', 'label' => 'إجمالي الأفراد', 'icon' => 'fa-users', 'color' => 'cyan'],
    ['key' => 'missing', 'label' => 'إجمالي المفقودين', 'icon' => 'fa-person-walking', 'color' => 'amber'],
    ['key' => 'males', 'label' => 'إجمالي الذكور', 'icon' => 'fa-mars', 'color' => 'sky'],
    ['key' => 'females', 'label' => 'إجمالي الإناث', 'icon' => 'fa-venus', 'color' => 'pink'],
    ['key' => 'unidentified', 'label' => 'مجهولي الهوية', 'icon' => 'fa-user-secret', 'color' => 'purple'],
    ['key' => 'deceased', 'label' => 'إجمالي المتوفين', 'icon' => 'fa-user-minus', 'color' => 'gray'],
    ['key' => 'users', 'label' => 'إجمالي المستخدمين', 'icon' => 'fa-user-shield', 'color' => 'emerald'],
  ];
  foreach ($statCards as $card):
    $colors = statCardColorClasses($card['color']);
  ?>
    <div class="stat-card bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500 dark:text-gray-400"><?= e($card['label']) ?></p>
          <p class="text-2xl font-bold mt-1"><?= nf($stats[$card['key']]) ?></p>
        </div>
        <div class="w-12 h-12 rounded-xl <?= $colors['bg'] ?> flex items-center justify-center <?= $colors['text'] ?>">
          <i class="fas <?= e($card['icon']) ?> text-lg"></i>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Quick Links -->
<?php if (isset($user['role']) && $user['role'] !== 'viewer'): ?>
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="text-lg font-bold mb-4"><i class="fas fa-bolt text-amber-500 ml-2"></i>روابط سريعة</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
      <?php
      $links = [
        ['url' => '/families/create', 'label' => 'إضافة عائلة', 'icon' => 'fa-people-roof', 'perm' => 'families.create'],
        ['url' => '/individuals/create', 'label' => 'إضافة فرد', 'icon' => 'fa-user-plus', 'perm' => 'individuals.create'],
        ['url' => '/dna-tests/create', 'label' => 'إضافة فحص DNA', 'icon' => 'fa-dna', 'perm' => 'dna.create'],
        ['url' => '/individuals/create?status=missing', 'label' => 'إضافة مفقود', 'icon' => 'fa-person-walking', 'perm' => 'individuals.create'],
        ['url' => '/individuals/create?status=unidentified', 'label' => 'مجهول هوية', 'icon' => 'fa-user-secret', 'perm' => 'individuals.create'],
        ['url' => '/individuals/create?status=deceased', 'label' => 'إضافة متوفى', 'icon' => 'fa-user-minus', 'perm' => 'individuals.create'],
        ['url' => '/users/create', 'label' => 'إضافة مستخدم', 'icon' => 'fa-user-shield', 'perm' => 'users.create'],
      ];
      foreach ($links as $link):
        if (!can($link['perm'])) continue;
      ?>
        <a href="<?= $baseUrl . $link['url'] ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/10 transition text-center">
          <i class="fas <?= $link['icon'] ?> text-xl text-primary-600 dark:text-primary-400"></i>
          <span class="text-sm font-medium"><?= $link['label'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>