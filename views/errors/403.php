<?php
/**
 * @var string $baseUrl
 * @var string $title
 */
require_once dirname(__DIR__) . '/init.php';
?>
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="text-center">
        <div class="text-8xl font-bold text-gray-200 dark:text-gray-700">403</div>
        <h1 class="text-2xl font-bold mt-4">غير مصرح</h1>
        <p class="text-gray-500 mt-2">ليس لديك صلاحية للوصول لهذه الصفحة</p>
        <a href="<?= $baseUrl ?>/dashboard" class="inline-block mt-6 px-6 py-3 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition">العودة للوحة التحكم</a>
    </div>
</div>
