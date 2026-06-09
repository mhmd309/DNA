<?php

declare(strict_types=1);

namespace App\Core;

class ActivityLogger
{
    public static function log(string $action, string $entityType, ?int $entityId = null, ?string $details = null): void
    {
        try {
            $db = Database::getInstance();
            $userId = Auth::id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $detailsText = $details ?? '';
            $entityIdValue = $entityId ?? 0;

            if ($userId === null) {
                $stmt = $db->prepare(
                    'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (NULL, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('ssiss', $action, $entityType, $entityIdValue, $detailsText, $ip);
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param('ississ', $userId, $action, $entityType, $entityIdValue, $detailsText, $ip);
            }
            $stmt->execute();
        } catch (\Throwable) {
            // لا توقف العملية الأساسية عند فشل التسجيل
        }
    }
}
