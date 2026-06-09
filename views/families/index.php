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
    <h1 class="text-2xl font-bold">العائلات</h1>
    <p class="text-sm text-gray-500">إجمالي السجلات: <span class="font-semibold text-primary-600"><?= nf($result['total']) ?></span></p>
  </div>
  <?php if (can('families.create')): ?>
    <a href="<?= $baseUrl ?>/families/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition shadow-sm">
      <i class="fas fa-plus"></i> إضافة عائلة
    </a>
  <?php endif; ?>
</div>

<form method="GET" action="<?= $baseUrl ?>/families" data-instant-search class="mb-4">
  <div class="relative max-w-md">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="بحث بالاسم أو الكود..."
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
          <th class="px-4 py-3 text-center font-semibold">اسم العائلة</th>
          <th class="px-4 py-3 text-center font-semibold">كود العائلة</th>
          <th class="px-4 py-3 text-center font-semibold hidden lg:table-cell">اسم الأب</th>
          <th class="px-4 py-3 text-center font-semibold hidden xl:table-cell">رقم الأب</th>
          <th class="px-4 py-3 text-center font-semibold hidden xl:table-cell">عينة الأب</th>
          <th class="px-4 py-3 text-center font-semibold hidden lg:table-cell">اسم الأم</th>
          <th class="px-4 py-3 text-center font-semibold hidden xl:table-cell">رقم الأم</th>
          <th class="px-4 py-3 text-center font-semibold hidden xl:table-cell">عينة الأم</th>
          <th class="px-4 py-3 text-center font-semibold">الأبناء</th>
          <th class="px-4 py-3 text-center font-semibold">الإجراءات</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-center">
        <?php if (empty($result['data'])): ?>
          <tr>
            <td colspan="11" class="px-4 py-8 text-center text-gray-500">لا توجد عائلات</td>
          </tr>
        <?php else: ?>
          <?php foreach ($result['data'] as $i => $row): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
              <td class="px-4 py-3"><?= ($result['current_page'] - 1) * $result['per_page'] + $i + 1 ?></td>
              <td class="px-4 py-3 font-medium"><?= e($row['family_name']) ?></td>
              <td class="px-4 py-3"><span class="px-2 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded text-xs font-mono"><?= e($row['family_code']) ?></span></td>
              <td class="px-4 py-3 hidden lg:table-cell"><?= e($row['father_name'] ?? '-') ?></td>
              <td class="px-4 py-3 hidden xl:table-cell font-mono text-xs"><?= e($row['father_national_id'] ?? '-') ?></td>
              <td class="px-4 py-3 hidden xl:table-cell font-mono text-xs"><?= e($row['father_dna_sample'] ?? '-') ?></td>
              <td class="px-4 py-3 hidden lg:table-cell"><?= e($row['mother_name'] ?? '-') ?></td>
              <td class="px-4 py-3 hidden xl:table-cell font-mono text-xs"><?= e($row['mother_national_id'] ?? '-') ?></td>
              <td class="px-4 py-3 hidden xl:table-cell font-mono text-xs"><?= e($row['mother_dna_sample'] ?? '-') ?></td>
              <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded-full text-xs"><?= nf((int)$row['children_count']) ?></span></td>
              <td class="px-4 py-3">
                <div class="flex justify-center items-center gap-1">
                  <a href="<?= $baseUrl ?>/families/show/<?= $row['id'] ?>" class="p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600" title="عرض"><i class="fas fa-eye"></i></a>
                  <?php if (can('families.edit')): ?>
                    <a href="<?= $baseUrl ?>/families/edit/<?= $row['id'] ?>" class="p-2 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 text-amber-600" title="تعديل"><i class="fas fa-edit"></i></a>
                  <?php endif; ?>
                  <?php if (can('families.delete')): ?>
                    <button data-delete="<?= $baseUrl ?>/families/delete/<?= $row['id'] ?>" data-name="<?= e($row['family_name']) ?>" class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600" title="حذف"><i class="fas fa-trash"></i></button>
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

<?= paginationLinks($result, $baseUrl . '/families', $search) ?>