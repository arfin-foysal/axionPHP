<?php

namespace App\Models;

class InMemoryUser
{
    private static array $users = [];
    private static int $nextId = 1;

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
        if (!isset($this->id)) {
            $this->id = self::$nextId++;
            self::$users[$this->id] = $this;
        } else {
            $this->updated_at = date('Y-m-d H:i:s');
            self::$users[$this->id] = $this;
        }

        return true;
    }

    public static function find(int $id): ?self
    {
        return self::$users[$id] ?? null;
    }

    public static function findByEmail(string $email): ?self
    {
        foreach (self::$users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }
        return null;
    }

    public static function all(): array
    {
        return array_values(self::$users);
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

    public function getJwtPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
