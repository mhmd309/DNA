<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Family extends Model
{
  protected string $table = 'families';

  public function getAll(int $page, int $perPage, string $search = ''): array
  {
    $where = 'f.deleted_at IS NULL';
    $params = [];
    $types = '';

    if ($search !== '') {
      $where .= ' AND (f.family_name LIKE ? OR f.family_code LIKE ?)';
      $like = "%{$search}%";
      $params = [$like, $like];
      $types = 'ss';
    }

    $sql = "SELECT f.*,
                (SELECT name FROM family_members WHERE family_id = f.id AND role = 'father' AND deleted_at IS NULL LIMIT 1) as father_name,
                (SELECT national_id FROM family_members WHERE family_id = f.id AND role = 'father' AND deleted_at IS NULL LIMIT 1) as father_national_id,
                (SELECT name FROM family_members WHERE family_id = f.id AND role = 'mother' AND deleted_at IS NULL LIMIT 1) as mother_name,
                (SELECT national_id FROM family_members WHERE family_id = f.id AND role = 'mother' AND deleted_at IS NULL LIMIT 1) as mother_national_id,
                (SELECT COUNT(*) FROM family_members WHERE family_id = f.id AND role = 'child' AND deleted_at IS NULL) as children_count
                FROM families f WHERE {$where} ORDER BY f.created_at DESC";

    $countSql = "SELECT COUNT(*) as total FROM families f WHERE {$where}";

    return $this->paginate($sql, $countSql, $params, $types, $page, $perPage);
  }

  public function getWithMembers(int $id): ?array
  {
    $family = $this->find($id);
    if (!$family) {
      return null;
    }

    $stmt = $this->db->prepare(
      "SELECT * FROM family_members WHERE family_id = ? AND deleted_at IS NULL ORDER BY FIELD(role,'father','mother','child'), sort_order, id"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $family['father'] = null;
    $family['mother'] = null;
    $family['children'] = [];

    foreach ($members as $member) {
      if ($member['role'] === 'father') {
        $family['father'] = $member;
      } elseif ($member['role'] === 'mother') {
        $family['mother'] = $member;
      } else {
        $family['children'][] = $member;
      }
    }

    if (empty($family['children'])) {
      $stmt2 = $this->db->prepare(
        "SELECT * FROM family_members WHERE family_id = ? AND role = 'child' AND deleted_at IS NOT NULL ORDER BY deleted_at DESC, sort_order, id"
      );
      $stmt2->bind_param('i', $id);
      $stmt2->execute();
      $deletedChildren = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
      if (!empty($deletedChildren)) {
        $family['children'] = $deletedChildren;
      }
    }

    return $family;
  }

  public function create(array $familyData, array $father, array $mother, array $children, int $userId): int
  {
    $this->db->beginTransaction();
    try {
      $familyName = $familyData['family_name'];
      $familyCode = $familyData['family_code'];
      $notes = $familyData['notes'] ?? '';
      $stmt = $this->db->prepare(
        'INSERT INTO families (family_name, family_code, notes, created_by) VALUES (?, ?, ?, ?)'
      );
      $stmt->bind_param('sssi', $familyName, $familyCode, $notes, $userId);
      $stmt->execute();
      $familyId = $this->db->lastInsertId();

      $this->insertMember($familyId, 'father', $father, 0);
      $this->insertMember($familyId, 'mother', $mother, 0);

      foreach ($children as $i => $child) {
        $this->insertMember($familyId, 'child', $child, $i);
      }

      $this->db->commit();
      return $familyId;
    } catch (\Throwable $e) {
      $this->db->rollback();
      throw $e;
    }
  }

  public function updateFamily(int $id, array $familyData, array $father, array $mother, array $children): void
  {
    $this->db->beginTransaction();
    try {
      $familyName = $familyData['family_name'];
      $familyCode = $familyData['family_code'];
      $notes = $familyData['notes'] ?? '';
      $stmt = $this->db->prepare(
        'UPDATE families SET family_name = ?, family_code = ?, notes = ? WHERE id = ?'
      );
      $stmt->bind_param('sssi', $familyName, $familyCode, $notes, $id);
      $stmt->execute();

      $fatherId = !empty($father['id']) ? (int) $father['id'] : null;
      $motherId = !empty($mother['id']) ? (int) $mother['id'] : null;

      if ($fatherId) {
        $this->updateMember($id, $fatherId, 'father', $father, 0);
      } else {
        $this->insertMember($id, 'father', $father, 0);
      }

      if ($motherId) {
        $this->updateMember($id, $motherId, 'mother', $mother, 0);
      } else {
        $this->insertMember($id, 'mother', $mother, 0);
      }

      $submittedChildIds = [];
      foreach ($children as $i => $child) {
        $childId = !empty($child['id']) ? (int) $child['id'] : null;
        if ($childId) {
          $submittedChildIds[] = $childId;
          $this->updateMember($id, $childId, 'child', $child, $i);
        } else {
          $this->insertMember($id, 'child', $child, $i);
        }
      }

      $this->softDeleteMissingChildren($id, $submittedChildIds);

      $this->db->commit();
    } catch (\Throwable $e) {
      $this->db->rollback();
      throw $e;
    }
  }

  private function insertMember(int $familyId, string $role, array $data, int $sortOrder): void
  {
    $name = $data['name'];
    $nationalId = !empty($data['national_id']) ? $data['national_id'] : null;
    $bloodType = !empty($data['blood_type']) ? $data['blood_type'] : null;
    $phone = !empty($data['phone']) ? $data['phone'] : null;
    $birthDate = !empty($data['birth_date']) ? $data['birth_date'] : null;
    $address = !empty($data['address']) ? $data['address'] : null;
    $gender = !empty($data['gender']) ? $data['gender'] : null;
    $idCard = !empty($data['id_card_image']) ? $data['id_card_image'] : null;
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
      'INSERT INTO family_members (family_id, role, name, national_id, blood_type, phone, birth_date, address, gender, id_card_image, sort_order, D3S1358_1, D3S1358_2, vWA_1, vWA_2, FGA_1, FGA_2, D8S1179_1, D8S1179_2, D21S11_1, D21S11_2)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
      'isssssssssissssssssss',
      $familyId,
      $role,
      $name,
      $nationalId,
      $bloodType,
      $phone,
      $birthDate,
      $address,
      $gender,
      $idCard,
      $sortOrder,
      $d3s1358_1,
      $d3s1358_2,
      $vwa_1,
      $vwa_2,
      $fga_1,
      $fga_2,
      $d8s1179_1,
      $d8s1179_2,
      $d21s11_1,
      $d21s11_2
    );
    $stmt->execute();
  }

  private function updateMember(int $familyId, int $memberId, string $role, array $data, int $sortOrder): void
  {
    $name = $data['name'];
    $nationalId = !empty($data['national_id']) ? $data['national_id'] : null;
    $bloodType = !empty($data['blood_type']) ? $data['blood_type'] : null;
    $phone = !empty($data['phone']) ? $data['phone'] : null;
    $birthDate = !empty($data['birth_date']) ? $data['birth_date'] : null;
    $address = !empty($data['address']) ? $data['address'] : null;
    $gender = !empty($data['gender']) ? $data['gender'] : null;
    $idCard = !empty($data['id_card_image']) ? $data['id_card_image'] : null;
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
      'UPDATE family_members
             SET name = ?, national_id = ?, blood_type = ?, phone = ?, birth_date = ?, address = ?, gender = ?, id_card_image = ?, sort_order = ?, deleted_at = NULL, D3S1358_1 = ?, D3S1358_2 = ?, vWA_1 = ?, vWA_2 = ?, FGA_1 = ?, FGA_2 = ?, D8S1179_1 = ?, D8S1179_2 = ?, D21S11_1 = ?, D21S11_2 = ?
             WHERE id = ? AND family_id = ? AND role = ?'
    );
    $stmt->bind_param(
      'ssssssssissssssssssiis',
      $name,
      $nationalId,
      $bloodType,
      $phone,
      $birthDate,
      $address,
      $gender,
      $idCard,
      $sortOrder,
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
      $memberId,
      $familyId,
      $role
    );
    $stmt->execute();
  }

  private function softDeleteMissingChildren(int $familyId, array $keepIds): void
  {
    $sql = "UPDATE family_members
                SET deleted_at = NOW(), national_id = NULL, phone = NULL
                WHERE family_id = ? AND role = 'child' AND deleted_at IS NULL";
    $params = [$familyId];
    $types = 'i';

    if (!empty($keepIds)) {
      $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
      $sql .= " AND id NOT IN ($placeholders)";
      $types .= str_repeat('i', count($keepIds));
      $params = array_merge($params, $keepIds);
    }

    $stmt = $this->db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
  }

  public function codeExists(string $code, ?int $excludeId = null): bool
  {
    return $this->fieldExists('family_code', $code, $excludeId);
  }

  public function nationalIdExists(string $nationalId, $excludeMemberIds = null): bool
  {
    if (empty($nationalId)) return false;
    $sql = 'SELECT id FROM family_members WHERE national_id = ? AND deleted_at IS NULL';
    $params = [$nationalId];
    $types = 's';

    if ($excludeMemberIds) {
      if (!is_array($excludeMemberIds)) {
        $excludeMemberIds = [$excludeMemberIds];
      }
      if (!empty($excludeMemberIds)) {
        $placeholders = implode(',', array_fill(0, count($excludeMemberIds), '?'));
        $sql .= " AND id NOT IN ($placeholders)";
        $types .= str_repeat('i', count($excludeMemberIds));
        $params = array_merge($params, $excludeMemberIds);
      }
    }

    $stmt = $this->db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_assoc();
  }

  public function phoneExists(string $phone, $excludeMemberIds = null): bool
  {
    if (empty($phone)) return false;
    $sql = 'SELECT id FROM family_members WHERE phone = ? AND deleted_at IS NULL';
    $params = [$phone];
    $types = 's';

    if ($excludeMemberIds) {
      if (!is_array($excludeMemberIds)) {
        $excludeMemberIds = [$excludeMemberIds];
      }
      if (!empty($excludeMemberIds)) {
        $placeholders = implode(',', array_fill(0, count($excludeMemberIds), '?'));
        $sql .= " AND id NOT IN ($placeholders)";
        $types .= str_repeat('i', count($excludeMemberIds));
        $params = array_merge($params, $excludeMemberIds);
      }
    }

    $stmt = $this->db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_assoc();
  }

  private function fieldExists(string $field, string $value, ?int $excludeId): bool
  {
    $sql = "SELECT id FROM families WHERE {$field} = ? AND deleted_at IS NULL";
    if ($excludeId) {
      $sql .= ' AND id != ?';
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('si', $value, $excludeId);
    } else {
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('s', $value);
    }
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_assoc();
  }

  public function search(string $query, int $limit = 5): array
  {
    $like = "%{$query}%";
    $stmt = $this->db->prepare(
      'SELECT id, family_name, family_code FROM families WHERE deleted_at IS NULL AND (family_name LIKE ? OR family_code LIKE ?) LIMIT ?'
    );
    $stmt->bind_param('ssi', $like, $like, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function searchForSelect(string $query, int $limit = 20): array
  {
    $like = "%{$query}%";
    $stmt = $this->db->prepare(
      'SELECT id, family_name, family_code FROM families WHERE deleted_at IS NULL AND (family_name LIKE ? OR family_code LIKE ?) ORDER BY family_name LIMIT ?'
    );
    $stmt->bind_param('ssi', $like, $like, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function countAll(): int
  {
    return $this->count('deleted_at IS NULL');
  }

  public function softDelete(int $id): bool
  {
    $stmt = $this->db->prepare(
      'UPDATE family_members SET deleted_at = NOW(), national_id = NULL, phone = NULL WHERE family_id = ? AND deleted_at IS NULL'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return parent::softDelete($id);
  }

  public function getAllForReport(): array
  {
    $stmt = $this->db->prepare(
      'SELECT f.id, f.family_name, f.family_code, f.created_at, u.name as created_by_name,
             COUNT(DISTINCT fm.id) as members_count
             FROM families f
             LEFT JOIN users u ON u.id = f.created_by
             LEFT JOIN family_members fm ON fm.family_id = f.id AND fm.deleted_at IS NULL
             WHERE f.deleted_at IS NULL
             GROUP BY f.id
             ORDER BY f.created_at DESC'
    );
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }
}
