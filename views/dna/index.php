<?php

/**
 * @var string $baseUrl
 * @var array $config
 * @var array|null $user
 * @var string $title
 * @var array $result
 * @var string $search
 */
require_once dirname(__DIR__) . '/init.php';
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-bold">فحوصات DNA</h1>
    <p class="text-sm text-gray-500">إجمالي السجلات: <span class="font-semibold text-primary-600"><?= nf($result['total']) ?></span></p>
  </div>
  <?php if (can('dna.create')): ?>
    <a href="<?= $baseUrl ?>/dna-tests/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition">
      <i class="fas fa-plus"></i> إضافة فحص
    </a>
  <?php endif; ?>
</div>

<form method="GET" action="<?= $baseUrl ?>/dna-tests" data-instant-search class="mb-4">
  <div class="relative max-w-md">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="بحث..."
      class="w-full pr-10 pl-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary-500">
    <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
  </div>
</form>

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
  <div class="table-responsive">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-700/50">
        <tr>
          <th class="px-4 py-3 text-center font-semibold">#</th>
          <th class="px-4 py-3 text-center font-semibold">اسم الشخص</th>
          <th class="px-4 py-3 text-center font-semibold">رقم العينة</th>
          <th class="px-4 py-3 text-center font-semibold hidden lg:table-cell">المختبر</th>
          <th class="px-4 py-3 text-center font-semibold">الحالة</th>
          <th class="px-4 py-3 text-center font-semibold hidden md:table-cell">تاريخ التسجيل</th>
          <th class="px-4 py-3 text-center font-semibold">الإجراءات</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-center">
        <?php if (empty($result['data'])): ?>
          <tr>
            <td colspan="7" class="px-4 py-8 text-center text-gray-500">لا توجد فحوصات</td>
          </tr>
        <?php else: ?>
          <?php foreach ($result['data'] as $i => $row): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
              <td class="px-4 py-3"><?= ($result['current_page'] - 1) * $result['per_page'] + $i + 1 ?></td>
              <td class="px-4 py-3 font-medium"><?= e($row['person_name']) ?></td>
              <td class="px-4 py-3 font-mono text-xs"><?= e($row['sample_number']) ?></td>
              <td class="px-4 py-3 hidden lg:table-cell"><?= e($row['lab_name'] ?? '-') ?></td>
              <td class="px-4 py-3"><?= statusBadge($row['status']) ?></td>
              <td class="px-4 py-3 hidden md:table-cell text-xs"><?= e(formatDateTime($row['created_at'])) ?></td>
              <td class="px-4 py-3">
                <div class="flex justify-center items-center gap-1">
                  <a href="<?= $baseUrl ?>/dna-tests/show/<?= $row['id'] ?>" class="p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600"><i class="fas fa-eye"></i></a>
                  <?php if (can('dna.edit')): ?>
                    <a href="<?= $baseUrl ?>/dna-tests/edit/<?= $row['id'] ?>" class="p-2 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 text-amber-600"><i class="fas fa-edit"></i></a>
                  <?php endif; ?>
                  <?php if (can('dna.delete')): ?>
                    <button data-delete="<?= $baseUrl ?>/dna-tests/delete/<?= $row['id'] ?>" data-name="<?= e($row['sample_number']) ?>" class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600"><i class="fas fa-trash"></i></button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= paginationLinks($result, $baseUrl . '/dna-tests', $search) ?>