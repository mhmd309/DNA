<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\Family;
use App\Models\Individual;

class IndividualController extends Controller
{
  private Individual $model;

  public function __construct()
  {
    $this->model = new Individual();
  }

  public function index(): void
  {
    $config = require __DIR__ . '/../../config/app.php';
    $page = $this->getPage();
    $search = $this->getSearch();
    $result = $this->model->getAll($page, $config['per_page'], $search);

    $this->render('individuals/index', [
      'title'  => 'الأفراد',
      'result' => $result,
      'search' => $search,
    ]);
  }

  public function create(): void
  {
    $status = $_GET['status'] ?? 'normal';
    $this->render('individuals/form', [
      'title'      => 'إضافة فرد',
      'individual' => null,
      'action'     => 'store',
      'presetStatus' => $status,
    ]);
  }

  public function store(): void
  {
    $data = $this->parseInput();
    $errors = $this->validate($data);

    if ($errors) {
      $this->json(['success' => false, 'message' => reset($errors), 'errors' => $errors], 422);
    }

    $id = $this->model->create($data, Auth::id());
    (new Family())->syncIndividualFamilyLink($id, $data);
    ActivityLogger::log('create', 'individual', $id, 'إضافة فرد: ' . $data['name']);
    $this->json(['success' => true, 'message' => 'تم إضافة الفرد بنجاح', 'redirect' => $this->redirectUrl('individuals')]);
  }

  public function show(string $id): void
  {
    $individual = $this->model->findWithFamily((int) $id);
    if (!$individual) {
      $this->redirect('individuals');
    }
    $this->render('individuals/show', ['title' => 'تفاصيل الفرد', 'individual' => $individual]);
  }

  public function edit(string $id): void
  {
    Auth::requirePermission('individuals.edit');
    $individual = $this->model->findWithFamily((int) $id);
    if (!$individual) {
      $this->redirect('individuals');
    }
    $this->render('individuals/form', [
      'title'      => 'تعديل فرد',
      'individual' => $individual,
      'action'     => 'update/' . $id,
      'presetStatus' => $individual['status'],
    ]);
  }

  public function update(string $id): void
  {
    Auth::requirePermission('individuals.edit');
    $individualId = (int) $id;
    $existing = $this->model->find($individualId);
    if (!$existing) {
      $this->json(['success' => false, 'message' => 'الفرد غير موجود'], 404);
    }

    $data = $this->parseInput();
    $errors = $this->validate($data, $individualId);

    if ($errors) {
      $this->json(['success' => false, 'message' => reset($errors), 'errors' => $errors], 422);
    }

    $this->model->update($individualId, $data);
    (new Family())->syncIndividualFamilyLink($individualId, $data, $existing);
    ActivityLogger::log('update', 'individual', $individualId, 'تعديل فرد: ' . $data['name']);
    $message = 'تم تحديث الفرد بنجاح';
    $oldFamilyId = !empty($existing['family_id']) ? (int) $existing['family_id'] : 0;
    $newFamilyId = !empty($data['family_id']) ? (int) $data['family_id'] : 0;
    if ($oldFamilyId && $newFamilyId && $oldFamilyId !== $newFamilyId) {
      $message = 'تم نقل الفرد إلى العائلة الجديدة بنجاح';
    } elseif ($oldFamilyId && !$newFamilyId) {
      $message = 'تم إزالة الفرد من العائلة السابقة بنجاح';
    } elseif (!$oldFamilyId && $newFamilyId) {
      $message = 'تم ربط الفرد بالعائلة بنجاح';
    }
    $this->json(['success' => true, 'message' => $message, 'redirect' => $this->redirectUrl('individuals/show/' . $individualId)]);
  }

  public function delete(string $id): void
  {
    Auth::requirePermission('individuals.delete');
    $individual = $this->model->find((int) $id);
    if (!$individual) {
      $this->json(['success' => false, 'message' => 'الفرد غير موجود'], 404);
    }
    $this->model->softDelete((int) $id);
    if (!empty($individual['family_id'])) {
      (new Family())->removeChildLinkedToIndividual((int) $id, (int) $individual['family_id'], $individual);
    }
    ActivityLogger::log('delete', 'individual', (int) $id, 'حذف فرد: ' . $individual['name']);
    $this->json(['success' => true, 'message' => 'تم حذف الفرد بنجاح']);
  }

  private function parseInput(): array
  {
    $input = $this->input();
    return [
      'name'              => $input['name'] ?? '',
      'national_id'       => $input['national_id'] ?? '',
      'blood_type'        => $input['blood_type'] ?? '',
      'birth_date'        => $input['birth_date'] ?? '',
      'gender'            => $input['gender'] ?? '',
      'family_id'         => $input['family_id'] ?? '',
      'status'            => $input['status'] ?? 'normal',
      'D3S1358_1'         => $input['D3S1358_1'] ?? null,
      'D3S1358_2'         => $input['D3S1358_2'] ?? null,
      'vWA_1'             => $input['vWA_1'] ?? null,
      'vWA_2'             => $input['vWA_2'] ?? null,
      'FGA_1'             => $input['FGA_1'] ?? null,
      'FGA_2'             => $input['FGA_2'] ?? null,
      'D8S1179_1'         => $input['D8S1179_1'] ?? null,
      'D8S1179_2'         => $input['D8S1179_2'] ?? null,
      'D21S11_1'          => $input['D21S11_1'] ?? null,
      'D21S11_2'          => $input['D21S11_2'] ?? null,
    ];
  }

  private function validate(array $data, ?int $excludeId = null): array
  {
    $errors = [];
    $statuses = ['normal', 'missing', 'unidentified', 'deceased'];
    $genders = ['male', 'female'];

    if (empty($data['name'])) {
      $errors['name'] = 'اسم الفرد مطلوب';
    }
    if (!in_array($data['gender'], $genders, true)) {
      $errors['gender'] = 'الجنس مطلوب';
    }
    if (!in_array($data['status'], $statuses, true)) {
      $errors['status'] = 'الحالة غير صالحة';
    }
    if (!empty($data['national_id'])) {
      $nidError = validateNationalId($data['national_id']);
      if ($nidError) {
        $errors['national_id'] = $nidError;
      } elseif ($this->model->nationalIdExists($data['national_id'], $excludeId)) {
        $errors['national_id'] = 'الرقم القومي مستخدم مسبقاً';
      }
    }
    if (!empty($data['family_id'])) {
      $family = (new Family())->find((int) $data['family_id']);
      if (!$family) {
        $errors['family_id'] = 'العائلة المحددة غير موجودة';
      }
    }

    return $errors;
  }
}
