<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class DnaTest extends Model
{
    protected string $table = 'dna_tests';

    public function getAll(int $page, int $perPage, string $search = ''): array
    {
        $where = 'd.deleted_at IS NULL';
        $params = [];
        $types = '';

        if ($search !== '') {
            $where .= ' AND (d.person_name LIKE ? OR d.sample_number LIKE ? OR d.lab_name LIKE ?)';
            $like = "%{$search}%";
            $params = [$like, $like, $like];
            $types = 'sss';
        }

        $sql = "SELECT d.*, f.family_name, f.family_code
                FROM dna_tests d
                LEFT JOIN families f ON f.id = d.family_id
                WHERE {$where}
                ORDER BY d.created_at DESC";

        $countSql = "SELECT COUNT(*) as total FROM dna_tests d WHERE {$where}";

        return $this->paginate($sql, $countSql, $params, $types, $page, $perPage);
    }

    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT d.*, f.family_name, f.family_code FROM dna_tests d
             LEFT JOIN families f ON f.id = d.family_id
             WHERE d.id = ? AND d.deleted_at IS NULL LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $test = $stmt->get_result()->fetch_assoc();
        if (!$test) return null;

        $stmt2 = $this->db->prepare('SELECT * FROM dna_test_attachments WHERE dna_test_id = ?');
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $test['attachments'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

        return $test;
    }

    public function create(array $data, int $userId): int
    {
        $familyId = !empty($data['family_id']) ? (int) $data['family_id'] : null;
        $sampleDate = $data['sample_date'] ?: null;

        $stmt = $this->db->prepare(
            'INSERT INTO dna_tests (person_name, family_id, sample_number, sample_date, lab_name, lab_location, doctor_name, status, result_summary, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'sisssssssi',
            $data['person_name'], $familyId, $data['sample_number'], $sampleDate,
            $data['lab_name'], $data['lab_location'], $data['doctor_name'],
            $data['status'], $data['result_summary'], $userId
        );
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $familyId = !empty($data['family_id']) ? (int) $data['family_id'] : null;
        $sampleDate = $data['sample_date'] ?: null;

        $stmt = $this->db->prepare(
            'UPDATE dna_tests SET person_name = ?, family_id = ?, sample_number = ?, sample_date = ?, lab_name = ?, lab_location = ?, doctor_name = ?, status = ?, result_summary = ? WHERE id = ?'
        );
        $stmt->bind_param(
            'sisssssssi',
            $data['person_name'], $familyId, $data['sample_number'], $sampleDate,
            $data['lab_name'], $data['lab_location'], $data['doctor_name'],
            $data['status'], $data['result_summary'], $id
        );
        return $stmt->execute();
    }

    public function addAttachment(int $testId, string $fileName, string $filePath, string $fileType, int $fileSize): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO dna_test_attachments (dna_test_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('isssi', $testId, $fileName, $filePath, $fileType, $fileSize);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    public function deleteAttachment(int $attachmentId): ?string
    {
        $stmt = $this->db->prepare('SELECT file_path FROM dna_test_attachments WHERE id = ?');
        $stmt->bind_param('i', $attachmentId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return null;

        $del = $this->db->prepare('DELETE FROM dna_test_attachments WHERE id = ?');
        $del->bind_param('i', $attachmentId);
        $del->execute();
        return $row['file_path'];
    }

    public function sampleExists(string $sample, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM dna_tests WHERE sample_number = ? AND deleted_at IS NULL';
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

        $stmt3 = $this->db->prepare('SELECT id FROM individuals WHERE dna_sample_number = ? AND deleted_at IS NULL');
        $stmt3->bind_param('s', $sample);
        $stmt3->execute();
        return (bool) $stmt3->get_result()->fetch_assoc();
    }

    public function search(string $query, int $limit = 5): array
    {
        $like = "%{$query}%";
        $stmt = $this->db->prepare(
            'SELECT id, person_name, sample_number, status FROM dna_tests WHERE deleted_at IS NULL AND (person_name LIKE ? OR sample_number LIKE ?) LIMIT ?'
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
            'UPDATE dna_tests SET deleted_at = NOW(), sample_number = CONCAT(sample_number, "_del_", id) WHERE id = ?'
        );
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
