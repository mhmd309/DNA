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
    default  => 'ابن/ابنة',
  };

  $colorClasses = match ($color) {
    'blue'  => ['header' => 'bg-blue-50 dark:bg-blue-900/20', 'icon' => 'text-blue-600'],
    'pink'  => ['header' => 'bg-pink-50 dark:bg-pink-900/20', 'icon' => 'text-pink-600'],
    'green' => ['header' => 'bg-green-50 dark:bg-green-900/20', 'icon' => 'text-green-600'],
    default => ['header' => 'bg-gray-50 dark:bg-gray-900/20', 'icon' => 'text-gray-600'],
  };

  $roleIcon = match ($role) {
    'father' => 'fa-mars',
    'mother' => 'fa-venus',
    default  => 'fa-child',
  };
?>
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="<?= $colorClasses['header'] ?> px-5 py-3 border-b border-gray-200 dark:border-gray-700">
      <h3 class="font-bold flex items-center gap-2">
        <i class="fas <?= $roleIcon ?> <?= $colorClasses['icon'] ?>"></i>
        <?= $roleLabel ?>: <?= e($member['name']) ?>
      </h3>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
      <?php if (!empty($member['national_id'])): ?><div><span class="text-gray-500">الرقم القومي:</span> <span class="font-mono"><?= e($member['national_id']) ?></span></div><?php endif; ?>
      <?php if (!empty($member['dna_sample_number'])): ?><div><span class="text-gray-500">عينة DNA:</span> <span class="font-mono"><?= e($member['dna_sample_number']) ?></span></div><?php endif; ?>
      <?php if (!empty($member['blood_type'])): ?><div><span class="text-gray-500">فصيلة الدم:</span> <?= e($member['blood_type']) ?></div><?php endif; ?>
      <?php if (!empty($member['phone'])): ?><div><span class="text-gray-500">الهاتف:</span> <?= e($member['phone']) ?></div><?php endif; ?>
      <?php if (!empty($member['birth_date'])): ?><div><span class="text-gray-500">تاريخ الميلاد:</span> <?= e($member['birth_date']) ?> (<?= calcAge($member['birth_date']) ?> سنة)</div><?php endif; ?>
      <?php if (!empty($member['gender'])): ?><div><span class="text-gray-500">الجنس:</span> <?= genderLabel($member['gender']) ?></div><?php endif; ?>
      <?php if (!empty($member['address'])): ?><div class="sm:col-span-2"><span class="text-gray-500">العنوان:</span> <?= e($member['address']) ?></div><?php endif; ?>
      <?php if (!empty($member['id_card_image'])): ?>
        <div class="sm:col-span-2">
          <span class="text-gray-500 block mb-2">صورة البطاقة:</span>
          <img src="<?= uploadUrl($member['id_card_image']) ?>" alt="بطاقة" class="max-w-xs rounded-lg border border-gray-200 dark:border-gray-600">
        </div>
      <?php endif; ?>
    </div>
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
