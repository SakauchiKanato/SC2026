<?php

/**
 * Area モデル
 *
 * 地域（Area）の特色・地理情報を扱うデータアクセスクラス。
 * Areaは所有者(user)を持たない独立したマスターデータであり、
 * 誰でも参照・登録・編集・削除が可能な想定。
 *
 * NOTE: backend/src/Core/Database.php はまだ存在しない（他メンバー実装予定）。
 *       Database::getConnection(): \PDO を提供するクラスである想定でこのモデルを実装している。
 *       Core/Database.php が実装され次第、動作確認をお願いします。
 */

require_once __DIR__ . '/../Core/Database.php';

class Area
{
    private const TABLE = 'areas';

    /** @var \PDO */
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * 全件取得（一覧表示用）
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT id, name, prefecture, city, latitude, longitude, features, created_at, updated_at
             FROM ' . self::TABLE . '
             ORDER BY created_at DESC'
        );

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * IDで1件取得
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, prefecture, city, latitude, longitude, features, created_at, updated_at
             FROM ' . self::TABLE . '
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * 新規登録
     *
     * @param array<string, mixed> $data バリデーション済みのデータ（AreaServiceで検証済みのものを渡すこと）
     * @return int 作成されたAreaのID
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . '
             (name, prefecture, city, latitude, longitude, features, created_at, updated_at)
             VALUES (:name, :prefecture, :city, :latitude, :longitude, :features, NOW(), NOW())'
        );

        $stmt->execute([
            'name' => $data['name'],
            'prefecture' => $data['prefecture'],
            'city' => $data['city'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'features' => $data['features'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * 更新
     *
     * @param array<string, mixed> $data バリデーション済みのデータ（AreaServiceで検証済みのものを渡すこと）
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . '
             SET name = :name,
                 prefecture = :prefecture,
                 city = :city,
                 latitude = :latitude,
                 longitude = :longitude,
                 features = :features,
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'prefecture' => $data['prefecture'],
            'city' => $data['city'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'features' => $data['features'],
        ]);
    }

    /**
     * 削除
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ' . self::TABLE . ' WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    /**
     * 存在確認（更新・削除前のチェック用）
     */
    public function exists(int $id): bool
    {
        return $this->find($id) !== null;
    }
}
