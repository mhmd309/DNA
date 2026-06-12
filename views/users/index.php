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
    <h1 class="text-2xl font-bold">المستخدمون</h1>
    <p class="text-sm text-gray-500">إجمالي السجلات: <span class="font-semibold text-primary-600"><?= nf($result['total']) ?></span></p>
  </div>
  <?php if (can('users.create')): ?>
    <a href="<?= $baseUrl ?>/users/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition">
      <i class="fas fa-plus"></i> إضافة مستخدم
    </a>
  <?php endif; ?>
</div>

<form method="GET" action="<?= $baseUrl ?>/users" data-instant-search class="mb-4">
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
          <th class="px-4 py-3 text-center font-semibold">الاسم</th>
          <th class="px-4 py-3 text-center font-semibold">البريد</th>
          <th class="px-4 py-3 text-center font-semibold">الدور</th>
          <th class="px-4 py-3 text-center font-semibold">الحالة</th>
          <th class="px-4 py-3 text-center font-semibold">الإجراءات</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-center">
        <?php if (empty($result['data'])): ?>
          <tr>
            <td colspan="6" class="px-4 py-8 text-center text-gray-500">لا يوجد مستخدمون</td>
          </tr>
        <?php else: ?>
          <?php foreach ($result['data'] as $i => $row): ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
              <td class="px-4 py-3"><?= ($result['current_page'] - 1) * $result['per_page'] + $i + 1 ?></td>
              <td class="px-4 py-3">
                <span class="font-medium"><?= e($row['name']) ?></span>
              </td>
              <td class="px-4 py-3"><?= e($row['email']) ?></td>
              <td class="px-4 py-3"><span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded text-xs"><?= roleLabel($row['role']) ?></span></td>
              <td class="px-4 py-3">
                <?php if ($row['is_active']): ?>
                  <span class="text-green-600 text-xs">نشط</span>
                <?php else: ?>
                  <span class="text-red-600 text-xs">معطل</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-center items-center gap-2">
                  <?php if (can('users.edit')): ?>
                    <a href="<?= $baseUrl ?>/users/edit/<?= $row['id'] ?>" class="action-btn action-btn-edit" title="تعديل"><i class="fas fa-edit"></i></a>
                  <?php endif; ?>
                  <?php if (can('users.delete') && $row['id'] != ($user['id'] ?? 0)): ?>
                    <button data-delete="<?= $baseUrl ?>/users/delete/<?= $row['id'] ?>" data-name="<?= e($row['name']) ?>" class="action-btn action-btn-delete" title="حذف"><i class="fas fa-trash"></i></button>
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

<?= paginationLinks($result, $baseUrl . '/users', $search) ?>