<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Uploader;
use App\Models\User;

class UserController extends Controller
{
  private User $model;

  public function __construct()
  {
    $this->model = new User();
  }

  public function index(): void
  {
    $config = require __DIR__ . '/../../config/app.php';
    $page = $this->getPage();
    $search = $this->getSearch();
    $result = $this->model->getAll($page, $config['per_page'], $search);

    $this->render('users/index', [
      'title'  => 'المستخدمون',
      'result' => $result,
      'search' => $search,
    ]);
  }

  public function create(): void
  {
    $this->render('users/form', [
      'title'  => 'إضافة مستخدم',
      'userData' => null,
      'action' => 'store',
    ]);
  }

  public function store(): void
  {
    $data = $this->parseInput();
    $errors = $this->validate($data, true);

    if ($errors) {
      $this->json(['success' => false, 'message' => reset($errors), 'errors' => $errors], 422);
    }

    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    $data['avatar'] = $this->handleAvatar();

    $id = $this->model->create($data);
    ActivityLogger::log('create', 'user', $id, 'إضافة مستخدم: ' . $data['name']);
    $this->json(['success' => true, 'message' => 'تم إضافة المستخدم بنجاح', 'redirect' => '/DNA/users']);
  }

  public function edit(string $id): void
  {
    $userData = $this->model->find((int) $id);
    if (!$userData) {
      $this->redirect('users');
    }
    unset($userData['password']);
    $this->render('users/form', [
      'title'    => 'تعديل مستخدم',
      'userData' => $userData,
      'action'   => 'update/' . $id,
    ]);
  }

  public function update(string $id): void
  {
    $userId = (int) $id;
    $existing = $this->model->find($userId);
    if (!$existing) {
      $this->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
    }

    $data = $this->parseInput();
    $errors = $this->validate($data, false, $userId);

    if ($errors) {
      $this->json(['success' => false, 'message' => reset($errors), 'errors' => $errors], 422);
    }

    if (!empty($data['password'])) {
      $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    } else {
      $data['password'] = '';
    }

    $avatar = $this->handleAvatar();
    $data['avatar'] = $avatar ?: ($existing['avatar'] ?? null);
    $data['is_active'] = isset($data['is_active']) ? (int) $data['is_active'] : 1;

    $this->model->update($userId, $data);
    ActivityLogger::log('update', 'user', $userId, 'تعديل مستخدم: ' . $data['name']);
    $this->json(['success' => true, 'message' => 'تم تحديث المستخدم بنجاح', 'redirect' => '/DNA/users']);
  }

  public function delete(string $id): void
  {
    $userId = (int) $id;
    if ($userId === Auth::id()) {
      $this->json(['success' => false, 'message' => 'لا يمكنك حذف حسابك'], 403);
    }

    $userData = $this->model->find($userId);
    if (!$userData) {
      $this->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
    }

    $this->model->softDelete($userId);
    ActivityLogger::log('delete', 'user', $userId, 'حذف مستخدم: ' . $userData['name']);
    $this->json(['success' => true, 'message' => 'تم حذف المستخدم بنجاح']);
  }

  private function parseInput(): array
  {
    $input = $this->input();
    return [
      'name'      => $input['name'] ?? '',
      'email'     => $input['email'] ?? '',
      'password'  => $input['password'] ?? '',
      'role'      => $input['role'] ?? 'viewer',
      'is_active' => $input['is_active'] ?? '1',
    ];
  }

  private function validate(array $data, bool $isCreate, ?int $excludeId = null): array
  {
    $errors = [];
    $roles = ['admin', 'manager', 'data_entry', 'viewer'];

    if (empty($data['name'])) {
      $errors['name'] = 'الاسم مطلوب';
    }
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'البريد الإلكتروني غير صالح';
    } elseif ($this->model->emailExists($data['email'], $excludeId)) {
      $errors['email'] = 'البريد الإلكتروني مستخدم مسبقاً';
    }
    if ($isCreate && (empty($data['password']) || strlen($data['password']) < 6)) {
      $errors['password'] = 'كلمة المرور مطلوبة (6 أحرف على الأقل)';
    }
    if (!$isCreate && !empty($data['password']) && strlen($data['password']) < 6) {
      $errors['password'] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    }
    if (!in_array($data['role'], $roles, true)) {
      $errors['role'] = 'الدور غير صالح';
    }

    return $errors;
  }

  private function handleAvatar(): ?string
  {
    if (empty($_FILES['avatar']['name'])) {
      return null;
    }
    $config = require __DIR__ . '/../../config/app.php';
    $upload = Uploader::upload($_FILES['avatar'], 'avatars', $config['allowed_images']);
    return $upload['success'] ? $upload['path'] : null;
  }
}
