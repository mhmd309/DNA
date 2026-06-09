<?php

/**
 * @var string $baseUrl
 * @var string $title
 * @var array|null $test
 * @var string $action
 */
require_once dirname(__DIR__) . '/init.php';

$isEdit = !empty($test);
$t = $test ?? [];
$familyText = '';
if (!empty($t['family_name'])) {
  $familyText = $t['family_name'] . ' (' . ($t['family_code'] ?? '') . ')';
}
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold"><?= $isEdit ? 'تعديل فحص DNA' : 'إضافة فحص DNA' ?></h1>
</div>

<form id="dnaForm" action="<?= $baseUrl ?>/dna-tests/<?= $action ?>" method="POST" enctype="multipart/form-data" class="max-w-3xl">
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">اسم الشخص *</label>
        <input type="text" name="person_name" value="<?= e($t['person_name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">رقم العينة *</label>
        <input type="text" name="sample_number" value="<?= e($t['sample_number'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 font-mono">
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">العائلة</label>
        <div class="searchable-select relative" data-api="<?= $baseUrl ?>/api/families/search">
          <input type="hidden" name="family_id" class="ss-hidden" value="<?= e($t['family_id'] ?? '') ?>">
          <input type="text" class="ss-input w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50" placeholder="ابحث عن عائلة..." value="<?= e($familyText) ?>">
          <div class="ss-dropdown searchable-select-dropdown hidden absolute top-full mt-1 w-full bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50"></div>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">تاريخ سحب العينة</label>
        <input type="date" name="sample_date" value="<?= e($t['sample_date'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">الحالة *</label>
        <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
          <option value="pending" <?= ($t['status'] ?? '') === 'pending' ? 'selected' : '' ?>>قيد الانتظار</option>
          <option value="completed" <?= ($t['status'] ?? '') === 'completed' ? 'selected' : '' ?>>مكتمل</option>
          <option value="failed" <?= ($t['status'] ?? '') === 'failed' ? 'selected' : '' ?>>فشل</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">اسم المختبر</label>
        <input type="text" name="lab_name" value="<?= e($t['lab_name'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">مكان المختبر</label>
        <input type="text" name="lab_location" value="<?= e($t['lab_location'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">اسم الدكتور المسؤول</label>
        <input type="text" name="doctor_name" value="<?= e($t['doctor_name'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">ملخص النتيجة</label>
        <textarea name="result_summary" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50"><?= e($t['result_summary'] ?? '') ?></textarea>
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">مرفقات (PDF, JPG, PNG, WEBP)</label>
        <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-sm">
        <?php if (!empty($t['attachments'])): ?>
          <div class="mt-2 space-y-1">
            <?php foreach ($t['attachments'] as $att): ?>
              <div class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-paperclip"></i> <?= e($att['file_name']) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="flex gap-3 mt-6">
    <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-semibold transition"><i class="fas fa-save ml-2"></i> حفظ</button>
    <a href="<?= $baseUrl ?>/dna-tests" class="px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">إلغاء</a>
  </div>
</form>

<script src="<?= $baseUrl ?>/public/assets/js/searchable-select.js"></script>
<script>
  document.getElementById('dnaForm').addEventListener('submit', e => {
    e.preventDefault();
    submitForm(e.target);
  });
</script>