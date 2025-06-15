<?php

namespace App\Models;

class FileUser
{
    private static string $dataFile = __DIR__ . '/../../users.json';
    
    public int $id;
    public string $name;
    public string $email;
    public string $password;
    public string $created_at;
    public string $updated_at;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                if ($key === 'password') {
                    $this->setPasswordAttribute($value);
                } else {
                    $this->$key = $value;
                }
            }
        }
        
        if (!isset($this->created_at)) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        
        if (!isset($this->updated_at)) {
            $this->updated_at = date('Y-m-d H:i:s');
        }
    }

    public function save(): bool
    {
        $users = self::loadUsers();
        
        if (!isset($this->id)) {
            $this->id = self::getNextId($users);
        } else {
            $this->updated_at = date('Y-m-d H:i:s');
        }
        
        $users[$this->id] = $this->toStorageArray();
        return self::saveUsers($users);
    }

    public static function find(int $id): ?self
    {
        $users = self::loadUsers();
        if (isset($users[$id])) {
            return new self($users[$id]);
        }
        return null;
    }

    public static function findByEmail(string $email): ?self
    {
        $users = self::loadUsers();
        foreach ($users as $userData) {
            if ($userData['email'] === $email) {
                return new self($userData);
            }
        }
        return null;
    }

    public static function all(): array
    {
        $users = self::loadUsers();
        $result = [];
        foreach ($users as $userData) {
            $user = new self($userData);
            $result[] = $user->toArray();
        }
        return $result;
    }

    public function setPasswordAttribute(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function toStorageArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function getJwtPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    private static function loadUsers(): array
    {
        if (!file_exists(self::$dataFile)) {
            return [];
        }
        
        $content = file_get_contents(self::$dataFile);
        return json_decode($content, true) ?? [];
    }

    private static function saveUsers(array $users): bool
    {
        $content = json_encode($users, JSON_PRETTY_PRINT);
        return file_put_contents(self::$dataFile, $content) !== false;
    }

    private static function getNextId(array $users): int
    {
        if (empty($users)) {
            return 1;
        }
        return max(array_keys($users)) + 1;
    }
}
