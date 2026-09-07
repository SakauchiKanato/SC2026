<?php
// アイデア（ideasテーブル）に対するDB操作をまとめたクラス
// フィールド構成はfrontend側のAPI仕様（area_name / status / content / reason）に合わせている

require_once __DIR__ . '/../Core/Database.php';

class Idea
{
    // アイデア一覧を取得する（アイデア閲覧：一覧画面用）
    // $filters: ['area_name' => string, 'status' => 'success'|'failure']
    public static function all(array $filters = []): array
    {
        $conn = Database::getConnection();

        $conditions = [];
        $params = [];
        $index = 1;

        if (!empty($filters['area_name'])) {
            $conditions[] = 'area_name ILIKE $' . $index;
            $params[] = '%' . $filters['area_name'] . '%';
            $index++;
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'status = $' . $index;
            $params[] = $filters['status'];
            $index++;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT id, area_name, title, status, content, reason, created_at
            FROM ideas
            $where
            ORDER BY created_at DESC
        ";

        $result = $params
            ? pg_query_params($conn, $sql, $params)
            : pg_query($conn, $sql);

        if ($result === false) {
            throw new RuntimeException('アイデア一覧の取得に失敗しました: ' . pg_last_error($conn));
        }

        return pg_fetch_all($result) ?: [];
    }

    // アイデア詳細を取得する（アイデア閲覧：詳細画面用）
    public static function find(int $id): ?array
    {
        $conn = Database::getConnection();

        $sql = "
            SELECT id, area_name, title, status, content, reason, created_at, updated_at
            FROM ideas
            WHERE id = $1
        ";

        $result = pg_query_params($conn, $sql, [$id]);

        if ($result === false) {
            throw new RuntimeException('アイデア詳細の取得に失敗しました: ' . pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);

        return $row ?: null;
    }

    // アイデアを新規登録する（アイデア入力）
    public static function create(array $data): int
    {
        $conn = Database::getConnection();

        $sql = "
            INSERT INTO ideas (area_name, title, status, content, reason, user_id)
            VALUES ($1, $2, $3, $4, $5, $6)
            RETURNING id
        ";

        $params = [
            $data['area_name'],
            $data['title'],
            $data['status'],
            $data['content'],
            $data['reason'],
            $data['user_id'] ?? null, // TODO(SECURITY): ログイン機能実装後はセッションから取得したuser_idを渡す
        ];

        $result = pg_query_params($conn, $sql, $params);

        if ($result === false) {
            throw new RuntimeException('アイデアの登録に失敗しました: ' . pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);

        return (int) $row['id'];
    }
}
