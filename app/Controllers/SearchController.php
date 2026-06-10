<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\DnaTest;
use App\Models\Family;
use App\Models\Individual;
use App\Models\User;

class SearchController extends Controller
{
  public function global(): void
  {
    $query = trim($_GET['q'] ?? '');
    if (mb_strlen($query) < 2) {
      $this->json(['success' => true, 'results' => []]);
    }

    $familyModel = new Family();
    $individualModel = new Individual();
    $dnaModel = new DnaTest();
    $userModel = new User();

    $results = [];

    if (\App\Core\Auth::hasPermission('families.view')) {
      foreach ($familyModel->search($query) as $item) {
        $results[] = [
          'type'  => 'family',
          'label' => 'عائلة',
          'title' => $item['family_name'],
          'subtitle' => $item['family_code'],
          'url'   => '/DNA/families/show/' . $item['id'],
          'icon'  => 'fa-people-roof',
        ];
      }
    }

    if (\App\Core\Auth::hasPermission('individuals.view')) {
      foreach ($individualModel->search($query) as $item) {
        $results[] = [
          'type'  => 'individual',
          'label' => 'فرد',
          'title' => $item['name'],
          'subtitle' => $item['national_id'] ?? '',
          'url'   => '/DNA/individuals/show/' . $item['id'],
          'icon'  => 'fa-user',
        ];
      }
    }

    if (\App\Core\Auth::hasPermission('dna.view')) {
      foreach ($dnaModel->search($query) as $item) {
        $results[] = [
          'type'  => 'dna',
          'label' => 'فحص DNA',
          'title' => $item['person_name'],
          'subtitle' => $item['sample_date'] ?? '',
          'url'   => '/DNA/dna-tests/show/' . $item['id'],
          'icon'  => 'fa-dna',
        ];
      }
    }

    if (\App\Core\Auth::hasPermission('users.view')) {
      foreach ($userModel->search($query) as $item) {
        $results[] = [
          'type'  => 'user',
          'label' => 'مستخدم',
          'title' => $item['name'],
          'subtitle' => $item['email'],
          'url'   => '/DNA/users/edit/' . $item['id'],
          'icon'  => 'fa-user-shield',
        ];
      }
    }

    $this->json(['success' => true, 'results' => $results]);
  }
}
