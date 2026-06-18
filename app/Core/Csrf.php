<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
  private const SESSION_KEY = '_csrf_token';
  public const FIELD = '_csrf_token';
  public const HEADER = 'HTTP_X_CSRF_TOKEN';

  public static function token(): string
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    if (empty($_SESSION[self::SESSION_KEY])) {
      $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }

    return $_SESSION[self::SESSION_KEY];
  }

  public static function validate(): bool
  {
    $submitted = $_POST[self::FIELD] ?? $_SERVER[self::HEADER] ?? '';
    if (!is_string($submitted) || $submitted === '') {
      return false;
    }

    return hash_equals(self::token(), $submitted);
  }

  public static function validateOrFail(): void
  {
    if (self::validate()) {
      return;
    }

    if (Auth::isAjax()) {
      View::json([
        'success' => false,
        'message' => 'انتهت صلاحية الصفحة، أعد تحميلها وحاول مجدداً',
      ], 403);
    }

    http_response_code(403);
    echo 'رمز الحماية غير صالح. أعد تحميل الصفحة وحاول مجدداً.';
    exit;
  }
}
