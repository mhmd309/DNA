<?php

declare(strict_types=1);

function e(?string $value): string
{
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function nationalIdAttrs(): string
{
  $config = require __DIR__ . '/../config/app.php';
  $len = (int) $config['national_id_length'];
  return 'maxlength="' . $len . '" inputmode="numeric" pattern="[0-9]{0,' . $len . '}" data-national-id';
}

function phoneAttrs(): string
{
  $config = require __DIR__ . '/../config/app.php';
  $len = (int) $config['phone_length'];
  return 'maxlength="' . $len . '" inputmode="numeric" pattern="[0-9]{0,' . $len . '}" data-phone';
}

function validateNationalId(?string $value): ?string
{
  if ($value === null || $value === '') {
    return null;
  }
  $config = require __DIR__ . '/../config/app.php';
  $max = (int) $config['national_id_length'];
  if (!preg_match('/^\d{1,' . $max . '}$/', $value)) {
    return "الرقم القومي يجب أن يكون أرقاماً فقط وبحد أقصى {$max} رقم";
  }
  return null;
}

function validatePhone(?string $value): ?string
{
  if ($value === null || $value === '') {
    return null;
  }
  $config = require __DIR__ . '/../config/app.php';
  $max = (int) $config['phone_length'];
  if (!preg_match('/^\d{1,' . $max . '}$/', $value)) {
    return "رقم الهاتف يجب أن يكون أرقاماً فقط وبحد أقصى {$max} رقم";
  }
  return null;
}

function nf(int|float $number): string
{
  return number_format($number);
}

function statusLabel(string $status): string
{
  return match ($status) {
    'normal'        => 'عادي',
    'missing'       => 'مفقود',
    'unidentified'  => 'مجهول هوية',
    'deceased'      => 'متوفى',
    'completed'     => 'مكتمل',
    'failed'        => 'فشل',
    'pending'       => 'قيد الانتظار',
    default         => $status,
  };
}

function statusBadge(string $status): string
{
  $colors = [
    'normal'       => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'missing'      => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    'unidentified' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    'deceased'     => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'completed'    => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    'failed'       => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    'pending'      => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
  ];
  $class = $colors[$status] ?? 'bg-gray-100 text-gray-800';
  return '<span class="px-2.5 py-1 rounded-full text-xs font-medium ' . $class . '">' . e(statusLabel($status)) . '</span>';
}

function genderLabel(string $gender): string
{
  return $gender === 'male' ? 'ذكر' : 'أنثى';
}

function roleLabel(string $role): string
{
  $config = require __DIR__ . '/../config/app.php';
  return $config['roles'][$role]['label'] ?? $role;
}

function can(string $permission): bool
{
  return \App\Core\Auth::hasPermission($permission);
}

function uploadUrl(?string $path): string
{
  if (!$path) return '';
  $config = require __DIR__ . '/../config/app.php';
  return $config['upload_url'] . $path;
}

function calcAge(?string $birthDate): ?int
{
  if (!$birthDate) return null;
  $birth = new DateTime($birthDate);
  $now = new DateTime();
  return (int) $birth->diff($now)->y;
}

function formatDateTime(?string $datetime): ?string
{
  if (!$datetime) return null;
  $dt = new DateTime($datetime);
  return $dt->format('Y-m-d (h:i a)');
}

function renderMemberCard(?array $member, string $role, string $color): void
{
  if (!$member) {
    return;
  }

  $roleLabel = match ($role) {
    'father' => 'الأب',
    'mother' => 'الأم',
    default => 'ابن/ابنة',
  };

  $colorClasses = match ($color) {
    'blue' => ['header' => 'bg-blue-50 dark:bg-blue-900/20', 'icon' => 'text-blue-600', 'marker-label' => 'text-blue-700 dark:text-blue-400'],
    'pink' => ['header' => 'bg-pink-50 dark:bg-pink-900/20', 'icon' => 'text-pink-600', 'marker-label' => 'text-pink-700 dark:text-pink-400'],
    'green' => ['header' => 'bg-green-50 dark:bg-green-900/20', 'icon' => 'text-green-600', 'marker-label' => 'text-green-700 dark:text-green-400'],
    default => ['header' => 'bg-gray-50 dark:bg-gray-900/20', 'icon' => 'text-gray-600', 'marker-label' => 'text-gray-700 dark:text-gray-400'],
  };

  $roleIcon = match ($role) {
    'father' => 'fa-mars',
    'mother' => 'fa-venus',
    default => 'fa-child',
  };
?>
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="<?= $colorClasses['header'] ?> px-5 py-3 border-b border-gray-200 dark:border-gray-700">
      <h3 class="font-bold flex items-center gap-2">
        <i class="fas <?= $roleIcon ?> <?= $colorClasses['icon'] ?>"></i>
        <?= $roleLabel ?>: <?= e($member['name']) ?>
      </h3>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-4">
      <?php if (!empty($member['national_id'])): ?><div><span class="text-gray-500">الرقم القومي:</span> <span class="font-mono"><?= e($member['national_id']) ?></span></div><?php endif; ?>
      <?php if (!empty($member['blood_type'])): ?><div><span class="text-gray-500">فصيلة الدم:</span> <?= e($member['blood_type']) ?></div><?php endif; ?>
      <?php if (!empty($member['phone'])): ?><div><span class="text-gray-500">الهاتف:</span> <?= e($member['phone']) ?></div><?php endif; ?>
      <?php if (!empty($member['birth_date'])): ?><div><span class="text-gray-500">تاريخ الميلاد:</span> <?= e($member['birth_date']) ?> (<?= calcAge($member['birth_date']) ?> سنة)</div><?php endif; ?>
      <?php if (!empty($member['gender'])): ?><div><span class="text-gray-500">الجنس:</span> <?= genderLabel($member['gender']) ?></div><?php endif; ?>
      <?php if (!empty($member['address'])): ?><div class="sm:col-span-2"><span class="text-gray-500">العنوان:</span> <?= e($member['address']) ?></div><?php endif; ?>
      <?php if (!empty($member['id_card_image'])): ?>
        <div class="sm:col-span-2">
          <span class="text-gray-500 block mb-2">صورة البطاقة:</span>
          <img src="<?= uploadUrl($member['id_card_image']) ?>" alt="بطاقة" class="max-w-xs rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer hover:opacity-90 transition">
        </div>
      <?php endif; ?>
    </div>
    <!-- DNA Markers -->
    <?php if (!empty($member['D3S1358_1']) || !empty($member['D3S1358_2']) || !empty($member['vWA_1']) || !empty($member['vWA_2']) || !empty($member['FGA_1']) || !empty($member['FGA_2']) || !empty($member['D8S1179_1']) || !empty($member['D8S1179_2']) || !empty($member['D21S11_1']) || !empty($member['D21S11_2'])): ?>
      <div class="px-5 pb-5">
        <h4 class="font-semibold mb-3 <?= $colorClasses['marker-label'] ?>">نتائج تحليل الحمض النووي</h4>
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th class="px-2 py-1.5 text-center font-semibold">العلامة</th>
                <th class="px-2 py-1.5 text-center font-semibold">الأليل 1</th>
                <th class="px-2 py-1.5 text-center font-semibold">الأليل 2</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr>
                <td class="px-2 py-1.5 text-center font-medium">D3S1358</td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['D3S1358_1'] ?? '-') ?></td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['D3S1358_2'] ?? '-') ?></td>
              </tr>
              <tr>
                <td class="px-2 py-1.5 text-center font-medium">vWA</td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['vWA_1'] ?? '-') ?></td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['vWA_2'] ?? '-') ?></td>
              </tr>
              <tr>
                <td class="px-2 py-1.5 text-center font-medium">FGA</td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['FGA_1'] ?? '-') ?></td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['FGA_2'] ?? '-') ?></td>
              </tr>
              <tr>
                <td class="px-2 py-1.5 text-center font-medium">D8S1179</td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['D8S1179_1'] ?? '-') ?></td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['D8S1179_2'] ?? '-') ?></td>
              </tr>
              <tr>
                <td class="px-2 py-1.5 text-center font-medium">D21S11</td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['D21S11_1'] ?? '-') ?></td>
                <td class="px-2 py-1.5 text-center font-mono"><?= e($member['D21S11_2'] ?? '-') ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
<?php
}

function statCardColorClasses(string $color): array
{
  return match ($color) {
    'blue'    => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-600 dark:text-blue-400'],
    'indigo'  => ['bg' => 'bg-indigo-100 dark:bg-indigo-900/30', 'text' => 'text-indigo-600 dark:text-indigo-400'],
    'cyan'    => ['bg' => 'bg-cyan-100 dark:bg-cyan-900/30', 'text' => 'text-cyan-600 dark:text-cyan-400'],
    'amber'   => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-600 dark:text-amber-400'],
    'sky'     => ['bg' => 'bg-sky-100 dark:bg-sky-900/30', 'text' => 'text-sky-600 dark:text-sky-400'],
    'pink'    => ['bg' => 'bg-pink-100 dark:bg-pink-900/30', 'text' => 'text-pink-600 dark:text-pink-400'],
    'purple'  => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-600 dark:text-purple-400'],
    'gray'    => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-400'],
    'emerald' => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-600 dark:text-emerald-400'],
    default   => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-400'],
  };
}

function paginationLinks(array $result, string $basePath, string $search = ''): string
{
  $current = $result['current_page'];
  $last = $result['last_page'];
  $query = $search ? '&search=' . urlencode($search) : '';

  $html = '<nav class="flex items-center justify-center gap-1 mt-6" aria-label="Pagination">';

  if ($current > 1) {
    $html .= '<a href="' . e($basePath) . '?page=' . ($current - 1) . $query . '" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"><i class="fas fa-chevron-right"></i></a>';
  }

  for ($i = 1; $i <= $last; $i++) {
    $active = $i === $current ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800';
    $html .= '<a href="' . e($basePath) . '?page=' . $i . $query . '" class="px-3 py-2 rounded-lg border ' . $active . '">' . $i . '</a>';
  }

  if ($current < $last) {
    $html .= '<a href="' . e($basePath) . '?page=' . ($current + 1) . $query . '" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"><i class="fas fa-chevron-left"></i></a>';
  }

  $html .= '</nav>';
  return $html;
}

function getDnaMarkers(): array
{
  return ['D3S1358', 'vWA', 'FGA', 'D8S1179', 'D21S11'];
}

function calculateDnaMatch(array $profile1, array $profile2): array
{
  $markers = getDnaMarkers();
  $totalMarkers = 0;
  $matchedMarkers = 0;
  $details = [];

  foreach ($markers as $marker) {
    $a1_1 = $profile1[$marker . '_1'] ?? null;
    $a1_2 = $profile1[$marker . '_2'] ?? null;
    $a2_1 = $profile2[$marker . '_1'] ?? null;
    $a2_2 = $profile2[$marker . '_2'] ?? null;

    $hasBoth1 = !empty($a1_1) && !empty($a1_2);
    $hasBoth2 = !empty($a2_1) && !empty($a2_2);

    if (!$hasBoth1 || !$hasBoth2) {
      $details[] = [
        'marker' => $marker,
        'status' => 'incomplete',
        'profile1' => [$a1_1, $a1_2],
        'profile2' => [$a2_1, $a2_2],
      ];
      continue;
    }

    $totalMarkers++;

    $alleles1 = [$a1_1, $a1_2];
    $alleles2 = [$a2_1, $a2_2];

    $match = false;
    foreach ($alleles1 as $a1) {
      foreach ($alleles2 as $a2) {
        if ($a1 === $a2) {
          $match = true;
          break 2;
        }
      }
    }

    if ($match) {
      $matchedMarkers++;
    }

    $details[] = [
      'marker' => $marker,
      'status' => $match ? 'matched' : 'unmatched',
      'profile1' => $alleles1,
      'profile2' => $alleles2,
    ];
  }

  $percentage = $totalMarkers > 0 ? round(($matchedMarkers / $totalMarkers) * 100, 2) : 0;

  return [
    'percentage' => $percentage,
    'total_markers' => $totalMarkers,
    'matched_markers' => $matchedMarkers,
    'details' => $details,
  ];
}
