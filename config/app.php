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
    'roles'          => [
        'admin'       => ['label' => 'مدير النظام', 'permissions' => ['*']],
        'manager'     => ['label' => 'مدير', 'permissions' => ['dashboard.view', 'families.*', 'individuals.*', 'dna.*', 'users.view']],
        'data_entry'  => ['label' => 'إدخال بيانات', 'permissions' => ['dashboard.view', 'families.view', 'families.create', 'families.edit', 'individuals.view', 'individuals.create', 'individuals.edit', 'dna.view', 'dna.create', 'dna.edit']],
        'viewer'      => ['label' => 'مشاهد', 'permissions' => ['dashboard.view', 'families.view', 'individuals.view', 'dna.view']],
    ],
];
