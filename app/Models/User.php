<?php

namespace App\Models;

class User extends BaseModel
{
    protected $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1",
            [strtolower(trim($email))]
        );
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findByOAuth(string $provider, string $oauth_id): ?array
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE oauth_provider = ? AND oauth_id = ? LIMIT 1",
            [$provider, $oauth_id]
        );
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $data['email'] = strtolower(trim($data['email']));
        if (!empty($data['name'])) {
            $data['name'] = trim($data['name']);
        }
        return $this->insert($data);
    }

    public function upsertOAuth(string $provider, array $profile): int
    {
        $existing = $this->findByOAuth($provider, $profile['id']);

        if ($existing) {
            $this->update($existing['id'], [
                'name' => $profile['name'],
                'avatar' => $profile['picture'] ?? null,
            ]);
            return $existing['id'];
        }

        return $this->insert([
            'name' => $profile['name'],
            'email' => strtolower($profile['email']),
            'oauth_provider' => $provider,
            'oauth_id' => $profile['id'],
            'avatar' => $profile['picture'] ?? null,
        ]);
    }

    public function getAllUsers(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->query(
            "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
