<?php

class DiaryDAO
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert($userId, $title, $content)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO diary_entries (user_id, title, content, entry_date, created_at)
            VALUES (?, ?, ?, CURDATE(), NOW())
        ");

        $stmt->execute([$userId, $title, $content]);
        return $this->pdo->lastInsertId();
    }

    public function getByUser($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT diary_id, title, content, entry_date, created_at
            FROM diary_entries
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");

        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}