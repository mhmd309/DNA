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
      $where .= ' AND (d.person_name LIKE ? OR d.lab_name LIKE ?)';
      $like = "%{$search}%";
      $params = [$like, $like];
      $types = 'ss';
    }

    $sql = "SELECT d.*
          FROM dna_tests d
          WHERE {$where}
          ORDER BY d.created_at DESC";

    $countSql = "SELECT COUNT(*) as total FROM dna_tests d WHERE {$where}";

    return $this->paginate($sql, $countSql, $params, $types, $page, $perPage);
  }

  public function findWithDetails(int $id): ?array
  {
    $stmt = $this->db->prepare(
      'SELECT d.* FROM dna_tests d
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
    $sampleDate = $data['sample_date'] ?: null;
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
      'INSERT INTO dna_tests (person_name, sample_date, lab_name, lab_location, doctor_name, status, result_summary, D3S1358_1, D3S1358_2, vWA_1, vWA_2, FGA_1, FGA_2, D8S1179_1, D8S1179_2, D21S11_1, D21S11_2, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
      'sssssssssssssssssi',
      $data['person_name'],
      $sampleDate,
      $data['lab_name'],
      $data['lab_location'],
      $data['doctor_name'],
      $data['status'],
      $data['result_summary'],
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
    $sampleDate = $data['sample_date'] ?: null;
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
      'UPDATE dna_tests SET person_name = ?, sample_date = ?, lab_name = ?, lab_location = ?, doctor_name = ?, status = ?, result_summary = ?, D3S1358_1 = ?, D3S1358_2 = ?, vWA_1 = ?, vWA_2 = ?, FGA_1 = ?, FGA_2 = ?, D8S1179_1 = ?, D8S1179_2 = ?, D21S11_1 = ?, D21S11_2 = ? WHERE id = ?'
    );
    $stmt->bind_param(
      'sssssssssssssssssi',
      $data['person_name'],
      $sampleDate,
      $data['lab_name'],
      $data['lab_location'],
      $data['doctor_name'],
      $data['status'],
      $data['result_summary'],
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

  public function search(string $query, int $limit = 5): array
  {
    $like = "%{$query}%";
    $stmt = $this->db->prepare(
      'SELECT id, person_name, status FROM dna_tests WHERE deleted_at IS NULL AND (person_name LIKE ?) LIMIT ?'
    );
    $stmt->bind_param('si', $like, $limit);
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
      'UPDATE dna_tests SET deleted_at = NOW() WHERE id = ?'
    );
    $stmt->bind_param('i', $id);
    return $stmt->execute();
  }

  public function getAllForReport(): array
  {
    $stmt = $this->db->prepare("
      SELECT d.id, d.person_name, d.sample_date, d.lab_name,
        d.lab_location, d.doctor_name, d.status, d.created_at, u.name as created_by_name,
        d.D3S1358_1, d.D3S1358_2, d.vWA_1, d.vWA_2, d.FGA_1, d.FGA_2, 
        d.D8S1179_1, d.D8S1179_2, d.D21S11_1, d.D21S11_2
      FROM dna_tests d
      LEFT JOIN users u ON u.id = d.created_by
      WHERE d.deleted_at IS NULL
      ORDER BY d.created_at DESC
    ");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getAllWithDna(): array
  {
    $stmt = $this->db->prepare("
      SELECT d.*, u.name as created_by_name
      FROM dna_tests d
      LEFT JOIN users u ON u.id = d.created_by
      WHERE d.deleted_at IS NULL 
        AND d.D3S1358_1 IS NOT NULL 
        AND d.D3S1358_2 IS NOT NULL
      ORDER BY d.created_at DESC
    ");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }
}
