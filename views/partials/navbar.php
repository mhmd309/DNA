<?php
/**
 * @var string $baseUrl
 */
require_once dirname(__DIR__) . '/init.php';
?>
<nav id="mainNavbar" class="fixed top-0 left-0 right-0 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 z-40 transition-all duration-300 sidebar-open-nav">
    <div class="flex items-center justify-between h-full px-4 gap-4">
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <button id="sidebarToggle" type="button" title="فتح/إغلاق القائمة" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 flex-shrink-0">
                <i id="sidebarToggleIcon" class="fas fa-bars text-lg"></i>
            </button>
            <div class="relative flex-1 max-w-md">
                <input type="text" id="globalSearch" placeholder="بحث شامل..." autocomplete="off"
                    class="w-full pr-10 pl-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <div id="searchResults" class="hidden absolute top-full mt-2 w-full bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50 max-h-80 overflow-y-auto"></div>
            </div>
        </div>
    </div>
</nav>
