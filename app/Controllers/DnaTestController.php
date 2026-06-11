<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLogger;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Uploader;
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
    $this->handleAttachments($id);
    ActivityLogger::log('create', 'dna_test', $id, 'إضافة فحص DNA: ' . $data['person_name']);
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
    Auth::requirePermission('dna.edit');
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
    Auth::requirePermission('dna.edit');
    $testId = (int) $id;
    $existing = $this->model->find($testId);
    if (!$existing) {
      $this->json(['success' => false, 'message' => 'الفحص غير موجود'], 404);
    }

    $data = $this->parseInput();
    $errors = $this->validate($data);

    if ($errors) {
      $this->json(['success' => false, 'message' => reset($errors), 'errors' => $errors], 422);
    }

    $this->model->update($testId, $data);
    $this->handleAttachments($testId);
    ActivityLogger::log('update', 'dna_test', $testId, 'تعديل فحص DNA: ' . $data['person_name']);
    $this->json(['success' => true, 'message' => 'تم تحديث الفحص بنجاح', 'redirect' => '/DNA/dna-tests/show/' . $testId]);
  }

  public function delete(string $id): void
  {
    Auth::requirePermission('dna.delete');
    $test = $this->model->find((int) $id);
    if (!$test) {
      $this->json(['success' => false, 'message' => 'الفحص غير موجود'], 404);
    }
    $this->model->softDelete((int) $id);
    ActivityLogger::log('delete', 'dna_test', (int) $id, 'حذف فحص DNA: ' . $test['person_name']);
    $this->json(['success' => true, 'message' => 'تم حذف الفحص بنجاح']);
  }

  private function parseInput(): array
  {
    $input = $this->input();
    return [
      'person_name'    => $input['person_name'] ?? '',
      'sample_date'    => $input['sample_date'] ?? '',
      'lab_name'       => $input['lab_name'] ?? '',
      'lab_location'   => $input['lab_location'] ?? '',
      'doctor_name'    => $input['doctor_name'] ?? '',
      'status'         => $input['status'] ?? 'pending',
      'result_summary' => $input['result_summary'] ?? '',
      'D3S1358_1'      => $input['D3S1358_1'] ?? null,
      'D3S1358_2'      => $input['D3S1358_2'] ?? null,
      'vWA_1'          => $input['vWA_1'] ?? null,
      'vWA_2'          => $input['vWA_2'] ?? null,
      'FGA_1'          => $input['FGA_1'] ?? null,
      'FGA_2'          => $input['FGA_2'] ?? null,
      'D8S1179_1'      => $input['D8S1179_1'] ?? null,
      'D8S1179_2'      => $input['D8S1179_2'] ?? null,
      'D21S11_1'       => $input['D21S11_1'] ?? null,
      'D21S11_2'       => $input['D21S11_2'] ?? null,
    ];
  }

  private function validate(array $data): array
  {
    $errors = [];
    $statuses = ['completed', 'failed', 'pending'];

    if (empty($data['person_name'])) {
      $errors['person_name'] = 'اسم الشخص مطلوب';
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

  public function compare(): void
  {
    $dnaModel = new \App\Models\DnaTest();
    $familyModel = new \App\Models\Family();

    $tests = $dnaModel->getAllWithDna();
    $selectedTestId = isset($_GET['test_id']) ? (int)$_GET['test_id'] : null;
    $results = [];

    if ($selectedTestId) {
      $selectedTest = $dnaModel->findWithDetails($selectedTestId);
      if ($selectedTest) {
        $familyParents = $familyModel->getAllParents();

        foreach ($familyParents as $parent) {
          $match = calculateDnaMatch($selectedTest, $parent);
          if ($match['percentage'] >= 80) {
            $results[] = [
              'parent' => $parent,
              'match' => $match,
            ];
          }
        }

        usort($results, function ($a, $b) {
          return $b['match']['percentage'] <=> $a['match']['percentage'];
        });
      }
    }

    $this->render('dna/compare', [
      'title' => 'مقارنة الحمض النووي',
      'tests' => $tests,
      'selectedTestId' => $selectedTestId,
      'results' => $results,
    ]);
  }
}
