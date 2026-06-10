<?php

declare(strict_types=1);

// Load Composer autoload if available
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
  require_once $composerAutoload;
}

$config = require __DIR__ . '/../config/app.php';
date_default_timezone_set($config['timezone']);
require_once __DIR__ . '/helpers.php';

spl_autoload_register(function (string $class): void {
  $prefix = 'App\\';
  $baseDir = __DIR__ . '/';

  if (!str_starts_with($class, $prefix)) {
    return;
  }

  $relativeClass = substr($class, strlen($prefix));
  $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

  if (file_exists($file)) {
    require $file;
  }
});
