<?php

declare(strict_types=1);

return [
  'name'           => 'نظام إدارة DNA',
  'version'        => '1.0.0',
  'base_url'       => '/DNA',
  'timezone'       => 'Africa/Cairo',
  'locale'         => 'ar',
  'upload_path'    => __DIR__ . '/../public/uploads/',
  'upload_url'     => '/DNA/public/uploads/',
  'max_upload_mb'  => 10,
  'allowed_images' => ['jpg', 'jpeg', 'png', 'webp'],
  'allowed_docs'   => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
  'per_page'            => 15,
  'national_id_length'  => 14,
  'phone_length'        => 11,
  'login_rate_limit'    => [
    'max_attempts'  => 5,
    'decay_minutes' => 15,
  ],
  'roles'          => [
    'admin'       => ['label' => 'مدير النظام', 'permissions' => ['*']],
    'manager'     => ['label' => 'مدير', 'permissions' => ['dashboard.view', 'families.view', 'families.create', 'individuals.view', 'individuals.create', 'dna.view', 'dna.create', 'users.view']],
    'data_entry'  => ['label' => 'إدخال بيانات', 'permissions' => ['dashboard.view', 'families.view', 'families.create', 'individuals.view', 'individuals.create', 'dna.view', 'dna.create']],
    'viewer'      => ['label' => 'مشاهد', 'permissions' => ['dashboard.view', 'families.view', 'individuals.view', 'dna.view']],
  ],
];
