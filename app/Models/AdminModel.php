<?php

require_once __DIR__ . '/BaseModel.php';

class AdminModel extends BaseModel
{
    protected string $table = 'admins';

    /**
     * Find an admin record by username.
     * Returns the row array or false if not found.
     */
    public function findByUsername(string $username): array|false
    {
        return $this->queryOne(
            "SELECT id, username, password FROM {$this->table} WHERE username = :username LIMIT 1",
            [':username' => $username]
        );
    }
}
