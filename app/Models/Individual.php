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
      $where .= ' AND (i.name LIKE ? OR i.national_id LIKE ?)';
      $like = "%{$search}%";
      $params = [$like, $like];
      $types = 'ss';
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
    $bloodType = $data['blood_type'] ?: null;
    $birthDate = $data['birth_date'] ?: null;
    $d3s1358_1 = $data['D3S1358_1'] ?? null;
    $d3s1358_2 = $data['D3S1358_2'] ?? null;
    $vwa_1 = $data['vWA_1'] ?? null;
    $vwa_2 = $data['vWA_2'] ?? null;
    $fga_1 = $data['FGA_1'] ?? null;
    $fga_2 = $data['FGA_2'] ?? null;
    $d8s1179_1 = $data['D8S1179_1'] ?? null;
    $d8s1179_2 = $data['D8S1179_2'] ?? null;
    $d21s11_1 = $data['D21S11_1'] ?? null;
    $d21s11_2 = $data['D21S11_2'] ?? null;

    $stmt = $this->db->prepare(
      'INSERT INTO individuals (name, national_id, blood_type, birth_date, gender, family_id, status, D3S1358_1, D3S1358_2, vWA_1, vWA_2, FGA_1, FGA_2, D8S1179_1, D8S1179_2, D21S11_1, D21S11_2, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
      'sssssisssssssssssi',
      $data['name'],
      $nationalId,
      $bloodType,
      $birthDate,
      $data['gender'],
      $familyId,
      $data['status'],
      $d3s1358_1,
      $d3s1358_2,
      $vwa_1,
      $vwa_2,
      $fga_1,
      $fga_2,
      $d8s1179_1,
      $d8s1179_2,
      $d21s11_1,
      $d21s11_2,
      $userId
    );
    $stmt->execute();
    return $this->db->lastInsertId();
  }

  public function update(int $id, array $data): bool
  {
    $familyId = !empty($data['family_id']) ? (int) $data['family_id'] : null;
    $nationalId = $data['national_id'] ?: null;
    $bloodType = $data['blood_type'] ?: null;
    $birthDate = $data['birth_date'] ?: null;
    $d3s1358_1 = $data['D3S1358_1'] ?? null;
    $d3s1358_2 = $data['D3S1358_2'] ?? null;
    $vwa_1 = $data['vWA_1'] ?? null;
    $vwa_2 = $data['vWA_2'] ?? null;
    $fga_1 = $data['FGA_1'] ?? null;
    $fga_2 = $data['FGA_2'] ?? null;
    $d8s1179_1 = $data['D8S1179_1'] ?? null;
    $d8s1179_2 = $data['D8S1179_2'] ?? null;
    $d21s11_1 = $data['D21S11_1'] ?? null;
    $d21s11_2 = $data['D21S11_2'] ?? null;

    $stmt = $this->db->prepare(
      'UPDATE individuals SET name = ?, national_id = ?, blood_type = ?, birth_date = ?, gender = ?, family_id = ?, status = ?, D3S1358_1 = ?, D3S1358_2 = ?, vWA_1 = ?, vWA_2 = ?, FGA_1 = ?, FGA_2 = ?, D8S1179_1 = ?, D8S1179_2 = ?, D21S11_1 = ?, D21S11_2 = ? WHERE id = ?'
    );
    $stmt->bind_param(
      'sssssisssssssssssi',
      $data['name'],
      $nationalId,
      $bloodType,
      $birthDate,
      $data['gender'],
      $familyId,
      $data['status'],
      $d3s1358_1,
      $d3s1358_2,
      $vwa_1,
      $vwa_2,
      $fga_1,
      $fga_2,
      $d8s1179_1,
      $d8s1179_2,
      $d21s11_1,
      $d21s11_2,
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

    // عند التعديل، لا نتحقق من family_members لأنه قد يكون نفس الشخص
    if ($excludeId) {
      return false;
    }

    $stmt2 = $this->db->prepare('SELECT id FROM family_members WHERE national_id = ? AND deleted_at IS NULL');
    $stmt2->bind_param('s', $nationalId);
    $stmt2->execute();
    return (bool) $stmt2->get_result()->fetch_assoc();
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
      'UPDATE individuals SET deleted_at = NOW(), national_id = NULL WHERE id = ?'
    );
    $stmt->bind_param('i', $id);
    return $stmt->execute();
  }

  public function getAllForReport(): array
  {
    $stmt = $this->db->prepare("
      SELECT i.id, i.name, i.national_id, i.blood_type,
        i.birth_date, i.gender, i.status, f.family_name, u.name as created_by_name, i.created_at,
        i.D3S1358_1, i.D3S1358_2, i.vWA_1, i.vWA_2, i.FGA_1, i.FGA_2, i.D8S1179_1, i.D8S1179_2, i.D21S11_1, i.D21S11_2
      FROM individuals i
      LEFT JOIN families f ON f.id = i.family_id
      LEFT JOIN users u ON u.id = i.created_by
      WHERE i.deleted_at IS NULL
      ORDER BY i.created_at DESC
    ");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getAllWithDna(): array
  {
    $stmt = $this->db->prepare("
      SELECT 
        i.*,
        f.family_name,
        f.family_code,
        'individual' as source
      FROM individuals i
      LEFT JOIN families f ON i.family_id = f.id
      WHERE i.deleted_at IS NULL 
        AND i.D3S1358_1 IS NOT NULL 
        AND i.D3S1358_2 IS NOT NULL
    ");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }
}
