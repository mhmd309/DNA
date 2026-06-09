<?php

/**
 * @var string $baseUrl
 * @var array|null $user
 * @var string $title
 * @var array $families
 */
require_once dirname(__DIR__) . '/init.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-bold">تقرير العائلات</h1>
    <p class="text-sm text-gray-500">إجمالي: <span class="font-semibold text-primary-600"><?= nf(count($families)) ?></span> عائلة</p>
  </div>
  <a href="?export=excel" class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl font-medium transition">
    <i class="fas fa-download"></i> تنزيل Excel
  </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-700/50">
        <tr>
          <th class="px-4 py-3 text-center font-semibold">#</th>
          <th class="px-4 py-3 text-right font-semibold">اسم العائلة</th>
          <th class="px-4 py-3 text-center font-semibold">الكود</th>
          <th class="px-4 py-3 text-center font-semibold">الأعضاء</th>
          <th class="px-4 py-3 text-center font-semibold">أنشئ بواسطة</th>
          <th class="px-4 py-3 text-center font-semibold">التاريخ</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        <?php if (empty($families)): ?>
          <tr>
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">لا توجد عائلات</td>
          </tr>
        <?php else: ?>
          <?php foreach ($families as $i => $row): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
              <td class="px-4 py-3 text-center"><?= $i + 1 ?></td>
              <td class="px-4 py-3 text-right font-medium"><?= e($row['family_name']) ?></td>
              <td class="px-4 py-3 text-center">
                <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded text-xs"><?= e($row['family_code']) ?></span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs font-medium"><?= $row['members_count'] ?></span>
              </td>
              <td class="px-4 py-3 text-center"><?= e($row['created_by_name'] ?? 'غير محدد') ?></td>
              <td class="px-4 py-3 text-center text-gray-500 text-xs"><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
