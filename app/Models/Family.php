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
                (SELECT dna_sample_number FROM family_members WHERE family_id = f.id AND role = 'father' AND deleted_at IS NULL LIMIT 1) as father_dna_sample,
                (SELECT name FROM family_members WHERE family_id = f.id AND role = 'mother' AND deleted_at IS NULL LIMIT 1) as mother_name,
                (SELECT national_id FROM family_members WHERE family_id = f.id AND role = 'mother' AND deleted_at IS NULL LIMIT 1) as mother_national_id,
                (SELECT dna_sample_number FROM family_members WHERE family_id = f.id AND role = 'mother' AND deleted_at IS NULL LIMIT 1) as mother_dna_sample,
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
        $dnaSample = !empty($data['dna_sample_number']) ? $data['dna_sample_number'] : null;
        $bloodType = !empty($data['blood_type']) ? $data['blood_type'] : null;
        $phone = !empty($data['phone']) ? $data['phone'] : null;
        $birthDate = !empty($data['birth_date']) ? $data['birth_date'] : null;
        $address = !empty($data['address']) ? $data['address'] : null;
        $gender = !empty($data['gender']) ? $data['gender'] : null;
        $idCard = !empty($data['id_card_image']) ? $data['id_card_image'] : null;

        $stmt = $this->db->prepare(
            'INSERT INTO family_members (family_id, role, name, national_id, dna_sample_number, blood_type, phone, birth_date, address, gender, id_card_image, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'issssssssssi',
            $familyId, $role, $name, $nationalId, $dnaSample, $bloodType,
            $phone, $birthDate, $address, $gender, $idCard, $sortOrder
        );
        $stmt->execute();
    }

    private function updateMember(int $familyId, int $memberId, string $role, array $data, int $sortOrder): void
    {
        $name = $data['name'];
        $nationalId = !empty($data['national_id']) ? $data['national_id'] : null;
        $dnaSample = !empty($data['dna_sample_number']) ? $data['dna_sample_number'] : null;
        $bloodType = !empty($data['blood_type']) ? $data['blood_type'] : null;
        $phone = !empty($data['phone']) ? $data['phone'] : null;
        $birthDate = !empty($data['birth_date']) ? $data['birth_date'] : null;
        $address = !empty($data['address']) ? $data['address'] : null;
        $gender = !empty($data['gender']) ? $data['gender'] : null;
        $idCard = !empty($data['id_card_image']) ? $data['id_card_image'] : null;

        $stmt = $this->db->prepare(
            'UPDATE family_members
             SET name = ?, national_id = ?, dna_sample_number = ?, blood_type = ?, phone = ?, birth_date = ?, address = ?, gender = ?, id_card_image = ?, sort_order = ?, deleted_at = NULL
             WHERE id = ? AND family_id = ? AND role = ?'
        );
        $stmt->bind_param(
            'sssssssssiiis',
            $name,
            $nationalId,
            $dnaSample,
            $bloodType,
            $phone,
            $birthDate,
            $address,
            $gender,
            $idCard,
            $sortOrder,
            $memberId,
            $familyId,
            $role
        );
        $stmt->execute();
    }

    private function softDeleteMissingChildren(int $familyId, array $keepIds): void
    {
        $sql = "UPDATE family_members
                SET deleted_at = NOW(), national_id = NULL, dna_sample_number = NULL, phone = NULL
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

    public function upsertChildFromIndividual(array $individual): void
    {
        $familyId = !empty($individual['family_id']) ? (int) $individual['family_id'] : 0;
        if ($familyId <= 0) return;

        $this->upsertChildMember($familyId, [
            'name'              => $individual['name'] ?? '',
            'national_id'       => $individual['national_id'] ?? null,
            'dna_sample_number' => $individual['dna_sample_number'] ?? null,
            'blood_type'        => $individual['blood_type'] ?? null,
            'birth_date'        => $individual['birth_date'] ?? null,
            'gender'            => $individual['gender'] ?? null,
        ]);
    }

    public function upsertChildFromDnaTest(array $test, ?array $existingTest = null): void
    {
        $familyId = !empty($test['family_id']) ? (int) $test['family_id'] : 0;
        if ($familyId <= 0) return;

        $payload = [
            'name'              => $test['person_name'] ?? '',
            'national_id'       => null,
            'dna_sample_number' => $test['sample_number'] ?? null,
            'blood_type'        => null,
            'birth_date'        => null,
            'gender'            => null,
        ];

        if (!empty($payload['dna_sample_number'])) {
            $found = $this->findMemberByDnaSample((string) $payload['dna_sample_number']);
            if (!$found && $existingTest && !empty($existingTest['sample_number'])) {
                $foundOld = $this->findMemberByDnaSample((string) $existingTest['sample_number']);
                if ($foundOld) {
                    $this->updateChildMemberById((int) $foundOld['id'], $familyId, $payload);
                    return;
                }
            }
        }

        $this->upsertChildMember($familyId, $payload);
    }

    public function softDeleteChildByIdentifiers(int $familyId, ?string $nationalId, ?string $dnaSampleNumber): void
    {
        $familyId = (int) $familyId;
        if ($familyId <= 0) return;

        $nationalId = $nationalId ? trim($nationalId) : null;
        $dnaSampleNumber = $dnaSampleNumber ? trim($dnaSampleNumber) : null;

        if (!$nationalId && !$dnaSampleNumber) return;

        $sql = "UPDATE family_members
                SET deleted_at = NOW(), national_id = NULL, dna_sample_number = NULL, phone = NULL
                WHERE family_id = ? AND role = 'child' AND deleted_at IS NULL AND (";
        $params = [$familyId];
        $types = 'i';

        if ($nationalId) {
            $sql .= "national_id = ?";
            $params[] = $nationalId;
            $types .= 's';
        }
        if ($dnaSampleNumber) {
            if ($nationalId) $sql .= " OR ";
            $sql .= "dna_sample_number = ?";
            $params[] = $dnaSampleNumber;
            $types .= 's';
        }
        $sql .= ')';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    private function upsertChildMember(int $familyId, array $data): void
    {
        $familyId = (int) $familyId;
        if ($familyId <= 0) return;

        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') return;

        $nationalId = !empty($data['national_id']) ? trim((string) $data['national_id']) : null;
        $dnaSample = !empty($data['dna_sample_number']) ? trim((string) $data['dna_sample_number']) : null;
        $bloodType = !empty($data['blood_type']) ? trim((string) $data['blood_type']) : null;
        $birthDate = !empty($data['birth_date']) ? trim((string) $data['birth_date']) : null;
        $gender = !empty($data['gender']) ? trim((string) $data['gender']) : null;

        $existing = null;
        if ($nationalId) {
            $existing = $this->findMemberByNationalId($nationalId);
        } elseif ($dnaSample) {
            $existing = $this->findMemberByDnaSample($dnaSample);
        } else {
            $existing = $this->findChildByHeuristics($familyId, $name, $birthDate, $gender);
        }

        if ($existing) {
            if (($existing['role'] ?? '') !== 'child') return;
            $this->updateChildMemberById((int) $existing['id'], $familyId, [
                'name'              => $name,
                'national_id'       => $nationalId,
                'dna_sample_number' => $dnaSample,
                'blood_type'        => $bloodType,
                'birth_date'        => $birthDate,
                'gender'            => $gender,
            ]);
            return;
        }

        $sortOrder = $this->nextChildSortOrder($familyId);
        $this->insertMember($familyId, 'child', [
            'name'              => $name,
            'national_id'       => $nationalId,
            'dna_sample_number' => $dnaSample,
            'blood_type'        => $bloodType,
            'phone'             => null,
            'birth_date'        => $birthDate,
            'address'           => null,
            'gender'            => $gender,
            'id_card_image'     => null,
        ], $sortOrder);
    }

    private function updateChildMemberById(int $memberId, int $familyId, array $payload): void
    {
        $memberId = (int) $memberId;
        $familyId = (int) $familyId;
        if ($memberId <= 0 || $familyId <= 0) return;

        $stmt0 = $this->db->prepare('SELECT family_id, sort_order FROM family_members WHERE id = ? AND role = \'child\' LIMIT 1');
        $stmt0->bind_param('i', $memberId);
        $stmt0->execute();
        $meta = $stmt0->get_result()->fetch_assoc();
        if (!$meta) return;

        $currentFamilyId = (int) ($meta['family_id'] ?? 0);
        $sortOrder = (int) ($meta['sort_order'] ?? 0);
        if ($currentFamilyId !== $familyId) {
            $sortOrder = $this->nextChildSortOrder($familyId);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $nationalId = !empty($payload['national_id']) ? trim((string) $payload['national_id']) : null;
        $dnaSample = !empty($payload['dna_sample_number']) ? trim((string) $payload['dna_sample_number']) : null;
        $bloodType = !empty($payload['blood_type']) ? trim((string) $payload['blood_type']) : null;
        $birthDate = !empty($payload['birth_date']) ? trim((string) $payload['birth_date']) : null;
        $gender = !empty($payload['gender']) ? trim((string) $payload['gender']) : null;

        $stmt = $this->db->prepare(
            'UPDATE family_members
             SET family_id = ?, name = ?, national_id = ?, dna_sample_number = ?, blood_type = ?, birth_date = ?, gender = ?, sort_order = ?, deleted_at = NULL
             WHERE id = ? AND role = \'child\''
        );
        $stmt->bind_param('issssssisii', $familyId, $name, $nationalId, $dnaSample, $bloodType, $birthDate, $gender, $sortOrder, $memberId);
        $stmt->execute();
    }

    private function nextChildSortOrder(int $familyId): int
    {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order), -1) as max_sort FROM family_members WHERE family_id = ? AND role = 'child'");
        $stmt->bind_param('i', $familyId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return ((int) ($row['max_sort'] ?? -1)) + 1;
    }

    private function findMemberByNationalId(string $nationalId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM family_members WHERE national_id = ? LIMIT 1');
        $stmt->bind_param('s', $nationalId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    private function findMemberByDnaSample(string $dnaSample): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM family_members WHERE dna_sample_number = ? LIMIT 1');
        $stmt->bind_param('s', $dnaSample);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    private function findChildByHeuristics(int $familyId, string $name, ?string $birthDate, ?string $gender): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM family_members
             WHERE family_id = ? AND role = 'child' AND name = ?
             AND (birth_date <=> ?) AND (gender <=> ?)
             LIMIT 1"
        );
        $stmt->bind_param('isss', $familyId, $name, $birthDate, $gender);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
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
        if ($stmt->get_result()->fetch_assoc()) return true;

        $stmt2 = $this->db->prepare('SELECT id FROM individuals WHERE national_id = ? AND deleted_at IS NULL');
        $stmt2->bind_param('s', $nationalId);
        $stmt2->execute();
        return (bool) $stmt2->get_result()->fetch_assoc();
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

    public function dnaSampleExists(string $sample, $excludeMemberIds = null): bool
    {
        if (empty($sample)) return false;

        $sql = 'SELECT id FROM family_members WHERE dna_sample_number = ? AND deleted_at IS NULL';
        $params = [$sample];
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
        if ($stmt->get_result()->fetch_assoc()) return true;

        $stmt2 = $this->db->prepare('SELECT id FROM individuals WHERE dna_sample_number = ? AND deleted_at IS NULL');
        $stmt2->bind_param('s', $sample);
        $stmt2->execute();
        if ($stmt2->get_result()->fetch_assoc()) return true;

        $stmt3 = $this->db->prepare('SELECT id FROM dna_tests WHERE sample_number = ? AND deleted_at IS NULL');
        $stmt3->bind_param('s', $sample);
        $stmt3->execute();
        return (bool) $stmt3->get_result()->fetch_assoc();
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
            'UPDATE family_members SET deleted_at = NOW(), national_id = NULL, dna_sample_number = NULL, phone = NULL WHERE family_id = ? AND deleted_at IS NULL'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return parent::softDelete($id);
    }
}
