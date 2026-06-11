<?php

/**
 * @var string $baseUrl
 * @var string $title
 * @var array|null $userData
 * @var string $action
 */
require_once dirname(__DIR__) . '/init.php';

$isEdit = !empty($userData);
$u = $userData ?? [];
$roles = ['admin' => 'مدير النظام', 'manager' => 'مدير', 'data_entry' => 'إدخال بيانات', 'viewer' => 'مشاهد'];
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold"><?= $isEdit ? 'تعديل مستخدم' : 'إضافة مستخدم' ?></h1>
</div>

<form id="userForm" action="<?= $baseUrl ?>/users/<?= $action ?>" method="POST" enctype="multipart/form-data" class="max-w-2xl">
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">الاسم *</label>
        <input type="text" name="name" value="<?= e($u['name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">البريد الإلكتروني *</label>
        <input type="email" name="email" value="<?= e($u['email'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">كلمة المرور <?= $isEdit ? '(اتركها فارغة للإبقاء)' : '*' ?></label>
        <input type="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="6" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">الدور *</label>
        <select name="role" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
          <?php foreach ($roles as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($u['role'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if ($isEdit): ?>
        <div class="sm:col-span-2">
          <label class="flex items-center gap-3 cursor-pointer">
            <div class="switch">
              <input type="checkbox" name="is_active" value="1" <?= ($u['is_active'] ?? 1) ? 'checked' : '' ?>>
              <span class="slider"></span>
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">الحساب نشط</span>
          </label>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="flex gap-3 mt-6">
    <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-semibold transition"><i class="fas fa-save ml-2"></i> حفظ</button>
    <a href="<?= $baseUrl ?>/users" class="px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">إلغاء</a>
  </div>
</form>

<script>
  document.getElementById('userForm').addEventListener('submit', e => {
    e.preventDefault();
    submitForm(e.target);
  });
</script>