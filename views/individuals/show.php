<?php
/**
 * @var string $baseUrl
 * @var string $title
 * @var array $individual
 */
require_once dirname(__DIR__) . '/init.php';
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold"><?= e($individual['name']) ?></h1>
        <div class="mt-1"><?= statusBadge($individual['status']) ?></div>
    </div>
    <div class="flex gap-2">
        <?php if (can('individuals.edit')): ?>
        <a href="<?= $baseUrl ?>/individuals/edit/<?= $individual['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-medium transition"><i class="fas fa-edit"></i> تعديل</a>
        <?php endif; ?>
        <a href="<?= $baseUrl ?>/individuals" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition"><i class="fas fa-arrow-right"></i> رجوع</a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
        <div><span class="text-gray-500 block mb-1">الرقم القومي</span><span class="font-mono font-medium"><?= e($individual['national_id'] ?? '-') ?></span></div>
        <div><span class="text-gray-500 block mb-1">رقم عينة DNA</span><span class="font-mono font-medium"><?= e($individual['dna_sample_number'] ?? '-') ?></span></div>
        <div><span class="text-gray-500 block mb-1">فصيلة الدم</span><span class="font-medium"><?= e($individual['blood_type'] ?? '-') ?></span></div>
        <div><span class="text-gray-500 block mb-1">تاريخ الميلاد</span><span class="font-medium"><?= e($individual['birth_date'] ?? '-') ?><?php if ($individual['birth_date']): ?> (<?= calcAge($individual['birth_date']) ?> سنة)<?php endif; ?></span></div>
        <div><span class="text-gray-500 block mb-1">الجنس</span><span class="font-medium"><?= genderLabel($individual['gender']) ?></span></div>
        <div><span class="text-gray-500 block mb-1">العائلة</span>
            <?php if ($individual['family_id']): ?>
            <a href="<?= $baseUrl ?>/families/show/<?= $individual['family_id'] ?>" class="text-primary-600 hover:underline font-medium"><?= e($individual['family_name']) ?> (<?= e($individual['family_code']) ?>)</a>
            <?php else: ?>
            <span>-</span>
            <?php endif; ?>
        </div>
        <div><span class="text-gray-500 block mb-1">تاريخ التسجيل</span><span class="font-medium"><?= e(formatDateTime($individual['created_at'])) ?></span></div>
    </div>
</div>
