<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
  protected string $table = 'users';

  public function findByEmail(string $email): ?array
  {
    $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
  }

  public function getAll(int $page, int $perPage, string $search = ''): array
  {
    $where = 'deleted_at IS NULL';
    $params = [];
    $types = '';

    if ($search !== '') {
      $where .= ' AND (name LIKE ? OR email LIKE ?)';
      $like = "%{$search}%";
      $params = [$like, $like];
      $types = 'ss';
    }

    $sql = "SELECT id, name, email, role, is_active, created_at FROM users WHERE {$where} ORDER BY created_at DESC";
    $countSql = "SELECT COUNT(*) as total FROM users WHERE {$where}";

    return $this->paginate($sql, $countSql, $params, $types, $page, $perPage);
  }

  public function create(array $data): int
  {
    $stmt = $this->db->prepare(
      'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)'
    );
    $stmt->bind_param('ssss', $data['name'], $data['email'], $data['password'], $data['role']);
    $stmt->execute();
    return $this->db->lastInsertId();
  }

  public function update(int $id, array $data): bool
  {
    if (!empty($data['password'])) {
      $stmt = $this->db->prepare(
        'UPDATE users SET name = ?, email = ?, password = ?, role = ?, is_active = ? WHERE id = ?'
      );
      $stmt->bind_param('ssssii', $data['name'], $data['email'], $data['password'], $data['role'], $data['is_active'], $id);
    } else {
      $stmt = $this->db->prepare(
        'UPDATE users SET name = ?, email = ?, role = ?, is_active = ? WHERE id = ?'
      );
      $stmt->bind_param('sssii', $data['name'], $data['email'], $data['role'], $data['is_active'], $id);
    }
    return $stmt->execute();
  }

  public function emailExists(string $email, ?int $excludeId = null): bool
  {
    $sql = 'SELECT id FROM users WHERE email = ? AND deleted_at IS NULL';
    if ($excludeId) {
      $sql .= ' AND id != ?';
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('si', $email, $excludeId);
    } else {
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('s', $email);
    }
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_assoc();
  }

  public function search(string $query, int $limit = 5): array
  {
    $like = "%{$query}%";
    $stmt = $this->db->prepare(
      'SELECT id, name, email, role FROM users WHERE deleted_at IS NULL AND (name LIKE ? OR email LIKE ?) LIMIT ?'
    );
    $stmt->bind_param('ssi', $like, $like, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function countActive(): int
  {
    return $this->count('deleted_at IS NULL');
  }

  public function getAllForReport(): array
  {
    $stmt = $this->db->prepare(
      'SELECT id, name, email, role, is_active, created_at
       FROM users
       WHERE deleted_at IS NULL
       ORDER BY created_at DESC'
    );
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }
}
