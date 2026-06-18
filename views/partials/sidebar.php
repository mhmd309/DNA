<?php

/**
 * @var string $baseUrl
 * @var array|null $user
 */
require_once dirname(__DIR__) . '/init.php';
?>
<aside id="sidebar" class="fixed top-0 right-0 w-64 h-full bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 z-40 transform transition-transform duration-300 translate-x-0 flex flex-col">

  <div class="flex items-center gap-3 h-16 px-4 border-b border-gray-200 dark:border-gray-700">
    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center text-white overflow-hidden">
      <img src="<?= $baseUrl ?>/public/dnalogofavicon.jpg" alt="Logo" class="w-full h-full object-cover rounded-xl">
    </div>
    <div>
      <div class="font-bold text-sm">نظام DNA</div>
      <div class="text-xs text-gray-500">إدارة العائلات والفحوصات</div>
    </div>
  </div>

  <nav class="p-3 flex flex-col flex-1 overflow-hidden">
    <div class="space-y-1 overflow-y-auto flex-1">
    <?php if (can('dashboard.view')): ?>
      <a href="<?= $baseUrl ?>/dashboard" class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
        <i class="fas fa-chart-pie w-5"></i> لوحة التحكم
      </a>
    <?php endif; ?>

    <?php if (can('families.view')): ?>
      <a href="<?= $baseUrl ?>/families" class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
        <i class="fas fa-people-roof w-5"></i> العائلات
      </a>
    <?php endif; ?>

    <?php if (can('individuals.view')): ?>
      <a href="<?= $baseUrl ?>/individuals" class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
        <i class="fas fa-users w-5"></i> الأفراد
      </a>
    <?php endif; ?>

    <?php if (can('dna.view')): ?>
      <a href="<?= $baseUrl ?>/dna-tests" class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
        <i class="fas fa-dna w-5"></i> فحوصات DNA
      </a>
      <a href="<?= $baseUrl ?>/dna-tests/compare" class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
        <i class="fas fa-exchange-alt w-5"></i> مقارنة فحوصات DNA 
      </a>
    <?php endif; ?>

    <?php if (can('users.view')): ?>
      <a href="<?= $baseUrl ?>/users" class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
        <i class="fas fa-user-shield w-5"></i> المستخدمون
      </a>
    <?php endif; ?>

    <?php if (can('families.view') || can('individuals.view') || can('dna.view') || can('users.view')): ?>
    <a href="<?= $baseUrl ?>/reports" class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
      <i class="fas fa-chart-line w-5"></i> التقارير
    </a>
    <?php endif; ?>
    <a href="<?= $baseUrl ?>/user-guide.html" target="_blank" class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
      <i class="fa-solid fa-question w-5"></i> دليل المستخدم
    </a>
    </div>

    <div class="border-t border-gray-200 dark:border-gray-700 p-3 mt-auto">
      <div class="flex items-center justify-between px-3">
        <button data-theme-toggle title="تغيير المظهر" aria-label="تغيير المظهر" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition">
          <i data-theme-icon class="fas fa-moon w-5"></i>
        </button>

        <a href="<?= $baseUrl ?>/logout" data-confirm-logout class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
          <i class="fas fa-sign-out-alt w-5"></i> <span class="sr-only">تسجيل الخروج</span>
        </a>
      </div>
    </div>
  </nav>
</aside>