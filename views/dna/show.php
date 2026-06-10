<?php

/**
 * @var string $baseUrl
 * @var string $title
 * @var array $test
 */
require_once dirname(__DIR__) . '/init.php';
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-bold">فحص DNA: <?= e($test['person_name']) ?></h1>
    <div class="mt-1"><?= statusBadge($test['status']) ?></div>
  </div>
  <div class="flex gap-2">
    <?php if (can('dna.edit')): ?>
      <a href="<?= $baseUrl ?>/dna-tests/edit/<?= $test['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-medium transition"><i class="fas fa-edit"></i> تعديل</a>
    <?php endif; ?>
    <a href="<?= $baseUrl ?>/dna-tests" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition"><i class="fas fa-arrow-right"></i> رجوع</a>
  </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
    <div><span class="text-gray-500 block mb-1">اسم الشخص</span><span class="font-medium"><?= e($test['person_name']) ?></span></div>
    <!-- العائلة: تمت إزالتها من عرض الفحص -->
    <div><span class="text-gray-500 block mb-1">تاريخ سحب العينة</span><span><?= e($test['sample_date'] ?? '-') ?></span></div>
    <div><span class="text-gray-500 block mb-1">المختبر</span><span><?= e($test['lab_name'] ?? '-') ?></span></div>
    <div><span class="text-gray-500 block mb-1">مكان المختبر</span><span><?= e($test['lab_location'] ?? '-') ?></span></div>
    <div><span class="text-gray-500 block mb-1">الدكتور المسؤول</span><span><?= e($test['doctor_name'] ?? '-') ?></span></div>
    <div><span class="text-gray-500 block mb-1">تاريخ التسجيل</span><span><?= e(formatDateTime($test['created_at'])) ?></span></div>
    <?php if ($test['result_summary']): ?>
      <div class="sm:col-span-2"><span class="text-gray-500 block mb-1">ملخص النتيجة</span>
        <p class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4"><?= e($test['result_summary']) ?></p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- DNA Markers Display -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
  <h3 class="font-bold mb-4"><i class="fas fa-dna ml-2"></i> نتائج تحليل الحمض النووي</h3>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-700/50">
        <tr>
          <th class="px-4 py-3 text-center font-semibold">العلامة (Marker)</th>
          <th class="px-4 py-3 text-center font-semibold">الأليل 1 (Allele 1)</th>
          <th class="px-4 py-3 text-center font-semibold">الأليل 2 (Allele 2)</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
        <tr>
          <td class="px-4 py-3 text-center font-medium">D3S1358</td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['D3S1358_1'] ?? '-') ?></td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['D3S1358_2'] ?? '-') ?></td>
        </tr>
        <tr>
          <td class="px-4 py-3 text-center font-medium">vWA</td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['vWA_1'] ?? '-') ?></td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['vWA_2'] ?? '-') ?></td>
        </tr>
        <tr>
          <td class="px-4 py-3 text-center font-medium">FGA</td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['FGA_1'] ?? '-') ?></td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['FGA_2'] ?? '-') ?></td>
        </tr>
        <tr>
          <td class="px-4 py-3 text-center font-medium">D8S1179</td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['D8S1179_1'] ?? '-') ?></td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['D8S1179_2'] ?? '-') ?></td>
        </tr>
        <tr>
          <td class="px-4 py-3 text-center font-medium">D21S11</td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['D21S11_1'] ?? '-') ?></td>
          <td class="px-4 py-3 text-center font-mono"><?= e($test['D21S11_2'] ?? '-') ?></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<?php if (!empty($test['attachments'])): ?>
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <h3 class="font-bold mb-4"><i class="fas fa-paperclip ml-2"></i> المرفقات</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <?php foreach ($test['attachments'] as $att): ?>
        <a href="<?= uploadUrl($att['file_path']) ?>" target="_blank" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
          <i class="fas <?= $att['file_type'] === 'pdf' ? 'fa-file-pdf text-red-500' : 'fa-file-image text-blue-500' ?> text-xl"></i>
          <div>
            <div class="text-sm font-medium"><?= e($att['file_name']) ?></div>
            <div class="text-xs text-gray-500"><?= strtoupper($att['file_type']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>