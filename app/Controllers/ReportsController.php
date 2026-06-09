<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\DnaTest;
use App\Models\Family;
use App\Models\Individual;
use App\Models\User;

class ReportsController extends Controller
{
  private Family $familyModel;
  private Individual $individualModel;
  private DnaTest $dnaModel;
  private User $userModel;

  public function __construct()
  {
    $this->familyModel = new Family();
    $this->individualModel = new Individual();
    $this->dnaModel = new DnaTest();
    $this->userModel = new User();
  }

  public function index(): void
  {
    $this->render('reports/index', [
      'title' => 'التقارير',
    ]);
  }

  public function families(): void
  {
    $families = $this->familyModel->getAllForReport();

    if ($this->input('export') === 'excel') {
      $this->exportFamiliesToExcel($families);
    }

    $this->render('reports/families', [
      'title' => 'تقرير العائلات',
      'families' => $families,
    ]);
  }

  public function individuals(): void
  {
    $individuals = $this->individualModel->getAllForReport();

    if ($this->input('export') === 'excel') {
      $this->exportIndividualsToExcel($individuals);
    }

    $this->render('reports/individuals', [
      'title' => 'تقرير الأفراد',
      'individuals' => $individuals,
    ]);
  }

  public function dnaTests(): void
  {
    $tests = $this->dnaModel->getAllForReport();

    if ($this->input('export') === 'excel') {
      $this->exportDnaTestsToExcel($tests);
    }

    $this->render('reports/dna', [
      'title' => 'تقرير فحوصات DNA',
      'tests' => $tests,
    ]);
  }

  public function users(): void
  {
    $users = $this->userModel->getAllForReport();

    if ($this->input('export') === 'excel') {
      $this->exportUsersToExcel($users);
    }

    $this->render('reports/users', [
      'title' => 'تقرير المستخدمين',
      'users' => $users,
    ]);
  }

  private function exportFamiliesToExcel(array $families): void
  {
    $filename = 'تقرير_العائلات_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

    // Headers
    fputcsv($output, ['#', 'اسم العائلة', 'كود العائلة', 'عدد الأعضاء', 'أنشئ بواسطة', 'التاريخ'], ',');

    // Data
    foreach ($families as $i => $row) {
      fputcsv($output, [
        $i + 1,
        $row['family_name'],
        $row['family_code'],
        $row['members_count'],
        $row['created_by_name'] ?? 'غير محدد',
        date('Y-m-d', strtotime($row['created_at']))
      ], ',');
    }

    fclose($output);
    exit;
  }

  private function exportIndividualsToExcel(array $individuals): void
  {
    $filename = 'تقرير_الأفراد_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

    // Headers
    fputcsv($output, ['#', 'الاسم', 'الرقم القومي', 'رقم العينة', 'فصيلة الدم', 'تاريخ الميلاد', 'النوع', 'الحالة', 'العائلة', 'أنشئ بواسطة', 'التاريخ'], ',');

    // Data
    foreach ($individuals as $i => $row) {
      fputcsv($output, [
        $i + 1,
        $row['name'],
        $row['national_id'] ?? '-',
        $row['dna_sample_number'] ?? '-',
        $row['blood_type'] ?? '-',
        $row['birth_date'] ?? '-',
        $row['gender'] === 'male' ? 'ذكر' : 'أنثى',
        $this->getStatusLabel($row['status']),
        $row['family_name'] ?? '-',
        $row['created_by_name'] ?? 'غير محدد',
        date('Y-m-d', strtotime($row['created_at']))
      ], ',');
    }

    fclose($output);
    exit;
  }

  private function exportDnaTestsToExcel(array $tests): void
  {
    $filename = 'تقرير_فحوصات_DNA_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

    // Headers
    fputcsv($output, ['#', 'اسم الشخص', 'رقم العينة', 'تاريخ العينة', 'المختبر', 'الموقع', 'اسم الطبيب', 'الحالة', 'أنشئ بواسطة', 'التاريخ'], ',');

    // Data
    foreach ($tests as $i => $row) {
      fputcsv($output, [
        $i + 1,
        $row['person_name'],
        $row['sample_number'],
        $row['sample_date'] ?? '-',
        $row['lab_name'] ?? '-',
        $row['lab_location'] ?? '-',
        $row['doctor_name'] ?? '-',
        $this->getDnaStatusLabel($row['status']),
        $row['created_by_name'] ?? 'غير محدد',
        date('Y-m-d', strtotime($row['created_at']))
      ], ',');
    }

    fclose($output);
    exit;
  }

  private function exportUsersToExcel(array $users): void
  {
    $filename = 'تقرير_المستخدمين_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

    // Headers
    fputcsv($output, ['#', 'الاسم', 'البريد الإلكتروني', 'الدور', 'الحالة', 'التاريخ'], ',');

    // Data
    foreach ($users as $i => $row) {
      fputcsv($output, [
        $i + 1,
        $row['name'],
        $row['email'],
        roleLabel($row['role']),
        $row['is_active'] ? 'نشط' : 'معطل',
        date('Y-m-d', strtotime($row['created_at']))
      ], ',');
    }

    fclose($output);
    exit;
  }

  private function getStatusLabel(string $status): string
  {
    return match ($status) {
      'normal' => 'عادي',
      'missing' => 'مفقود',
      'unidentified' => 'غير محدد',
      'deceased' => 'متوفي',
      default => $status
    };
  }

  private function getDnaStatusLabel(string $status): string
  {
    return match ($status) {
      'completed' => 'مكتمل',
      'failed' => 'فشل',
      'pending' => 'قيد الانتظار',
      default => $status
    };
  }
}
