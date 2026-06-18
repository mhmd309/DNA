<?php

/**
 * @var string $baseUrl
 * @var array $config
 * @var string $title
 * @var array|null $family
 * @var string $action
 */
require_once dirname(__DIR__) . '/init.php';

$isEdit = !empty($family);
$f = $family ?? [];
$father = $f['father'] ?? [];
$mother = $f['mother'] ?? [];
$children = $f['children'] ?? [];
$bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold"><?= $isEdit ? 'تعديل عائلة' : 'إضافة عائلة' ?></h1>
</div>

<form id="familyForm" action="<?= $baseUrl ?>/families/<?= $action ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
  <?= csrf_field() ?>
  <!-- Section 1 -->
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-bold mb-4 flex items-center gap-2"><span class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 text-sm">1</span> بيانات العائلة</h2>
    <div class="grid grid-cols-1 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">اسم العائلة *</label>
        <input type="text" name="family_name" value="<?= e($f['family_name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 focus:ring-2 focus:ring-primary-500">
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">ملاحظات</label>
        <textarea name="notes" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 focus:ring-2 focus:ring-primary-500"><?= e($f['notes'] ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Section 2: Father -->
  <div class="bg-white mt-4 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-bold mb-4 flex items-center gap-2"><span class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 text-sm"><i class="fas fa-mars"></i></span> بيانات الأب</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <input type="hidden" name="father_id" value="<?= e($father['id'] ?? '') ?>">
      <input type="hidden" name="father_id_card_image" value="<?= e($father['id_card_image'] ?? '') ?>">
      <div><label class="block text-sm font-medium mb-1">اسم الأب *</label><input type="text" name="father_name" value="<?= e($father['name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50"></div>
      <div><label class="block text-sm font-medium mb-1">الرقم القومي</label><input type="text" name="father_national_id" value="<?= e($father['national_id'] ?? '') ?>" <?= nationalIdAttrs() ?> class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 font-mono"></div>
      <div><label class="block text-sm font-medium mb-1">فصيلة الدم</label><select name="father_blood_type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
          <option value="">--</option><?php foreach ($bloodTypes as $bt): ?><option value="<?= $bt ?>" <?= ($father['blood_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option><?php endforeach; ?>
        </select></div>
      <div><label class="block text-sm font-medium mb-1">رقم الهاتف</label><input type="text" name="father_phone" value="<?= e($father['phone'] ?? '') ?>" <?= phoneAttrs() ?> class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50"></div>
      <div><label class="block text-sm font-medium mb-1">تاريخ الميلاد</label><input type="date" name="father_birth_date" value="<?= e($father['birth_date'] ?? '') ?>" data-age-calc="#fatherAge" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50"></div>
      <div><label class="block text-sm font-medium mb-1">العمر</label><input type="text" id="fatherAge" readonly value="<?= calcAge($father['birth_date'] ?? null) ?? '' ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700"></div>
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1">العنوان</label><input type="text" name="father_address" value="<?= e($father['address'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50"></div>
      <div><label class="block text-sm font-medium mb-1">صورة البطاقة</label><input type="file" name="father_id_card" accept="image/*" class="w-full text-sm"><?php if (!empty($father['id_card_image'])): ?><img src="<?= uploadUrl($father['id_card_image']) ?>" class="mt-2 h-16 rounded border"><?php endif; ?></div>
    </div>
    <!-- DNA Markers for Father -->
    <div class="mt-6">
      <h3 class="text-md font-semibold mb-3 text-blue-700 dark:text-blue-400">نتائج تحليل الحمض النووي للأب</h3>
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
              <td class="px-3 py-2"><input type="text" name="father_D3S1358_1" value="<?= e($father['D3S1358_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 15"></td>
              <td class="px-3 py-2"><input type="text" name="father_D3S1358_2" value="<?= e($father['D3S1358_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 17"></td>
            </tr>
            <tr>
              <td class="px-3 py-2 text-center font-medium">vWA</td>
              <td class="px-3 py-2"><input type="text" name="father_vWA_1" value="<?= e($father['vWA_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 16"></td>
              <td class="px-3 py-2"><input type="text" name="father_vWA_2" value="<?= e($father['vWA_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 18"></td>
            </tr>
            <tr>
              <td class="px-3 py-2 text-center font-medium">FGA</td>
              <td class="px-3 py-2"><input type="text" name="father_FGA_1" value="<?= e($father['FGA_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 21"></td>
              <td class="px-3 py-2"><input type="text" name="father_FGA_2" value="<?= e($father['FGA_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 24"></td>
            </tr>
            <tr>
              <td class="px-3 py-2 text-center font-medium">D8S1179</td>
              <td class="px-3 py-2"><input type="text" name="father_D8S1179_1" value="<?= e($father['D8S1179_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 10"></td>
              <td class="px-3 py-2"><input type="text" name="father_D8S1179_2" value="<?= e($father['D8S1179_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 14"></td>
            </tr>
            <tr>
              <td class="px-3 py-2 text-center font-medium">D21S11</td>
              <td class="px-3 py-2"><input type="text" name="father_D21S11_1" value="<?= e($father['D21S11_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 28"></td>
              <td class="px-3 py-2"><input type="text" name="father_D21S11_2" value="<?= e($father['D21S11_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 30"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Section 2: Mother -->
  <div class="bg-white mt-4 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-lg font-bold mb-4 flex items-center gap-2"><span class="w-8 h-8 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center text-pink-600 text-sm"><i class="fas fa-venus"></i></span> بيانات الأم</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <input type="hidden" name="mother_id" value="<?= e($mother['id'] ?? '') ?>">
      <input type="hidden" name="mother_id_card_image" value="<?= e($mother['id_card_image'] ?? '') ?>">
      <div><label class="block text-sm font-medium mb-1">اسم الأم *</label><input type="text" name="mother_name" value="<?= e($mother['name'] ?? '') ?>" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50"></div>
      <div><label class="block text-sm font-medium mb-1">الرقم القومي</label><input type="text" name="mother_national_id" value="<?= e($mother['national_id'] ?? '') ?>" <?= nationalIdAttrs() ?> class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 font-mono"></div>
      <div><label class="block text-sm font-medium mb-1">فصيلة الدم</label><select name="mother_blood_type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50">
          <option value="">--</option><?php foreach ($bloodTypes as $bt): ?><option value="<?= $bt ?>" <?= ($mother['blood_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option><?php endforeach; ?>
        </select></div>
      <div><label class="block text-sm font-medium mb-1">رقم الهاتف</label><input type="text" name="mother_phone" value="<?= e($mother['phone'] ?? '') ?>" <?= phoneAttrs() ?> class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50"></div>
      <div><label class="block text-sm font-medium mb-1">تاريخ الميلاد</label><input type="date" name="mother_birth_date" value="<?= e($mother['birth_date'] ?? '') ?>" data-age-calc="#motherAge" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50"></div>
      <div><label class="block text-sm font-medium mb-1">العمر</label><input type="text" id="motherAge" readonly value="<?= calcAge($mother['birth_date'] ?? null) ?? '' ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700"></div>
      <div class="sm:col-span-2"><label class="block text-sm font-medium mb-1">العنوان</label><input type="text" name="mother_address" value="<?= e($mother['address'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50"></div>
      <div><label class="block text-sm font-medium mb-1">صورة البطاقة</label><input type="file" name="mother_id_card" accept="image/*" class="w-full text-sm"><?php if (!empty($mother['id_card_image'])): ?><img src="<?= uploadUrl($mother['id_card_image']) ?>" class="mt-2 h-16 rounded border"><?php endif; ?></div>
    </div>
    <!-- DNA Markers for Mother -->
    <div class="mt-6">
      <h3 class="text-md font-semibold mb-3 text-pink-700 dark:text-pink-400">نتائج تحليل الحمض النووي للأم</h3>
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
              <td class="px-3 py-2"><input type="text" name="mother_D3S1358_1" value="<?= e($mother['D3S1358_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 15"></td>
              <td class="px-3 py-2"><input type="text" name="mother_D3S1358_2" value="<?= e($mother['D3S1358_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 17"></td>
            </tr>
            <tr>
              <td class="px-3 py-2 text-center font-medium">vWA</td>
              <td class="px-3 py-2"><input type="text" name="mother_vWA_1" value="<?= e($mother['vWA_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 16"></td>
              <td class="px-3 py-2"><input type="text" name="mother_vWA_2" value="<?= e($mother['vWA_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 18"></td>
            </tr>
            <tr>
              <td class="px-3 py-2 text-center font-medium">FGA</td>
              <td class="px-3 py-2"><input type="text" name="mother_FGA_1" value="<?= e($mother['FGA_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 21"></td>
              <td class="px-3 py-2"><input type="text" name="mother_FGA_2" value="<?= e($mother['FGA_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 24"></td>
            </tr>
            <tr>
              <td class="px-3 py-2 text-center font-medium">D8S1179</td>
              <td class="px-3 py-2"><input type="text" name="mother_D8S1179_1" value="<?= e($mother['D8S1179_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 10"></td>
              <td class="px-3 py-2"><input type="text" name="mother_D8S1179_2" value="<?= e($mother['D8S1179_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 14"></td>
            </tr>
            <tr>
              <td class="px-3 py-2 text-center font-medium">D21S11</td>
              <td class="px-3 py-2"><input type="text" name="mother_D21S11_1" value="<?= e($mother['D21S11_1'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 28"></td>
              <td class="px-3 py-2"><input type="text" name="mother_D21S11_2" value="<?= e($mother['D21S11_2'] ?? '') ?>" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-center font-mono bg-white dark:bg-gray-800" placeholder="مثلاً: 30"></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Section 3: Children -->
  <div class="bg-white mt-4 mb-4 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-bold flex items-center gap-2"><span class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 text-sm"><i class="fas fa-children"></i></span> الأبناء</h2>
      <button type="button" id="addChildBtn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium transition"><i class="fas fa-plus ml-1"></i> إضافة ابن</button>
    </div>
    <div id="childrenContainer" class="space-y-4"></div>
  </div>

  <div class="flex gap-3">
    <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-semibold transition shadow-sm"><i class="fas fa-save ml-2"></i> حفظ</button>
    <a href="<?= $baseUrl ?>/families" class="px-6 py-3 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">إلغاء</a>
  </div>
</form>

<script>
  const uploadUrlBase = <?= json_encode($config['upload_url']) ?>;
</script>
<script src="<?= $baseUrl ?>/public/assets/js/family-form.js?v=<?= file_exists(__DIR__ . '/../../public/assets/js/family-form.js') ? filemtime(__DIR__ . '/../../public/assets/js/family-form.js') : time() ?>"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('familyForm')?.addEventListener('submit', e => {
      e.preventDefault();
      submitForm(e.target);
    });
    <?php if (!empty($children)): ?>
      <?php foreach ($children as $child): ?>
        addChild(<?= json_encode($child, JSON_UNESCAPED_UNICODE) ?>);
      <?php endforeach; ?>
    <?php endif; ?>
    if (typeof App !== 'undefined' && typeof App.initFileInputs === 'function') App.initFileInputs();
  });
</script>