<?php
// アイデア（ideasテーブル）に対するDB操作をまとめたクラス
// 「街タネ」UIでは、発案者が特定の地域ページからアイデア登録に進むため、
// area_id（areasテーブルへの外部キー）で正式に紐付ける。
// area名はareasテーブルとJOINして取得する（表示用）。

require_once __DIR__ . '/../Core/Database.php';

class Idea
{
    // 指定した地域のアイデア一覧を取得する（新着アイデア／過去のアイデア画面用）
    // $filters: ['status' => 'success'|'failure']（任意）
    public static function allForArea(int $areaId, array $filters = []): array
    {
        $conn = Database::getConnection();

        $conditions = ['i.area_id = $1'];
        $params = [$areaId];
        $index = 2;

        if (!empty($filters['status'])) {
            $conditions[] = 'i.status = $' . $index;
            $params[] = $filters['status'];
            $index++;
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT i.id, i.area_id, a.name AS area_name, i.title, i.status, i.content, i.reason, i.created_at
            FROM ideas i
            JOIN areas a ON a.id = i.area_id
            $where
            ORDER BY i.created_at DESC
        ";

        $result = pg_query_params($conn, $sql, $params);

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
            SELECT i.id, i.area_id, a.name AS area_name, i.title, i.status, i.content, i.reason,
                   i.created_at, i.updated_at
            FROM ideas i
            JOIN areas a ON a.id = i.area_id
            WHERE i.id = $1
        ";

        $result = pg_query_params($conn, $sql, [$id]);

        if ($result === false) {
            throw new RuntimeException('アイデア詳細の取得に失敗しました: ' . pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);

        return $row ?: null;
    }

    // アイデアを新規登録する（アイデア入力。特定の地域ページから遷移してくるため area_id は必須）
    public static function create(array $data): int
    {
        $conn = Database::getConnection();

        $sql = "
            INSERT INTO ideas (area_id, title, status, content, reason, user_id)
            VALUES ($1, $2, $3, $4, $5, $6)
            RETURNING id
        ";

        $params = [
            $data['area_id'],
            $data['title'],
            $data['status'],
            $data['content'],
            $data['reason'],
            $data['user_id'],
        ];

        $result = pg_query_params($conn, $sql, $params);

        if ($result === false) {
            throw new RuntimeException('アイデアの登録に失敗しました: ' . pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);

        return (int) $row['id'];
    }
}
