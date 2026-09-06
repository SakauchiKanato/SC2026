<?php
// アイデア（ideasテーブル）に対するDB操作をまとめたクラス

require_once __DIR__ . '/../Core/Database.php';

class Idea
{
    // アイデア一覧を取得する（アイデア閲覧：一覧画面用）
    public static function all(): array
    {
        $conn = Database::getConnection();

        $sql = "
            SELECT
                ideas.id,
                ideas.title,
                ideas.summary,
                ideas.business_plan,
                ideas.result_status,
                ideas.result_note,
                ideas.created_at,
                areas.id   AS area_id,
                areas.name AS area_name
            FROM ideas
            JOIN areas ON areas.id = ideas.area_id
            ORDER BY ideas.created_at DESC
        ";

        $result = pg_query($conn, $sql);

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
            SELECT
                ideas.id,
                ideas.title,
                ideas.summary,
                ideas.business_plan,
                ideas.result_status,
                ideas.result_note,
                ideas.created_at,
                ideas.updated_at,
                areas.id   AS area_id,
                areas.name AS area_name,
                users.id   AS user_id,
                users.name AS user_name
            FROM ideas
            JOIN areas ON areas.id = ideas.area_id
            JOIN users ON users.id = ideas.user_id
            WHERE ideas.id = $1
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
            INSERT INTO ideas (area_id, user_id, title, summary, business_plan, result_status, result_note)
            VALUES ($1, $2, $3, $4, $5, $6, $7)
            RETURNING id
        ";

        $params = [
            $data['area_id'],
            $data['user_id'],
            $data['title'],
            $data['summary'],
            $data['business_plan'] ?? null,
            $data['result_status'] ?? 'ongoing',
            $data['result_note'] ?? null,
        ];

        $result = pg_query_params($conn, $sql, $params);

        if ($result === false) {
            throw new RuntimeException('アイデアの登録に失敗しました: ' . pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);

        return (int) $row['id'];
    }
}
