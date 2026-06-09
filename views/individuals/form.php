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
$bloodTypes = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
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
                <label class="block text-sm font-medium mb-1">رقم عينة DNA</label>
                <input type="text" name="dna_sample_number" value="<?= e($ind['dna_sample_number'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 font-mono">
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
                    <p class="ss-selected-text text-xs text-gray-500 mt-1"><?= e($familyText) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-3 mt-6">
        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-semibold transition"><i class="fas fa-save ml-2"></i> حفظ</button>
        <a href="<?= $baseUrl ?>/individuals" class="px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">إلغاء</a>
    </div>
</form>

<script src="<?= $baseUrl ?>/public/assets/js/searchable-select.js"></script>
<script>
document.getElementById('individualForm').addEventListener('submit', e => { e.preventDefault(); submitForm(e.target); });
</script>
