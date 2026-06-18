<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Validator;
use App\Models\User;

class AuthController extends Controller
{
  public function showLogin(): void
  {
    if (Auth::check()) {
      $this->redirect('dashboard');
    }
    $this->render('auth/login', ['title' => 'تسجيل الدخول'], 'guest');
  }

  public function login(): void
  {
    if (Auth::check()) {
      if (Auth::isAjax()) {
        $this->json(['success' => false, 'message' => 'أنت مسجل دخول بالفعل']);
      }
      $this->redirect('dashboard');
    }

    $data = $this->input();
    $email = strtolower(trim($data['email'] ?? ''));
    $config = require __DIR__ . '/../../config/app.php';
    $rateConfig = $config['login_rate_limit'];
    $limiter = new RateLimiter();
    $rateKey = 'login:' . hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') . '|' . $email);

    if ($limiter->tooManyAttempts($rateKey, (int) $rateConfig['max_attempts'])) {
      $retrySeconds = $limiter->availableIn($rateKey);
      $retryMinutes = max(1, (int) ceil($retrySeconds / 60));
      $this->json([
        'success' => false,
        'message' => "محاولات تسجيل دخول كثيرة. انتظر {$retryMinutes} دقيقة ثم حاول مجدداً",
      ], 429);
    }

    $validator = new Validator($data, [
      'email'    => 'required|email',
      'password' => 'required|min:6',
    ], [
      'email'    => 'البريد الإلكتروني مطلوب',
      'password' => 'كلمة المرور مطلوبة (6 أحرف على الأقل)',
    ]);

    if (!$validator->validate()) {
      $this->json(['success' => false, 'message' => $validator->firstError(), 'errors' => $validator->errors()], 422);
    }

    $userModel = new User();
    $user = $userModel->findByEmail($data['email']);

    if (!$user || !$user['is_active'] || !password_verify($data['password'], $user['password'])) {
      $limiter->hit($rateKey, (int) $rateConfig['decay_minutes'] * 60);
      $this->json(['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'], 401);
    }

    $limiter->clear($rateKey);

    unset($user['password'], $user['remember_token']);
    $remember = isset($data['remember']) && $data['remember'] === '1';
    Auth::login($user, $remember);

    ActivityLogger::log('login', 'user', $user['id'], 'تسجيل دخول');

    $this->json(['success' => true, 'message' => 'تم تسجيل الدخول بنجاح', 'redirect' => $this->redirectUrl('dashboard')]);
  }

  public function logout(): void
  {
    if (Auth::check()) {
      ActivityLogger::log('logout', 'user', Auth::id(), 'تسجيل خروج');
    }
    Auth::logout();
    $this->redirect('login');
  }
}
