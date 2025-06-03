<?php

namespace App\models;

use App\core\BaseModel;

/**
 * SessionModel manages user sessions stored in the database.
 */
class SessionModel extends BaseModel
{
    protected static $table = 'sessions';
    protected static $fields = ['user_id', 'session_id', 'ip_address', 'user_agent'];
    protected static $required = ['user_id', 'session_id'];

    /**
     * Checks if a session is valid for a given user and session ID.
     *
     * @param int    $userId    User ID to validate
     * @param string $sessionId Session ID to validate
     * @return bool True if session exists and is valid, false otherwise
     */
    public static function isValid($userId, $sessionId)
    {
        $table = static::table();
        $sql = "SELECT COUNT(*) FROM `$table` WHERE user_id = ? AND session_id = ?";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$userId, $sessionId]);
        return $stmt->fetchColumn() > 0;
    }
}
