<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
  protected function render(string $view, array $data = [], string $layout = 'main'): void
  {
    View::render($view, $data, $layout);
  }

  protected function json(array $data, int $status = 200): void
  {
    View::json($data, $status);
  }

  protected function redirect(string $path): void
  {
    View::redirect($path);
  }

  protected function redirectUrl(string $path): string
  {
    return url($path);
  }

  protected function input(?string $key = null, mixed $default = null): mixed
  {
    $data = Validator::sanitizeArray(
      array_merge($_GET, $_POST)
    );

    if ($key === null) {
      return $data;
    }

    return $data[$key] ?? $default;
  }

  protected function isPost(): bool
  {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
  }

  protected function getPage(): int
  {
    return max(1, (int) ($_GET['page'] ?? 1));
  }

  protected function getSearch(): string
  {
    return trim($_GET['search'] ?? '');
  }
}
