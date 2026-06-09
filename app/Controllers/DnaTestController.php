<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Uploader;
use App\Models\Family;
use App\Models\DnaTest;

class DnaTestController extends Controller
{
  private DnaTest $model;

  public function __construct()
  {
    $this->model = new DnaTest();
  }

  public function index(): void
  {
    $config = require __DIR__ . '/../../config/app.php';
    $page = $this->getPage();
    $search = $this->getSearch();
    $result = $this->model->getAll($page, $config['per_page'], $search);

    $this->render('dna/index', [
      'title'  => 'فحوصات DNA',
      'result' => $result,
      'search' => $search,
    ]);
  }

  public function create(): void
  {
    $this->render('dna/form', [
      'title' => 'إضافة فحص DNA',
      'test'  => null,
      'action' => 'store',
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
    if (!empty($data['family_id'])) {
      (new Family())->upsertChildFromDnaTest($data);
    }
    $this->handleAttachments($id);
    ActivityLogger::log('create', 'dna_test', $id, 'إضافة فحص DNA: ' . $data['sample_number']);
    $this->json(['success' => true, 'message' => 'تم إضافة الفحص بنجاح', 'redirect' => '/DNA/dna-tests']);
  }

  public function show(string $id): void
  {
    $test = $this->model->findWithDetails((int) $id);
    if (!$test) {
      $this->redirect('dna-tests');
    }
    $this->render('dna/show', ['title' => 'تفاصيل فحص DNA', 'test' => $test]);
  }

  public function edit(string $id): void
  {
    $test = $this->model->findWithDetails((int) $id);
    if (!$test) {
      $this->redirect('dna-tests');
    }
    $this->render('dna/form', [
      'title'  => 'تعديل فحص DNA',
      'test'   => $test,
      'action' => 'update/' . $id,
    ]);
  }

  public function update(string $id): void
  {
    $testId = (int) $id;
    $existing = $this->model->find($testId);
    if (!$existing) {
      $this->json(['success' => false, 'message' => 'الفحص غير موجود'], 404);
    }

    $data = $this->parseInput();
    $errors = $this->validate($data, $testId);

    if ($errors) {
      $this->json(['success' => false, 'message' => reset($errors), 'errors' => $errors], 422);
    }

    $this->model->update($testId, $data);
    if (!empty($data['family_id'])) {
      (new Family())->upsertChildFromDnaTest($data, $existing);
    }
    $this->handleAttachments($testId);
    ActivityLogger::log('update', 'dna_test', $testId, 'تعديل فحص DNA: ' . $data['sample_number']);
    $this->json(['success' => true, 'message' => 'تم تحديث الفحص بنجاح', 'redirect' => '/DNA/dna-tests/show/' . $testId]);
  }

  public function delete(string $id): void
  {
    $test = $this->model->find((int) $id);
    if (!$test) {
      $this->json(['success' => false, 'message' => 'الفحص غير موجود'], 404);
    }
    $this->model->softDelete((int) $id);
    ActivityLogger::log('delete', 'dna_test', (int) $id, 'حذف فحص DNA: ' . $test['sample_number']);
    $this->json(['success' => true, 'message' => 'تم حذف الفحص بنجاح']);
  }

  private function parseInput(): array
  {
    $input = $this->input();
    return [
      'person_name'    => $input['person_name'] ?? '',
      'family_id'      => $input['family_id'] ?? '',
      'sample_number'  => $input['sample_number'] ?? '',
      'sample_date'    => $input['sample_date'] ?? '',
      'lab_name'       => $input['lab_name'] ?? '',
      'lab_location'   => $input['lab_location'] ?? '',
      'doctor_name'    => $input['doctor_name'] ?? '',
      'status'         => $input['status'] ?? 'pending',
      'result_summary' => $input['result_summary'] ?? '',
    ];
  }

  private function validate(array $data, ?int $excludeId = null): array
  {
    $errors = [];
    $statuses = ['completed', 'failed', 'pending'];

    if (empty($data['person_name'])) {
      $errors['person_name'] = 'اسم الشخص مطلوب';
    }
    if (empty($data['sample_number'])) {
      $errors['sample_number'] = 'رقم العينة مطلوب';
    } elseif ($this->model->sampleExists($data['sample_number'], $excludeId)) {
      $errors['sample_number'] = 'رقم العينة مستخدم مسبقاً';
    }
    if (!in_array($data['status'], $statuses, true)) {
      $errors['status'] = 'الحالة غير صالحة';
    }

    return $errors;
  }

  private function handleAttachments(int $testId): void
  {
    $config = require __DIR__ . '/../../config/app.php';

    if (empty($_FILES['attachments']['name'][0])) {
      return;
    }

    $files = $_FILES['attachments'];
    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
      if (empty($files['name'][$i])) continue;

      $file = [
        'name'     => $files['name'][$i],
        'type'     => $files['type'][$i],
        'tmp_name' => $files['tmp_name'][$i],
        'error'    => $files['error'][$i],
        'size'     => $files['size'][$i],
      ];

      $upload = Uploader::upload($file, 'dna_attachments', $config['allowed_docs']);
      if ($upload['success']) {
        $ext = pathinfo($upload['filename'], PATHINFO_EXTENSION);
        $this->model->addAttachment($testId, $files['name'][$i], $upload['path'], $ext, (int) $files['size'][$i]);
      }
    }
  }
}
