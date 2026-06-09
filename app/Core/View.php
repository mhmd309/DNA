<?php

declare(strict_types=1);

namespace App\Core;

class View
{
  public static function render(string $view, array $data = [], string $layout = 'main'): void
  {
    $config = require __DIR__ . '/../../config/app.php';
    $data['config'] = $config;
    $data['user'] = Auth::user();
    $data['baseUrl'] = $config['base_url'];
    $data['scripts'] = $data['scripts'] ?? '';

    extract($data, EXTR_SKIP);

    require_once __DIR__ . '/../../views/init.php';

    ob_start();
    $viewPath = __DIR__ . '/../../views/' . str_replace('.', '/', $view) . '.php';
    if (!file_exists($viewPath)) {
      throw new \RuntimeException("View not found: {$view}");
    }
    require $viewPath;
    $content = ob_get_clean();

    if ($layout === 'none') {
      echo $content;
      return;
    }

    $layoutPath = __DIR__ . '/../../views/layouts/' . $layout . '.php';
    if (!file_exists($layoutPath)) {
      echo $content;
      return;
    }
    require $layoutPath;
  }

  public static function json(array $data, int $status = 200): void
  {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
  }

  public static function redirect(string $path): void
  {
    $config = require __DIR__ . '/../../config/app.php';
    header('Location: ' . $config['base_url'] . '/' . ltrim($path, '/'));
    exit;
  }
}
