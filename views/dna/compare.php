<?php

/**
 * @var string $baseUrl
 * @var string $title
 * @var array $tests
 * @var ?int $selectedTestId
 * @var array $results
 */
require_once dirname(__DIR__) . '/init.php';
?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <h1 class="text-2xl font-bold">قارنة فحوصات DNA</h1>
  <a href="<?= $baseUrl ?>/dna-tests" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition"><i class="fas fa-arrow-right"></i> رجوع</a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
  <form method="GET" class="flex flex-col sm:flex-row gap-4">
    <div class="flex-1">
      <label class="block text-sm font-medium mb-2">اختر فحص DNA للمقارنة</label>
      <input type="text" list="dnaTestsList" id="dnaTestInput" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="ابحث بالاسم..." autocomplete="off">
      <datalist id="dnaTestsList">
        <?php foreach ($tests as $test): ?>
          <option value="<?= e($test['person_name']) ?>" data-id="<?= $test['id'] ?>"><?= e($test['sample_date'] ?? 'بدون تاريخ') ?></option>
        <?php endforeach; ?>
      </datalist>
      <input type="hidden" name="test_id" id="dnaTestId" value="<?= $selectedTestId ?>">
    </div>
    <div class="flex items-end">
      <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-medium transition w-full sm:w-auto">مقارنة</button>
    </div>
  </form>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const input = document.getElementById('dnaTestInput');
      const hiddenInput = document.getElementById('dnaTestId');
      const tests = <?= json_encode($tests, JSON_UNESCAPED_UNICODE) ?>;
      
      // Set initial value if selected
      <?php if ($selectedTestId): ?>
        const selectedTest = tests.find(t => t.id === <?= $selectedTestId ?>);
        if (selectedTest) {
          input.value = selectedTest.person_name;
        }
      <?php endif; ?>
      
      input.addEventListener('input', () => {
        // Find matching test
        const match = tests.find(t => t.person_name === input.value);
        hiddenInput.value = match ? match.id : '';
      });
    });
  </script>
</div>

<?php if ($selectedTestId && !empty($results)): ?>
  <?php 
    $topFather = null;
    $topMother = null;
    foreach ($results as $result) {
      $parent = $result['parent'];
      $gender = $parent['gender'] ?? ($parent['role'] ?? null);
      if ($gender === 'male' || $gender === 'father') {
        if (!$topFather || $result['match']['percentage'] > $topFather['match']['percentage']) {
          $topFather = $result;
        }
      } elseif ($gender === 'female' || $gender === 'mother') {
        if (!$topMother || $result['match']['percentage'] > $topMother['match']['percentage']) {
          $topMother = $result;
        }
      }
    }
  ?>

  <?php if ($topFather || $topMother): ?>
    <div class="bg-gradient-to-r from-blue-50 to-pink-50 dark:from-blue-900/20 dark:to-pink-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 p-6 mb-6">
      <h2 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fas fa-trophy text-yellow-500"></i> أعلى نسب التطابق</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <?php if ($topFather): ?>
          <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
            <h3 class="font-bold text-blue-600 dark:text-blue-400 mb-3 flex items-center gap-2"><i class="fas fa-mars"></i> الأب المحتمل</h3>
            <div class="mb-3">
              <span class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?= $topFather['match']['percentage'] ?>%</span>
              <span class="text-sm text-gray-500">تطابق</span>
            </div>
            <div class="text-sm">
              <div><strong>الاسم:</strong> <?= e($topFather['parent']['name']) ?></div>
              <?php if (!empty($topFather['parent']['national_id'])): ?>
                <div><strong>الرقم القومي:</strong> <?= e($topFather['parent']['national_id']) ?></div>
              <?php endif; ?>
              <?php if (!empty($topFather['parent']['family_name'])): ?>
                <div><strong>العائلة:</strong> <?= e($topFather['parent']['family_name']) ?></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
        <?php if ($topMother): ?>
          <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-pink-200 dark:border-pink-800">
            <h3 class="font-bold text-pink-600 dark:text-pink-400 mb-3 flex items-center gap-2"><i class="fas fa-venus"></i> الأم المحتملة</h3>
            <div class="mb-3">
              <span class="text-3xl font-bold text-pink-600 dark:text-pink-400"><?= $topMother['match']['percentage'] ?>%</span>
              <span class="text-sm text-gray-500">تطابق</span>
            </div>
            <div class="text-sm">
              <div><strong>الاسم:</strong> <?= e($topMother['parent']['name']) ?></div>
              <?php if (!empty($topMother['parent']['national_id'])): ?>
                <div><strong>الرقم القومي:</strong> <?= e($topMother['parent']['national_id']) ?></div>
              <?php endif; ?>
              <?php if (!empty($topMother['parent']['family_name'])): ?>
                <div><strong>العائلة:</strong> <?= e($topMother['parent']['family_name']) ?></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
    <h2 class="text-xl font-bold mb-4">جميع النتائج</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="px-4 py-3 text-center font-semibold">الترتيب</th>
            <th class="px-4 py-3 text-center font-semibold">الاسم</th>
            <th class="px-4 py-3 text-center font-semibold">الدور</th>
            <th class="px-4 py-3 text-center font-semibold">العائلة</th>
            <th class="px-4 py-3 text-center font-semibold">نسبة التطابق</th>
            <th class="px-4 py-3 text-center font-semibold">العلامات المتطابقة</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <?php foreach ($results as $index => $result): ?>
            <?php 
              $parent = $result['parent'];
              $match = $result['match'];
              $genderOrRole = $parent['gender'] ?? ($parent['role'] ?? 'غير محدد');
              $colorClass = $match['percentage'] >= 80 ? 'text-green-600 dark:text-green-400' : ($match['percentage'] >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400');
            ?>
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
              <td class="px-4 py-3 text-center font-bold"><?= $index + 1 ?></td>
              <td class="px-4 py-3 text-center font-medium"><?= e($parent['name']) ?></td>
              <td class="px-4 py-3 text-center">
                <?php if ($genderOrRole === 'male' || $genderOrRole === 'father'): ?>
                  <span class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400"><i class="fas fa-mars"></i> <?= $genderOrRole === 'father' ? 'أب' : 'ذكر' ?></span>
                <?php elseif ($genderOrRole === 'female' || $genderOrRole === 'mother'): ?>
                  <span class="inline-flex items-center gap-1 text-pink-600 dark:text-pink-400"><i class="fas fa-venus"></i> <?= $genderOrRole === 'mother' ? 'أم' : 'أنثى' ?></span>
                <?php else: ?>
                  <span class="text-gray-500"><?= e($genderOrRole) ?></span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-center"><?= e($parent['family_name'] ?? '-') ?></td>
              <td class="px-4 py-3 text-center font-bold <?= $colorClass ?>"><?= $match['percentage'] ?>%</td>
              <td class="px-4 py-3 text-center"><?= $match['matched_markers'] ?> / <?= $match['total_markers'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php elseif ($selectedTestId && empty($results)): ?>
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-12 text-center">
    <i class="fas fa-search text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
    <p class="text-gray-500">لا يوجد آباء أو أمهات مسجلين ببيانات الحمض النووي</p>
  </div>
<?php endif; ?>