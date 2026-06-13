<?php

/**
 * @var string $baseUrl
 * @var string $title
 * @var array|null $individual
 * @var string $action
 * @var string $presetStatus
 */
require_once dirname(__DIR__) . '/init.php';

$isEdit = !empty($individual);
$ind = $individual ?? [];
$bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$statuses = ['normal' => 'عادي', 'missing' => 'مفقود', 'unidentified' => 'مجهول هوية', 'deceased' => 'متوفى'];
$currentStatus = $presetStatus ?? ($ind['status'] ?? 'normal');
$familyText = '';
if (!empty($ind['family_name'])) {
  $familyText = $ind['family_name'] . ' (' . ($ind['family_code'] ?? '') . ')';
}
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold"><?= $isEdit ? 'تعديل فرد' : 'إضافة فرد' ?></h1>
</div>

<form id="individualForm" action="<?= $baseUrl ?>/individuals/<?= $action ?>" method="POST" class="max-w-3xl">
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">اسم الفرد *</label>
        <input type="text" name="name" value="<?= e($ind['name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 focus:ring-2 focus:ring-primary-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">الرقم القومي</label>
        <input type="text" name="national_id" value="<?= e($ind['national_id'] ?? '') ?>" <?= nationalIdAttrs() ?> class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 font-mono">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">فصيلة الدم</label>
        <select name="blood_type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
          <option value="">--</option>
          <?php foreach ($bloodTypes as $bt): ?>
            <option value="<?= $bt ?>" <?= ($ind['blood_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">تاريخ الميلاد</label>
        <input type="date" name="birth_date" id="birthDate" value="<?= e($ind['birth_date'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">الجنس *</label>
        <select name="gender" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
          <option value="male" <?= ($ind['gender'] ?? '') === 'male' ? 'selected' : '' ?>>ذكر</option>
          <option value="female" <?= ($ind['gender'] ?? '') === 'female' ? 'selected' : '' ?>>أنثى</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">الحالة *</label>
        <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
          <?php foreach ($statuses as $val => $label): ?>
            <option value="<?= $val ?>" <?= $currentStatus === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">العائلة (اختياري)</label>
        <div class="searchable-select relative" data-api="<?= $baseUrl ?>/api/families/search">
          <input type="hidden" name="family_id" class="ss-hidden" value="<?= e($ind['family_id'] ?? '') ?>">
          <input type="text" class="ss-input w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50" placeholder="ابحث عن عائلة..." value="<?= e($familyText) ?>">
          <div class="ss-dropdown searchable-select-dropdown hidden absolute top-full mt-1 w-full bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- DNA Markers Section -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-4 mt-6">
    <h2 class="text-lg font-bold mb-4">نتائج تحليل الحمض النووي (Markers)</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="px-3 py-2 text-center font-semibold">العلامة (Marker)</th>
            <th class="px-3 py-2 text-center font-semibold">الأليل 1 (Allele 1)</th>
            <th class="px-3 py-2 text-center font-semibold">الأليل 2 (Allele 2)</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr>
            <td class="px-3 py-2 text-center font-medium">D3S1358</td>
            <td class="px-3 py-2">
              <input type="text" name="D3S1358_1" value="<?= e($ind['D3S1358_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 15">
            </td>
            <td class="px-3 py-2">
              <input type="text" name="D3S1358_2" value="<?= e($ind['D3S1358_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 17">
            </td>
          </tr>
          <tr>
            <td class="px-3 py-2 text-center font-medium">vWA</td>
            <td class="px-3 py-2">
              <input type="text" name="vWA_1" value="<?= e($ind['vWA_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 16">
            </td>
            <td class="px-3 py-2">
              <input type="text" name="vWA_2" value="<?= e($ind['vWA_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 18">
            </td>
          </tr>
          <tr>
            <td class="px-3 py-2 text-center font-medium">FGA</td>
            <td class="px-3 py-2">
              <input type="text" name="FGA_1" value="<?= e($ind['FGA_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 21">
            </td>
            <td class="px-3 py-2">
              <input type="text" name="FGA_2" value="<?= e($ind['FGA_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 24">
            </td>
          </tr>
          <tr>
            <td class="px-3 py-2 text-center font-medium">D8S1179</td>
            <td class="px-3 py-2">
              <input type="text" name="D8S1179_1" value="<?= e($ind['D8S1179_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 10">
            </td>
            <td class="px-3 py-2">
              <input type="text" name="D8S1179_2" value="<?= e($ind['D8S1179_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 14">
            </td>
          </tr>
          <tr>
            <td class="px-3 py-2 text-center font-medium">D21S11</td>
            <td class="px-3 py-2">
              <input type="text" name="D21S11_1" value="<?= e($ind['D21S11_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 28">
            </td>
            <td class="px-3 py-2">
              <input type="text" name="D21S11_2" value="<?= e($ind['D21S11_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 30">
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="flex gap-3 mt-6">
    <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-semibold transition"><i class="fas fa-save ml-2"></i> حفظ</button>
    <a href="<?= $baseUrl ?>/individuals" class="px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">إلغاء</a>
  </div>
</form>

<script>
  document.getElementById('individualForm').addEventListener('submit', e => {
    e.preventDefault();
    submitForm(e.target);
  });
</script>