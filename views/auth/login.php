<?php

/**
 * @var string $baseUrl
 * @var string $title
 */
require_once dirname(__DIR__) . '/init.php';
?>
<div class="login-bg min-h-screen flex items-center justify-center p-4" style="background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('<?= $baseUrl ?>/public/bgsite.jpg'); background-size: cover; background-position: center;">
  <div class="absolute inset-0 overflow-hidden">
    <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-500/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl"></div>
  </div>

  <div class="glass-card relative w-full max-w-md rounded-2xl shadow-2xl p-8 border border-white/20">
    <div class="text-center mb-8">
      <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-primary-500 to-indigo-600 flex items-center justify-center text-white shadow-lg overflow-hidden">
        <img src="<?= $baseUrl ?>/public/dnalogofavicon.jpg" alt="Logo" class="w-full h-full object-cover">
      </div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">نظام إدارة DNA</h1>
      <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">تسجيل الدخول للمتابعة</p>
    </div>

    <form id="loginForm" action="<?= $baseUrl ?>/login" method="POST" class="space-y-5">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">البريد الإلكتروني</label>
        <div class="relative">
          <input type="email" name="email" required
            class="w-full pr-10 pl-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="admin@dna.com">
          <i class="fas fa-envelope absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1.5">كلمة المرور</label>
        <div class="relative">
          <input type="password" name="password" id="password" required
            class="w-full pr-10 pl-10 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            placeholder="••••••••">
          <i class="fas fa-lock absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <button type="button" id="togglePassword" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer">
          <div class="switch">
            <input type="checkbox" name="remember" value="1">
            <span class="slider"></span>
          </div>
          <span class="text-sm text-gray-600 dark:text-gray-400">تذكرني</span>
        </label>
      </div>

      <button type="submit" class="w-full py-3 bg-gradient-to-l from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-lg shadow-primary-500/25 transition">
        <i class="fas fa-sign-in-alt ml-2"></i> تسجيل الدخول
      </button>
    </form>
  </div>
</div>

<script>
  document.getElementById('togglePassword').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  });

  document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm(this);
  });
</script>