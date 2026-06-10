<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Uploader;
use App\Models\Family;

class FamilyController extends Controller
{
  private Family $model;

  public function __construct()
  {
    $this->model = new Family();
  }

  public function index(): void
  {
    $config = require __DIR__ . '/../../config/app.php';
    $page = $this->getPage();
    $search = $this->getSearch();
    $result = $this->model->getAll($page, $config['per_page'], $search);

    $this->render('families/index', [
      'title'  => 'العائلات',
      'result' => $result,
      'search' => $search,
    ]);
  }

  public function create(): void
  {
    $this->render('families/form', [
      'title'  => 'إضافة عائلة',
      'family' => null,
      'action' => 'store',
    ]);
  }

  public function store(): void
  {
    $data = $this->parseFamilyInput();
    $errors = $this->validateFamily($data);

    if ($errors) {
      $this->json(['success' => false, 'message' => reset($errors), 'errors' => $errors], 422);
    }

    $this->handleMemberUploads($data);

    $userId = Auth::id();
    if ($userId === null) {
      $this->json(['success' => false, 'message' => 'يجب تسجيل الدخول'], 401);
    }

    try {
      $id = $this->model->create(
        $data['family'],
        $data['father'],
        $data['mother'],
        $data['children'],
        $userId
      );
      try {
        ActivityLogger::log('create', 'family', $id, 'إضافة عائلة: ' . $data['family']['family_name']);
      } catch (\Throwable) {
        // لا توقف الحفظ إذا فشل تسجيل النشاط
      }
      $this->json(['success' => true, 'message' => 'تم إضافة العائلة بنجاح', 'redirect' => '/DNA/families']);
    } catch (\Throwable $e) {
      $this->json(['success' => false, 'message' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage()], 500);
    }
  }

  public function show(string $id): void
  {
    $family = $this->model->getWithMembers((int) $id);
    if (!$family) {
      $this->redirect('families');
    }
    $this->render('families/show', ['title' => 'تفاصيل العائلة', 'family' => $family]);
  }

  public function tree(string $id): void
  {
    $family = $this->model->getWithMembers((int) $id);
    if (!$family) {
      $this->json(['success' => false, 'message' => 'العائلة غير موجودة'], 404);
    }
    $this->render('families/tree', ['title' => 'شجرة العائلة', 'family' => $family], 'none');
  }

  public function edit(string $id): void
  {
    $family = $this->model->getWithMembers((int) $id);
    if (!$family) {
      $this->redirect('families');
    }
    $this->render('families/form', [
      'title'  => 'تعديل عائلة',
      'family' => $family,
      'action' => 'update/' . $id,
    ]);
  }

  public function update(string $id): void
  {
    $familyId = (int) $id;
    $existing = $this->model->getWithMembers($familyId);
    if (!$existing) {
      $this->json(['success' => false, 'message' => 'العائلة غير موجودة'], 404);
    }

    $data = $this->parseFamilyInput();
    $errors = $this->validateFamily($data, $familyId, $existing);

    if ($errors) {
      $this->json(['success' => false, 'message' => reset($errors), 'errors' => $errors], 422);
    }

    $this->handleMemberUploads($data, $existing);

    try {
      $this->model->updateFamily($familyId, $data['family'], $data['father'], $data['mother'], $data['children']);
      ActivityLogger::log('update', 'family', $familyId, 'تعديل عائلة: ' . $data['family']['family_name']);
      $this->json(['success' => true, 'message' => 'تم تحديث العائلة بنجاح', 'redirect' => '/DNA/families/show/' . $familyId]);
    } catch (\Throwable $e) {
      $this->json(['success' => false, 'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()], 500);
    }
  }

  public function delete(string $id): void
  {
    $family = $this->model->find((int) $id);
    if (!$family) {
      $this->json(['success' => false, 'message' => 'العائلة غير موجودة'], 404);
    }
    $this->model->softDelete((int) $id);
    ActivityLogger::log('delete', 'family', (int) $id, 'حذف عائلة: ' . $family['family_name']);
    $this->json(['success' => true, 'message' => 'تم حذف العائلة بنجاح']);
  }

  public function searchApi(): void
  {
    $query = trim($_GET['q'] ?? '');
    $results = $this->model->searchForSelect($query);
    $this->json(['success' => true, 'data' => $results]);
  }

  private function parseFamilyInput(): array
  {
    $input = $this->input();
    $children = [];
    if (!empty($input['children']) && is_array($input['children'])) {
      foreach ($input['children'] as $child) {
        if (!empty($child['name'])) {
          $children[] = $child;
        }
      }
    }

    return [
      'family' => [
        'family_name' => $input['family_name'] ?? '',
        'family_code' => $input['family_code'] ?? '',
        'notes'       => $input['notes'] ?? '',
      ],
      'father' => [
        'id'                => $input['father_id'] ?? '',
        'name'              => $input['father_name'] ?? '',
        'national_id'       => $input['father_national_id'] ?? '',
        'blood_type'        => $input['father_blood_type'] ?? '',
        'phone'             => $input['father_phone'] ?? '',
        'birth_date'        => $input['father_birth_date'] ?? '',
        'address'           => $input['father_address'] ?? '',
        'id_card_image'     => $input['father_id_card_image'] ?? '',
        'D3S1358_1'         => $input['father_D3S1358_1'] ?? null,
        'D3S1358_2'         => $input['father_D3S1358_2'] ?? null,
        'vWA_1'             => $input['father_vWA_1'] ?? null,
        'vWA_2'             => $input['father_vWA_2'] ?? null,
        'FGA_1'             => $input['father_FGA_1'] ?? null,
        'FGA_2'             => $input['father_FGA_2'] ?? null,
        'D8S1179_1'         => $input['father_D8S1179_1'] ?? null,
        'D8S1179_2'         => $input['father_D8S1179_2'] ?? null,
        'D21S11_1'          => $input['father_D21S11_1'] ?? null,
        'D21S11_2'          => $input['father_D21S11_2'] ?? null,
      ],
      'mother' => [
        'id'                => $input['mother_id'] ?? '',
        'name'              => $input['mother_name'] ?? '',
        'national_id'       => $input['mother_national_id'] ?? '',
        'blood_type'        => $input['mother_blood_type'] ?? '',
        'phone'             => $input['mother_phone'] ?? '',
        'birth_date'        => $input['mother_birth_date'] ?? '',
        'address'           => $input['mother_address'] ?? '',
        'id_card_image'     => $input['mother_id_card_image'] ?? '',
        'D3S1358_1'         => $input['mother_D3S1358_1'] ?? null,
        'D3S1358_2'         => $input['mother_D3S1358_2'] ?? null,
        'vWA_1'             => $input['mother_vWA_1'] ?? null,
        'vWA_2'             => $input['mother_vWA_2'] ?? null,
        'FGA_1'             => $input['mother_FGA_1'] ?? null,
        'FGA_2'             => $input['mother_FGA_2'] ?? null,
        'D8S1179_1'         => $input['mother_D8S1179_1'] ?? null,
        'D8S1179_2'         => $input['mother_D8S1179_2'] ?? null,
        'D21S11_1'          => $input['mother_D21S11_1'] ?? null,
        'D21S11_2'          => $input['mother_D21S11_2'] ?? null,
      ],
      'children' => $children,
    ];
  }

  private function validateFamily(array $data, ?int $excludeFamilyId = null, ?array $existing = null): array
  {
    $errors = [];

    if (empty($data['family']['family_name'])) {
      $errors['family_name'] = 'اسم العائلة مطلوب';
    }
    if (empty($data['family']['family_code'])) {
      $errors['family_code'] = 'كود العائلة مطلوب';
    } elseif ($this->model->codeExists($data['family']['family_code'], $excludeFamilyId)) {
      $errors['family_code'] = 'كود العائلة مستخدم مسبقاً';
    }

    if (empty($data['father']['name'])) {
      $errors['father_name'] = 'اسم الأب مطلوب';
    }
    if (empty($data['mother']['name'])) {
      $errors['mother_name'] = 'اسم الأم مطلوب';
    }

    // Collect existing member IDs to exclude
    $existingMemberIds = [];
    if ($existing) {
      if ($existing['father']) $existingMemberIds[] = $existing['father']['id'];
      if ($existing['mother']) $existingMemberIds[] = $existing['mother']['id'];
      foreach ($existing['children'] as $child) {
        $existingMemberIds[] = $child['id'];
      }
    }

    $members = [
      ['key' => 'father', 'data' => $data['father'], 'label' => 'الأب'],
      ['key' => 'mother', 'data' => $data['mother'], 'label' => 'الأم'],
    ];
    foreach ($data['children'] as $i => $child) {
      $members[] = ['key' => "child_{$i}", 'data' => $child, 'label' => 'الابن ' . ($i + 1)];
    }

    $nationalIds = [];
    $phones = [];

    foreach ($members as $member) {
      $m = $member['data'];
      $label = $member['label'];

      if (!empty($m['national_id'])) {
        $nidError = validateNationalId($m['national_id']);
        if ($nidError) {
          $errors[$member['key'] . '_national_id'] = "{$label}: {$nidError}";
        } elseif (in_array($m['national_id'], $nationalIds, true)) {
          $errors[$member['key'] . '_national_id'] = "الرقم القومي لـ{$label} مكرر";
        } elseif ($this->model->nationalIdExists($m['national_id'], $existingMemberIds)) {
          $errors[$member['key'] . '_national_id'] = "الرقم القومي لـ{$label} مستخدم مسبقاً";
        }
        $nationalIds[] = $m['national_id'];
      }

      if (!empty($m['phone'])) {
        $phoneError = validatePhone($m['phone']);
        if ($phoneError) {
          $errors[$member['key'] . '_phone'] = "{$label}: {$phoneError}";
        } elseif (in_array($m['phone'], $phones, true)) {
          $errors[$member['key'] . '_phone'] = "رقم الهاتف لـ{$label} مكرر";
        } elseif ($this->model->phoneExists($m['phone'], $existingMemberIds)) {
          $errors[$member['key'] . '_phone'] = "رقم الهاتف لـ{$label} مستخدم مسبقاً";
        }
        $phones[] = $m['phone'];
      }
    }

    return $errors;
  }

  private function handleMemberUploads(array &$data, ?array $existing = null): void
  {
    $config = require __DIR__ . '/../../config/app.php';

    if (!empty($_FILES['father_id_card']['name'])) {
      $upload = Uploader::upload($_FILES['father_id_card'], 'id_cards', $config['allowed_images']);
      if ($upload['success']) {
        $data['father']['id_card_image'] = $upload['path'];
      }
    } elseif (!empty($data['father']['id_card_image'])) {
    } elseif ($existing && $existing['father']) {
      $data['father']['id_card_image'] = $existing['father']['id_card_image'];
    }

    if (!empty($_FILES['mother_id_card']['name'])) {
      $upload = Uploader::upload($_FILES['mother_id_card'], 'id_cards', $config['allowed_images']);
      if ($upload['success']) {
        $data['mother']['id_card_image'] = $upload['path'];
      }
    } elseif (!empty($data['mother']['id_card_image'])) {
    } elseif ($existing && $existing['mother']) {
      $data['mother']['id_card_image'] = $existing['mother']['id_card_image'];
    }

    foreach ($data['children'] as $i => &$child) {
      $fileKey = "child_id_card_{$i}";
      if (!empty($_FILES[$fileKey]['name'])) {
        $upload = Uploader::upload($_FILES[$fileKey], 'id_cards', $config['allowed_images']);
        if ($upload['success']) {
          $child['id_card_image'] = $upload['path'];
        }
      } elseif (!empty($child['id_card_image'])) {
      } elseif ($existing && isset($existing['children'][$i])) {
        $child['id_card_image'] = $existing['children'][$i]['id_card_image'];
      }
    }
  }
}
