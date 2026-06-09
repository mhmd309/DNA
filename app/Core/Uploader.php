<?php

declare(strict_types=1);

namespace App\Core;

class Uploader
{
  public static function upload(array $file, string $subdir, array $allowedExtensions): array
  {
    if ($file['error'] !== UPLOAD_ERR_OK) {
      return ['success' => false, 'message' => 'فشل رفع الملف'];
    }

    $config = require __DIR__ . '/../../config/app.php';
    $maxSize = $config['max_upload_mb'] * 1024 * 1024;

    if ($file['size'] > $maxSize) {
      return ['success' => false, 'message' => 'حجم الملف يتجاوز الحد المسموح'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions, true)) {
      return ['success' => false, 'message' => 'نوع الملف غير مسموح'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
      'jpg'  => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'png'  => 'image/png',
      'webp' => 'image/webp',
      'pdf'  => 'application/pdf',
    ];

    if (!isset($allowedMimes[$ext]) || $mime !== $allowedMimes[$ext]) {
      return ['success' => false, 'message' => 'محتوى الملف غير صالح'];
    }

    $uploadDir = $config['upload_path'] . $subdir . '/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
      return ['success' => false, 'message' => 'فشل حفظ الملف'];
    }

    return [
      'success'  => true,
      'filename' => $filename,
      'path'     => $subdir . '/' . $filename,
      'url'      => $config['upload_url'] . $subdir . '/' . $filename,
    ];
  }

  public static function delete(string $path): void
  {
    $config = require __DIR__ . '/../../config/app.php';
    $fullPath = $config['upload_path'] . $path;
    if (file_exists($fullPath)) {
      unlink($fullPath);
    }
  }
}
