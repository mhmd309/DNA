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
    <h1 class="text-2xl font-bold">الأفراد</h1>
    <p class="text-sm text-gray-500">إجمالي السجلات: <span class="font-semibold text-primary-600"><?= nf($result['total']) ?></span></p>
  </div>
  <?php if (can('individuals.create')): ?>
    <a href="<?= $baseUrl ?>/individuals/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition">
      <i class="fas fa-plus"></i> إضافة فرد
    </a>
  <?php endif; ?>
</div>

<form method="GET" action="<?= $baseUrl ?>/individuals" data-instant-search class="mb-4">
  <div class="relative max-w-md">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="بحث بالاسم أو الرقم القومي..."
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
          <th class="px-4 py-3 text-center font-semibold">اسم الفرد</th>
          <th class="px-4 py-3 text-center font-semibold">الرقم القومي</th>
          <th class="px-4 py-3 text-center font-semibold hidden md:table-cell">فصيلة الدم</th>
          <th class="px-4 py-3 text-center font-semibold hidden md:table-cell">تاريخ الميلاد</th>
          <th class="px-4 py-3 text-center font-semibold">الجنس</th>
          <th class="px-4 py-3 text-center font-semibold hidden lg:table-cell">العائلة</th>
          <th class="px-4 py-3 text-center font-semibold">الحالة</th>
          <th class="px-4 py-3 text-center font-semibold">الإجراءات</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-center">
        <?php if (empty($result['data'])): ?>
          <tr>
            <td colspan="9" class="px-4 py-8 text-center text-gray-500">لا يوجد أفراد</td>
          </tr>
        <?php else: ?>
          <?php foreach ($result['data'] as $i => $row): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
              <td class="px-4 py-3"><?= ($result['current_page'] - 1) * $result['per_page'] + $i + 1 ?></td>
              <td class="px-4 py-3 font-medium"><?= e($row['name']) ?></td>
              <td class="px-4 py-3 font-mono text-xs"><?= e($row['national_id'] ?? '-') ?></td>
              <td class="px-4 py-3 hidden md:table-cell"><?= e($row['blood_type'] ?? '-') ?></td>
              <td class="px-4 py-3 hidden md:table-cell"><?= e($row['birth_date'] ?? '-') ?></td>
              <td class="px-4 py-3"><?= genderLabel($row['gender']) ?></td>
              <td class="px-4 py-3 hidden lg:table-cell"><?= e($row['family_name'] ?? '-') ?></td>
              <td class="px-4 py-3"><?= statusBadge($row['status']) ?></td>
              <td class="px-4 py-3">
                <div class="flex justify-center items-center gap-2">
                  <a href="<?= $baseUrl ?>/individuals/show/<?= $row['id'] ?>" class="action-btn action-btn-view" title="عرض"><i class="fas fa-eye"></i></a>
                  <?php if (can('individuals.edit')): ?>
                    <a href="<?= $baseUrl ?>/individuals/edit/<?= $row['id'] ?>" class="action-btn action-btn-edit" title="تعديل"><i class="fas fa-edit"></i></a>
                  <?php endif; ?>
                  <?php if (can('individuals.delete')): ?>
                    <button data-delete="<?= $baseUrl ?>/individuals/delete/<?= $row['id'] ?>" data-name="<?= e($row['name']) ?>" class="action-btn action-btn-delete" title="حذف"><i class="fas fa-trash"></i></button>
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

<?= paginationLinks($result, $baseUrl . '/individuals', $search) ?>