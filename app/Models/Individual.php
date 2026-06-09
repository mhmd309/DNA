<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Individual extends Model
{
  protected string $table = 'individuals';

  public function getAll(int $page, int $perPage, string $search = ''): array
  {
    $where = 'i.deleted_at IS NULL';
    $params = [];
    $types = '';

    if ($search !== '') {
      $where .= ' AND (i.name LIKE ? OR i.national_id LIKE ? OR i.dna_sample_number LIKE ?)';
      $like = "%{$search}%";
      $params = [$like, $like, $like];
      $types = 'sss';
    }

    $sql = "SELECT i.*, f.family_name, f.family_code
                FROM individuals i
                LEFT JOIN families f ON f.id = i.family_id
                WHERE {$where}
                ORDER BY i.created_at DESC";

    $countSql = "SELECT COUNT(*) as total FROM individuals i WHERE {$where}";

    return $this->paginate($sql, $countSql, $params, $types, $page, $perPage);
  }

  public function findWithFamily(int $id): ?array
  {
    $stmt = $this->db->prepare(
      'SELECT i.*, f.family_name, f.family_code FROM individuals i
             LEFT JOIN families f ON f.id = i.family_id
             WHERE i.id = ? AND i.deleted_at IS NULL LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
  }

  public function create(array $data, int $userId): int
  {
    $familyId = !empty($data['family_id']) ? (int) $data['family_id'] : null;
    $nationalId = $data['national_id'] ?: null;
    $dnaSample = $data['dna_sample_number'] ?: null;
    $bloodType = $data['blood_type'] ?: null;
    $birthDate = $data['birth_date'] ?: null;

    $stmt = $this->db->prepare(
      'INSERT INTO individuals (name, national_id, dna_sample_number, blood_type, birth_date, gender, family_id, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
      'ssssssisi',
      $data['name'],
      $nationalId,
      $dnaSample,
      $bloodType,
      $birthDate,
      $data['gender'],
      $familyId,
      $data['status'],
      $userId
    );
    $stmt->execute();
    return $this->db->lastInsertId();
  }

  public function update(int $id, array $data): bool
  {
    $familyId = !empty($data['family_id']) ? (int) $data['family_id'] : null;
    $nationalId = $data['national_id'] ?: null;
    $dnaSample = $data['dna_sample_number'] ?: null;
    $bloodType = $data['blood_type'] ?: null;
    $birthDate = $data['birth_date'] ?: null;

    $stmt = $this->db->prepare(
      'UPDATE individuals SET name = ?, national_id = ?, dna_sample_number = ?, blood_type = ?, birth_date = ?, gender = ?, family_id = ?, status = ? WHERE id = ?'
    );
    $stmt->bind_param(
      'ssssssisi',
      $data['name'],
      $nationalId,
      $dnaSample,
      $bloodType,
      $birthDate,
      $data['gender'],
      $familyId,
      $data['status'],
      $id
    );
    return $stmt->execute();
  }

  public function nationalIdExists(string $nationalId, ?int $excludeId = null): bool
  {
    if (empty($nationalId)) return false;

    $sql = 'SELECT id FROM individuals WHERE national_id = ? AND deleted_at IS NULL';
    if ($excludeId) {
      $sql .= ' AND id != ?';
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('si', $nationalId, $excludeId);
    } else {
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('s', $nationalId);
    }
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) return true;

    $stmt2 = $this->db->prepare('SELECT id FROM family_members WHERE national_id = ? AND deleted_at IS NULL');
    $stmt2->bind_param('s', $nationalId);
    $stmt2->execute();
    return (bool) $stmt2->get_result()->fetch_assoc();
  }

  public function dnaSampleExists(string $sample, ?int $excludeId = null): bool
  {
    if (empty($sample)) return false;

    $sql = 'SELECT id FROM individuals WHERE dna_sample_number = ? AND deleted_at IS NULL';
    if ($excludeId) {
      $sql .= ' AND id != ?';
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('si', $sample, $excludeId);
    } else {
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('s', $sample);
    }
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) return true;

    $stmt2 = $this->db->prepare('SELECT id FROM family_members WHERE dna_sample_number = ? AND deleted_at IS NULL');
    $stmt2->bind_param('s', $sample);
    $stmt2->execute();
    if ($stmt2->get_result()->fetch_assoc()) return true;

    $stmt3 = $this->db->prepare('SELECT id FROM dna_tests WHERE sample_number = ? AND deleted_at IS NULL');
    $stmt3->bind_param('s', $sample);
    $stmt3->execute();
    return (bool) $stmt3->get_result()->fetch_assoc();
  }

  public function search(string $query, int $limit = 5): array
  {
    $like = "%{$query}%";
    $stmt = $this->db->prepare(
      'SELECT id, name, national_id, status FROM individuals WHERE deleted_at IS NULL AND (name LIKE ? OR national_id LIKE ?) LIMIT ?'
    );
    $stmt->bind_param('ssi', $like, $like, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function countAll(): int
  {
    return $this->count('deleted_at IS NULL');
  }

  public function countByStatus(string $status): int
  {
    return $this->count('deleted_at IS NULL AND status = ?', [$status], 's');
  }

  public function countByGender(string $gender): int
  {
    return $this->count('deleted_at IS NULL AND gender = ?', [$gender], 's');
  }

  public function softDelete(int $id): bool
  {
    $stmt = $this->db->prepare(
      'UPDATE individuals SET deleted_at = NOW(), national_id = NULL, dna_sample_number = NULL WHERE id = ?'
    );
    $stmt->bind_param('i', $id);
    return $stmt->execute();
  }
}
