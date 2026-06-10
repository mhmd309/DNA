<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\DnaTest;
use App\Models\Family;
use App\Models\Individual;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    try {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('تقرير العائلات');

      // Headers
      $sheet->setCellValue('A1', '#');
      $sheet->setCellValue('B1', 'اسم العائلة');
      $sheet->setCellValue('C1', 'كود العائلة');
      $sheet->setCellValue('D1', 'عدد الأعضاء');
      $sheet->setCellValue('E1', 'أنشئ بواسطة');
      $sheet->setCellValue('F1', 'التاريخ');

      // Set header styling
      $headerStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => 'E0E0E0']
        ]
      ];
      $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

      // Data
      $row = 2;
      foreach ($families as $i => $data) {
        $sheet->setCellValue('A' . $row, $i + 1);
        $sheet->setCellValue('B' . $row, $data['family_name']);
        $sheet->setCellValue('C' . $row, $data['family_code']);
        $sheet->setCellValue('D' . $row, $data['members_count']);
        $sheet->setCellValue('E' . $row, $data['created_by_name'] ?? 'غير محدد');
        $sheet->setCellValue('F' . $row, date('Y-m-d', strtotime($data['created_at'])));
        $row++;
      }

      // Auto size columns
      foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
      }

      // Set RTL
      $sheet->setRightToLeft(true);

      // Output
      $filename = 'تقرير_العائلات_' . date('Y-m-d_H-i-s') . '.xlsx';
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new Xlsx($spreadsheet);
      $writer->save('php://output');
      exit;
    } catch (\Exception) {
      // Fallback to HTML if PhpSpreadsheet fails
      $this->exportFamiliesToExcelFallback($families);
    }
  }

  private function exportIndividualsToExcel(array $individuals): void
  {
    try {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('تقرير الأفراد');

      // Headers
      $sheet->setCellValue('A1', '#');
      $sheet->setCellValue('B1', 'الاسم');
      $sheet->setCellValue('C1', 'الرقم القومي');
      $sheet->setCellValue('D1', 'رقم العينة');
      $sheet->setCellValue('E1', 'فصيلة الدم');
      $sheet->setCellValue('F1', 'تاريخ الميلاد');
      $sheet->setCellValue('G1', 'النوع');
      $sheet->setCellValue('H1', 'الحالة');
      $sheet->setCellValue('I1', 'العائلة');
      $sheet->setCellValue('J1', 'أنشئ بواسطة');
      $sheet->setCellValue('K1', 'التاريخ');

      // Set header styling
      $headerStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => 'E0E0E0']
        ]
      ];
      $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

      // Data
      $row = 2;
      foreach ($individuals as $i => $data) {
        $sheet->setCellValue('A' . $row, $i + 1);
        $sheet->setCellValue('B' . $row, $data['name']);
        $sheet->setCellValue('C' . $row, $data['national_id'] ?? '-');
        $sheet->setCellValue('D' . $row, $data['dna_sample_number'] ?? '-');
        $sheet->setCellValue('E' . $row, $data['blood_type'] ?? '-');
        $sheet->setCellValue('F' . $row, $data['birth_date'] ?? '-');
        $sheet->setCellValue('G' . $row, $data['gender'] === 'male' ? 'ذكر' : 'أنثى');
        $sheet->setCellValue('H' . $row, $this->getStatusLabel($data['status']));
        $sheet->setCellValue('I' . $row, $data['family_name'] ?? '-');
        $sheet->setCellValue('J' . $row, $data['created_by_name'] ?? 'غير محدد');
        $sheet->setCellValue('K' . $row, date('Y-m-d', strtotime($data['created_at'])));
        $row++;
      }

      // Auto size columns
      foreach (range('A', 'K') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
      }

      // Set RTL
      $sheet->setRightToLeft(true);

      // Output
      $filename = 'تقرير_الأفراد_' . date('Y-m-d_H-i-s') . '.xlsx';
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new Xlsx($spreadsheet);
      $writer->save('php://output');
      exit;
    } catch (\Exception) {
      // Fallback to HTML if PhpSpreadsheet fails
      $this->exportIndividualsToExcelFallback($individuals);
    }
  }

  private function exportDnaTestsToExcel(array $tests): void
  {
    try {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('تقرير فحوصات DNA');

      // Headers
      $sheet->setCellValue('A1', '#');
      $sheet->setCellValue('B1', 'اسم الشخص');
      $sheet->setCellValue('C1', 'رقم العينة');
      $sheet->setCellValue('D1', 'تاريخ العينة');
      $sheet->setCellValue('E1', 'المختبر');
      $sheet->setCellValue('F1', 'الموقع');
      $sheet->setCellValue('G1', 'اسم الطبيب');
      $sheet->setCellValue('H1', 'الحالة');
      $sheet->setCellValue('I1', 'أنشئ بواسطة');
      $sheet->setCellValue('J1', 'التاريخ');

      // Set header styling
      $headerStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => 'E0E0E0']
        ]
      ];
      $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

      // Data
      $row = 2;
      foreach ($tests as $i => $data) {
        $sheet->setCellValue('A' . $row, $i + 1);
        $sheet->setCellValue('B' . $row, $data['person_name']);
        $sheet->setCellValue('C' . $row, $data['sample_number']);
        $sheet->setCellValue('D' . $row, $data['sample_date'] ?? '-');
        $sheet->setCellValue('E' . $row, $data['lab_name'] ?? '-');
        $sheet->setCellValue('F' . $row, $data['lab_location'] ?? '-');
        $sheet->setCellValue('G' . $row, $data['doctor_name'] ?? '-');
        $sheet->setCellValue('H' . $row, $this->getDnaStatusLabel($data['status']));
        $sheet->setCellValue('I' . $row, $data['created_by_name'] ?? 'غير محدد');
        $sheet->setCellValue('J' . $row, date('Y-m-d', strtotime($data['created_at'])));
        $row++;
      }

      // Auto size columns
      foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
      }

      // Set RTL
      $sheet->setRightToLeft(true);

      // Output
      $filename = 'تقرير_فحوصات_DNA_' . date('Y-m-d_H-i-s') . '.xlsx';
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new Xlsx($spreadsheet);
      $writer->save('php://output');
      exit;
    } catch (\Exception) {
      // Fallback to HTML if PhpSpreadsheet fails
      $this->exportDnaTestsToExcelFallback($tests);
    }
  }

  private function exportUsersToExcel(array $users): void
  {
    try {
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('تقرير المستخدمين');

      // Headers
      $sheet->setCellValue('A1', '#');
      $sheet->setCellValue('B1', 'الاسم');
      $sheet->setCellValue('C1', 'البريد الإلكتروني');
      $sheet->setCellValue('D1', 'الدور');
      $sheet->setCellValue('E1', 'الحالة');
      $sheet->setCellValue('F1', 'التاريخ');

      // Set header styling
      $headerStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'fill' => [
          'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
          'startColor' => ['rgb' => 'E0E0E0']
        ]
      ];
      $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

      // Data
      $row = 2;
      foreach ($users as $i => $data) {
        $sheet->setCellValue('A' . $row, $i + 1);
        $sheet->setCellValue('B' . $row, $data['name']);
        $sheet->setCellValue('C' . $row, $data['email']);
        $sheet->setCellValue('D' . $row, roleLabel($data['role']));
        $sheet->setCellValue('E' . $row, $data['is_active'] ? 'نشط' : 'معطل');
        $sheet->setCellValue('F' . $row, date('Y-m-d', strtotime($data['created_at'])));
        $row++;
      }

      // Auto size columns
      foreach (range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
      }

      // Set RTL
      $sheet->setRightToLeft(true);

      // Output
      $filename = 'تقرير_المستخدمين_' . date('Y-m-d_H-i-s') . '.xlsx';
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $filename . '"');
      header('Cache-Control: max-age=0');

      $writer = new Xlsx($spreadsheet);
      $writer->save('php://output');
      exit;
    } catch (\Exception) {
      // Fallback to HTML if PhpSpreadsheet fails
      $this->exportUsersToExcelFallback($users);
    }
  }

  // Fallback functions if PhpSpreadsheet not installed
  private function exportFamiliesToExcelFallback(array $families): void
  {
    $filename = 'تقرير_العائلات_' . date('Y-m-d_H-i-s') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<meta http-equiv="content-type" content="application/vnd.ms-excel; charset=utf-8">';
    echo '<head><style>table{border-collapse:collapse;}td,th{border:1px solid #000;padding:5px;text-align:center;}</style></head>';
    echo '<body><table>';
    echo '<tr><th>#</th><th>اسم العائلة</th><th>كود العائلة</th><th>عدد الأعضاء</th><th>أنشئ بواسطة</th><th>التاريخ</th></tr>';

    foreach ($families as $i => $row) {
      echo '<tr>';
      echo '<td>' . ($i + 1) . '</td>';
      echo '<td>' . htmlspecialchars($row['family_name']) . '</td>';
      echo '<td>' . htmlspecialchars($row['family_code']) . '</td>';
      echo '<td>' . $row['members_count'] . '</td>';
      echo '<td>' . htmlspecialchars($row['created_by_name'] ?? 'غير محدد') . '</td>';
      echo '<td>' . date('Y-m-d', strtotime($row['created_at'])) . '</td>';
      echo '</tr>';
    }

    echo '</table></body></html>';
    exit;
  }

  private function exportIndividualsToExcelFallback(array $individuals): void
  {
    $filename = 'تقرير_الأفراد_' . date('Y-m-d_H-i-s') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<meta http-equiv="content-type" content="application/vnd.ms-excel; charset=utf-8">';
    echo '<head><style>table{border-collapse:collapse;}td,th{border:1px solid #000;padding:5px;text-align:center;}</style></head>';
    echo '<body><table>';
    echo '<tr><th>#</th><th>الاسم</th><th>الرقم القومي</th><th>رقم العينة</th><th>فصيلة الدم</th><th>تاريخ الميلاد</th><th>النوع</th><th>الحالة</th><th>العائلة</th><th>أنشئ بواسطة</th><th>التاريخ</th></tr>';

    foreach ($individuals as $i => $row) {
      echo '<tr>';
      echo '<td>' . ($i + 1) . '</td>';
      echo '<td>' . htmlspecialchars($row['name']) . '</td>';
      echo '<td>' . htmlspecialchars($row['national_id'] ?? '-') . '</td>';
      echo '<td>' . htmlspecialchars($row['dna_sample_number'] ?? '-') . '</td>';
      echo '<td>' . htmlspecialchars($row['blood_type'] ?? '-') . '</td>';
      echo '<td>' . htmlspecialchars($row['birth_date'] ?? '-') . '</td>';
      echo '<td>' . ($row['gender'] === 'male' ? 'ذكر' : 'أنثى') . '</td>';
      echo '<td>' . $this->getStatusLabel($row['status']) . '</td>';
      echo '<td>' . htmlspecialchars($row['family_name'] ?? '-') . '</td>';
      echo '<td>' . htmlspecialchars($row['created_by_name'] ?? 'غير محدد') . '</td>';
      echo '<td>' . date('Y-m-d', strtotime($row['created_at'])) . '</td>';
      echo '</tr>';
    }

    echo '</table></body></html>';
    exit;
  }

  private function exportDnaTestsToExcelFallback(array $tests): void
  {
    $filename = 'تقرير_فحوصات_DNA_' . date('Y-m-d_H-i-s') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<meta http-equiv="content-type" content="application/vnd.ms-excel; charset=utf-8">';
    echo '<head><style>table{border-collapse:collapse;}td,th{border:1px solid #000;padding:5px;text-align:center;}</style></head>';
    echo '<body><table>';
    echo '<tr><th>#</th><th>اسم الشخص</th><th>رقم العينة</th><th>تاريخ العينة</th><th>المختبر</th><th>الموقع</th><th>اسم الطبيب</th><th>الحالة</th><th>أنشئ بواسطة</th><th>التاريخ</th></tr>';

    foreach ($tests as $i => $row) {
      echo '<tr>';
      echo '<td>' . ($i + 1) . '</td>';
      echo '<td>' . htmlspecialchars($row['person_name']) . '</td>';
      echo '<td>' . htmlspecialchars($row['sample_number']) . '</td>';
      echo '<td>' . htmlspecialchars($row['sample_date'] ?? '-') . '</td>';
      echo '<td>' . htmlspecialchars($row['lab_name'] ?? '-') . '</td>';
      echo '<td>' . htmlspecialchars($row['lab_location'] ?? '-') . '</td>';
      echo '<td>' . htmlspecialchars($row['doctor_name'] ?? '-') . '</td>';
      echo '<td>' . $this->getDnaStatusLabel($row['status']) . '</td>';
      echo '<td>' . htmlspecialchars($row['created_by_name'] ?? 'غير محدد') . '</td>';
      echo '<td>' . date('Y-m-d', strtotime($row['created_at'])) . '</td>';
      echo '</tr>';
    }

    echo '</table></body></html>';
    exit;
  }

  private function exportUsersToExcelFallback(array $users): void
  {
    $filename = 'تقرير_المستخدمين_' . date('Y-m-d_H-i-s') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<meta http-equiv="content-type" content="application/vnd.ms-excel; charset=utf-8">';
    echo '<head><style>table{border-collapse:collapse;}td,th{border:1px solid #000;padding:5px;text-align:center;}</style></head>';
    echo '<body><table>';
    echo '<tr><th>#</th><th>الاسم</th><th>البريد الإلكتروني</th><th>الدور</th><th>الحالة</th><th>التاريخ</th></tr>';

    foreach ($users as $i => $row) {
      echo '<tr>';
      echo '<td>' . ($i + 1) . '</td>';
      echo '<td>' . htmlspecialchars($row['name']) . '</td>';
      echo '<td>' . htmlspecialchars($row['email']) . '</td>';
      echo '<td>' . roleLabel($row['role']) . '</td>';
      echo '<td>' . ($row['is_active'] ? 'نشط' : 'معطل') . '</td>';
      echo '<td>' . date('Y-m-d', strtotime($row['created_at'])) . '</td>';
      echo '</tr>';
    }

    echo '</table></body></html>';
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
