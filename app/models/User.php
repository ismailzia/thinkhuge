<?php
namespace App\models;

use App\core\BaseModel;
use PDO;

/**
 * User model manages users including their authentication info, API keys,
 * and relations with clients and transactions.
 */
class User extends BaseModel
{
    public static $table = 'users';
    protected static $fields = [
        'id',
        'first_name',
        'last_name',
        'email',
        'password',
        'is_active',
        'role',
        'created_at',
        'updated_at',
        'api_key'
    ];
    protected static $required = ['first_name', 'last_name', 'email', 'password'];
    protected static $protected = ['password', 'api_key'];
    protected static $softDelete = true;

    /**
     * Get clients related to this user.
     *
     * @return array
     */
    public function clients()
    {
        return Client::paginate(1, 1000, '', [], false, ['user_id' => $this->id]);
    }

    /**
     * Retrieve API key for this user from database.
     *
     * @return string|null
     */
    public function getApiKey()
    {
        $table = static::table();
        $sql = "SELECT api_key FROM `$table` WHERE id = ? LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['api_key'] : null;
    }

    /**
     * Generate a unique API key string.
     *
     * @param int $length Length of the key (default 64)
     * @return string
     */
    public static function generateUniqueApiKey($length = 64)
    {
        do {
            $key = bin2hex(random_bytes($length / 2));
            $exists = User::findByApiKey($key);
        } while ($exists);
        return $key;
    }

    /**
     * Find a user by API key.
     *
     * @param string $apiKey
     * @return static|null
     */
    public static function findByApiKey($apiKey)
    {
        $table = static::table();
        $select = User::getDefaultFieldsForSelect();
        $sql = "SELECT $select FROM `$table` WHERE api_key = ? LIMIT 1";
        $stmt = self::db()->prepare($sql);
        $stmt->execute([$apiKey]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new static($data) : null;
    }

    /**
     * Get transactions related to this user.
     *
     * @return array
     */
    public function transactions()
    {
        return Transaction::paginate(1, 1000, '', [], false, ['user_id' => $this->id]);
    }

    /**
     * Find user by email address.
     * Supports soft delete exclusion.
     *
     * @param string $email
     * @param bool $withTrashed Include soft deleted if true
     * @return static|null
     */
    public static function findByEmail($email, $withTrashed = false)
    {
        $table = static::table();
        $sql = "SELECT * FROM `$table` WHERE email = ?";
        $params = [$email];

        if (property_exists(static::class, 'softDelete') && static::$softDelete && !$withTrashed) {
            $sql .= " AND deleted_at IS NULL";
        }
        $sql .= " LIMIT 1";

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new static($data) : null;
    }

}
