<?php

declare(strict_types=1);

namespace App\Core;

class RateLimiter
{
  private string $storageDir;

  public function __construct()
  {
    $this->storageDir = dirname(__DIR__, 2) . '/storage/rate_limits';
    if (!is_dir($this->storageDir)) {
      mkdir($this->storageDir, 0755, true);
    }
  }

  public function tooManyAttempts(string $key, int $maxAttempts): bool
  {
    $data = $this->read($key);
    if ($data === null) {
      return false;
    }

    if ($data['reset_at'] <= time()) {
      $this->forget($key);
      return false;
    }

    return $data['attempts'] >= $maxAttempts;
  }

  public function hit(string $key, int $decaySeconds): void
  {
    $data = $this->read($key);
    $now = time();

    if ($data === null || $data['reset_at'] <= $now) {
      $data = ['attempts' => 0, 'reset_at' => $now + $decaySeconds];
    }

    $data['attempts']++;
    $this->write($key, $data);
  }

  public function clear(string $key): void
  {
    $this->forget($key);
  }

  public function availableIn(string $key): int
  {
    $data = $this->read($key);
    if ($data === null) {
      return 0;
    }

    return max(0, $data['reset_at'] - time());
  }

  private function filePath(string $key): string
  {
    return $this->storageDir . '/' . hash('sha256', $key) . '.json';
  }

  private function read(string $key): ?array
  {
    $path = $this->filePath($key);
    if (!is_file($path)) {
      return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
      return null;
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
  }

  private function write(string $key, array $data): void
  {
    file_put_contents($this->filePath($key), json_encode($data), LOCK_EX);
  }

  private function forget(string $key): void
  {
    $path = $this->filePath($key);
    if (is_file($path)) {
      unlink($path);
    }
  }
}
