<?php

/**
 * AreaChallengeRequest モデル
 *
 * 「企業・自治体が実現したいこと」（課題点・問題点／期待する未来）の投稿。
 * 1つの地域(area)に対して、地域を登録していない別の企業・自治体も含め、
 * 誰でも・何件でも投稿できる掲示板形式（area_challenge_requestsテーブル）。
 *
 * Areaモデルと同じくPDOを使う（Areaと同じトランザクションに参加させるため、
 * AreaServiceからは同一のPDOインスタンスを渡して使う想定）。
 */
class AreaChallengeRequest
{
    private const TABLE = 'area_challenge_requests';

    /** @var \PDO */
    private $db;

    public function __construct(?\PDO $connection = null)
    {
        if ($connection !== null) {
            $this->db = $connection;
            return;
        }

        require_once __DIR__ . '/../Core/Database.php';
        $this->db = Database::getPdoConnection();
    }

    /**
     * 指定した地域への投稿一覧を取得する（投稿者名を含む。投稿順＝古い順）
     *
     * @return array<int, array<string, mixed>>
     */
    public function allForArea(int $areaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.id, r.area_id, r.user_id, u.name AS user_name,
                    r.challenges, r.expected_future, r.created_at, r.updated_at
             FROM ' . self::TABLE . ' r
             JOIN sc2026_users u ON u.id = r.user_id
             WHERE r.area_id = :area_id
             ORDER BY r.created_at ASC'
        );
        $stmt->execute(['area_id' => $areaId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * IDで1件取得する（投稿者本人かどうかの認可チェック用）
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.id, r.area_id, r.user_id, u.name AS user_name,
                    r.challenges, r.expected_future, r.created_at, r.updated_at
             FROM ' . self::TABLE . ' r
             JOIN sc2026_users u ON u.id = r.user_id
             WHERE r.id = :id'
        );
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * 新規投稿を作成する
     *
     * @param array<string, mixed> $data area_id, user_id, challenges, expected_future
     * @return int 作成された投稿のID
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (area_id, user_id, challenges, expected_future, created_at, updated_at)
             VALUES (:area_id, :user_id, :challenges, :expected_future, NOW(), NOW())
             RETURNING id'
        );

        $stmt->execute([
            'area_id' => $data['area_id'],
            'user_id' => $data['user_id'],
            'challenges' => $data['challenges'],
            'expected_future' => $data['expected_future'],
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * 投稿を更新する（投稿者本人かどうかのチェックは呼び出し側で行う）
     *
     * @param array<string, mixed> $data challenges, expected_future
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . '
             SET challenges = :challenges, expected_future = :expected_future
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'challenges' => $data['challenges'],
            'expected_future' => $data['expected_future'],
        ]);
    }

    /**
     * 投稿を削除する（投稿者本人かどうかのチェックは呼び出し側で行う）
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }
}
