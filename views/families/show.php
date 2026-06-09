<?php

/**
 * @var string $baseUrl
 * @var array $config
 * @var array|null $user
 * @var string $title
 * @var array $family
 */
require_once dirname(__DIR__) . '/init.php';
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-bold"><?= e($family['family_name']) ?></h1>
    <p class="text-sm text-gray-500">كود العائلة: <span class="font-mono font-semibold text-primary-600"><?= e($family['family_code']) ?></span></p>
  </div>
  <div class="flex gap-2">
    <a href="<?= $baseUrl ?>/families/tree/<?= $family['id'] ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition">
      <i class="fas fa-sitemap"></i> شجرة العائلة
    </a>
    <?php if (can('families.edit')): ?>
      <a href="<?= $baseUrl ?>/families/edit/<?= $family['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-medium transition">
        <i class="fas fa-edit"></i> تعديل
      </a>
    <?php endif; ?>
    <a href="<?= $baseUrl ?>/families" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
      <i class="fas fa-arrow-right"></i> رجوع
    </a>
  </div>
</div>

<?php if (!empty($family['notes'])): ?>
  <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-6">
    <div class="text-sm font-medium text-amber-800 dark:text-amber-300 mb-1"><i class="fas fa-sticky-note ml-1"></i> ملاحظات</div>
    <p class="text-sm text-amber-700 dark:text-amber-400"><?= e($family['notes']) ?></p>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
  <?php renderMemberCard($family['father'] ?? null, 'father', 'blue'); ?>
  <?php renderMemberCard($family['mother'] ?? null, 'mother', 'pink'); ?>
</div>

<?php if (!empty($family['children'])): ?>
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 font-bold">
      <i class="fas fa-children text-green-600 ml-2"></i> الأبناء (<?= count($family['children']) ?>)
    </div>
    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
      <?php foreach ($family['children'] as $child): ?>
        <?php renderMemberCard($child, 'child', 'green'); ?>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>