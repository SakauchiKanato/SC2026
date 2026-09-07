<?php
// アイデア（ideasテーブル）に対するDB操作をまとめたクラス
// area_id で areas テーブルと紐づく（登録済みの地域にしか紐づけられない）。
// 一覧・詳細では areas を JOIN して地域名（area_name）も一緒に返す
// （frontend側の表示・検索は従来どおり area_name ベースのため、互換性を保つ）。

require_once __DIR__ . '/../Core/Database.php';

class Idea
{
    // アイデア一覧を取得する（アイデア閲覧：一覧画面用）
    // $filters: ['area_name' => string, 'area_id' => int, 'status' => 'success'|'failure']
    public static function all(array $filters = []): array
    {
        $conn = Database::getConnection();

        $conditions = [];
        $params = [];
        $index = 1;

        if (!empty($filters['area_id'])) {
            $conditions[] = 'ideas.area_id = $' . $index;
            $params[] = (int) $filters['area_id'];
            $index++;
        }

        if (!empty($filters['area_name'])) {
            $conditions[] = 'areas.name ILIKE $' . $index;
            $params[] = '%' . $filters['area_name'] . '%';
            $index++;
        }

        if (!empty($filters['status'])) {
            $conditions[] = 'ideas.status = $' . $index;
            $params[] = $filters['status'];
            $index++;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT ideas.id, ideas.area_id, areas.name AS area_name, ideas.title,
                   ideas.status, ideas.content, ideas.reason, ideas.created_at
            FROM ideas
            LEFT JOIN areas ON areas.id = ideas.area_id
            $where
            ORDER BY ideas.created_at DESC
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
            SELECT ideas.id, ideas.area_id, areas.name AS area_name, ideas.title,
                   ideas.status, ideas.content, ideas.reason, ideas.created_at, ideas.updated_at
            FROM ideas
            LEFT JOIN areas ON areas.id = ideas.area_id
            WHERE ideas.id = $1
        ";

        $result = pg_query_params($conn, $sql, [$id]);

        if ($result === false) {
            throw new RuntimeException('アイデア詳細の取得に失敗しました: ' . pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);

        return $row ?: null;
    }

    // area_id が実在するareaを指しているか確認する
    // （IdeaController::validate()から呼ぶ。DB外部キー制約に任せて500にするのではなく、
    //   事前にチェックして422の分かりやすいエラーを返すため）
    public static function areaExists(int $areaId): bool
    {
        $conn = Database::getConnection();

        $result = pg_query_params($conn, 'SELECT 1 FROM areas WHERE id = $1', [$areaId]);

        if ($result === false) {
            throw new RuntimeException('地域の確認に失敗しました: ' . pg_last_error($conn));
        }

        return pg_fetch_assoc($result) !== false;
    }

    // アイデアを新規登録する（アイデア入力）
    // user_id・area_id はどちらもコントローラー側で検証済みの値を受け取る
    // （user_idはクライアント入力を信用せずAuthMiddleware::requireUserId()から取得したもの、
    //  area_idはareaExists()で実在確認済みのもの）。
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
