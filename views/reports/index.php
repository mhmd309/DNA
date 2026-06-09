<?php

/**
 * @var string $baseUrl
 * @var array|null $user
 * @var string $title
 */
require_once dirname(__DIR__) . '/init.php';
?>

<div class="mb-6">
  <h1 class="text-2xl font-bold">التقارير والإحصائيات</h1>
  <p class="text-sm text-gray-500 mt-1">قم بتنزيل التقارير بصيغة Excel</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
  <a href="<?= $baseUrl ?>/reports/families" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition group">
    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-4 group-hover:scale-110 transition">
      <i class="fas fa-people-roof text-xl"></i>
    </div>
    <h3 class="font-semibold text-lg mb-2">تقرير العائلات</h3>
    <p class="text-sm text-gray-500">عرض وتنزيل بيانات جميع العائلات</p>
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2 text-blue-600 dark:text-blue-400 text-sm font-medium">
      <i class="fas fa-arrow-left"></i> عرض التقرير
    </div>
  </a>

  <a href="<?= $baseUrl ?>/reports/individuals" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition group">
    <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 mb-4 group-hover:scale-110 transition">
      <i class="fas fa-users text-xl"></i>
    </div>
    <h3 class="font-semibold text-lg mb-2">تقرير الأفراد</h3>
    <p class="text-sm text-gray-500">عرض وتنزيل بيانات جميع الأفراد</p>
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2 text-green-600 dark:text-green-400 text-sm font-medium">
      <i class="fas fa-arrow-left"></i> عرض التقرير
    </div>
  </a>

  <a href="<?= $baseUrl ?>/reports/dna-tests" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition group">
    <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 mb-4 group-hover:scale-110 transition">
      <i class="fas fa-dna text-xl"></i>
    </div>
    <h3 class="font-semibold text-lg mb-2">تقرير فحوصات DNA</h3>
    <p class="text-sm text-gray-500">عرض وتنزيل بيانات جميع الفحوصات</p>
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2 text-purple-600 dark:text-purple-400 text-sm font-medium">
      <i class="fas fa-arrow-left"></i> عرض التقرير
    </div>
  </a>

  <a href="<?= $baseUrl ?>/reports/users" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition group">
    <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400 mb-4 group-hover:scale-110 transition">
      <i class="fas fa-user-shield text-xl"></i>
    </div>
    <h3 class="font-semibold text-lg mb-2">تقرير المستخدمين</h3>
    <p class="text-sm text-gray-500">عرض وتنزيل بيانات جميع المستخدمين</p>
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2 text-orange-600 dark:text-orange-400 text-sm font-medium">
      <i class="fas fa-arrow-left"></i> عرض التقرير
    </div>
  </a>
</div>
