<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\DnaTest;
use App\Models\Family;
use App\Models\Individual;
use App\Models\User;

class DashboardController extends Controller
{
  public function index(): void
  {
    $familyModel = new Family();
    $individualModel = new Individual();
    $dnaModel = new DnaTest();
    $userModel = new User();

    $stats = [
      'families'      => $familyModel->countAll(),
      'dna_tests'     => $dnaModel->countAll(),
      'individuals'   => $individualModel->countAll(),
      'missing'       => $individualModel->countByStatus('missing'),
      'males'         => $individualModel->countByGender('male'),
      'females'       => $individualModel->countByGender('female'),
      'unidentified'  => $individualModel->countByStatus('unidentified'),
      'deceased'      => $individualModel->countByStatus('deceased'),
      'users'         => $userModel->countActive(),
    ];

    $this->render('dashboard/index', [
      'title' => 'لوحة التحكم',
      'stats' => $stats,
    ]);
  }

  public function notifications(): void
  {
    if (!\App\Core\Auth::hasPermission('users.view')) {
      $this->json(['success' => false, 'message' => 'ليس لديك صلاحية لعرض سجل النشاط'], 403);
    }

    $db = \App\Core\Database::getInstance();
    $stmt = $db->prepare(
      'SELECT al.*, u.name as user_name FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT 10'
    );
    $stmt->execute();
    $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $this->json(['success' => true, 'data' => $logs]);
  }
}
