<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
  private const SESSION_KEY = 'dna_user';
  private const REMEMBER_COOKIE = 'dna_remember';

  public static function init(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    if (!self::check() && isset($_COOKIE[self::REMEMBER_COOKIE])) {
      self::loginFromRememberToken($_COOKIE[self::REMEMBER_COOKIE]);
    }
  }

  public static function login(array $user, bool $remember = false): void
  {
    $_SESSION[self::SESSION_KEY] = [
      'id'    => $user['id'],
      'name'  => $user['name'],
      'email' => $user['email'],
      'role'  => $user['role'],
    ];

    if ($remember) {
      $token = bin2hex(random_bytes(32));
      $db = Database::getInstance();
      $stmt = $db->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
      $stmt->bind_param('si', $token, $user['id']);
      $stmt->execute();
      setcookie(self::REMEMBER_COOKIE, $token, time() + (86400 * 30), self::cookiePath(), '', false, true);
    }
  }

  private static function cookiePath(): string
  {
    $config = require __DIR__ . '/../../config/app.php';
    $base = rtrim($config['base_url'], '/');
    return $base === '' ? '/' : $base . '/';
  }

  private static function loginFromRememberToken(string $token): void
  {
    $db = Database::getInstance();
    $stmt = $db->prepare('SELECT id, name, email, role FROM users WHERE remember_token = ? AND deleted_at IS NULL AND is_active = 1 LIMIT 1');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user) {
      self::login($user, false);
    }
  }

  public static function logout(): void
  {
    if (self::check()) {
      $user = self::user();
      $db = Database::getInstance();
      $stmt = $db->prepare('UPDATE users SET remember_token = NULL WHERE id = ?');
      $stmt->bind_param('i', $user['id']);
      $stmt->execute();
    }

    unset($_SESSION[self::SESSION_KEY]);
    setcookie(self::REMEMBER_COOKIE, '', time() - 3600, self::cookiePath(), '', false, true);
    session_destroy();
  }

  public static function check(): bool
  {
    return isset($_SESSION[self::SESSION_KEY]);
  }

  public static function user(): ?array
  {
    return $_SESSION[self::SESSION_KEY] ?? null;
  }

  public static function id(): ?int
  {
    return self::user()['id'] ?? null;
  }

  public static function requireAuth(): void
  {
    if (!self::check()) {
      if (self::isAjax()) {
        $config = require __DIR__ . '/../../config/app.php';
        View::json([
          'success'  => false,
          'message'  => 'انتهت جلستك، يرجى تسجيل الدخول مرة أخرى',
          'redirect' => $config['base_url'] . '/login',
        ], 401);
      }
      View::redirect('login');
    }
  }

  public static function requirePermission(string $permission): void
  {
    self::requireAuth();
    if (!self::hasPermission($permission)) {
      if (self::isAjax()) {
        View::json(['success' => false, 'message' => 'ليس لديك صلاحية للوصول'], 403);
      }
      http_response_code(403);
      View::render('errors/403', ['title' => 'غير مصرح'], 'guest');
      exit;
    }
  }

  public static function hasPermission(string $permission): bool
  {
    $user = self::user();
    if (!$user) {
      return false;
    }

    $config = require __DIR__ . '/../../config/app.php';
    $role = $user['role'];
    $roleConfig = $config['roles'][$role] ?? null;
    if (!$roleConfig) {
      return false;
    }

    $permissions = $roleConfig['permissions'];
    if (in_array('*', $permissions, true)) {
      return true;
    }

    if (in_array($permission, $permissions, true)) {
      return true;
    }

    // Wildcard check: families.* matches families.view
    $parts = explode('.', $permission);
    if (count($parts) === 2) {
      $wildcard = $parts[0] . '.*';
      if (in_array($wildcard, $permissions, true)) {
        return true;
      }
    }

    return false;
  }

  public static function isAjax(): bool
  {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  }
}
