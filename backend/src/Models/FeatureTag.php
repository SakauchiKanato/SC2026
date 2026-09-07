<?php

/**
 * FeatureTag モデル
 *
 * Areaの「特色タグ」マスターデータを扱うデータアクセスクラス。
 * タグは一度登録されると全Areaで共有され、以後は誰でもプルダウンから
 * 選択・再利用できるようにする想定。
 *
 * NOTE: backend/src/Core/Database.php はまだ存在しない（他メンバー実装予定）。
 *       PDOを直接コンストラクタで受け取った場合はCore/Database.phpを読み込まないため、
 *       Core/Database.php完成前でもテストからは動かせる。
 */

class FeatureTag
{
    private const TABLE = 'feature_tags';

    /** @var \PDO */
    private $db;

    public function __construct(?\PDO $connection = null)
    {
        if ($connection !== null) {
            $this->db = $connection;
            return;
        }

        require_once __DIR__ . '/../Core/Database.php';
        $this->db = Database::getConnection();
    }

    /**
     * 全タグ取得（プルダウンの選択肢用）
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT id, name FROM ' . self::TABLE . ' ORDER BY name ASC'
        );

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 名前でタグを検索する
     */
    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name FROM ' . self::TABLE . ' WHERE name = :name'
        );
        $stmt->execute(['name' => $name]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * 新規タグを作成する
     */
    public function create(string $name): int
    {
        // NOTE: PostgreSQLではPDO::lastInsertId()がMySQLほど素直に使えないため、
        //       INSERT文にRETURNING idを付けて直接IDを取得している
        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (name, created_at) VALUES (:name, NOW()) RETURNING id'
        );
        $stmt->execute(['name' => $name]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * 既存タグがあればそのIDを返し、なければ新規作成してIDを返す
     */
    public function findOrCreateByName(string $name): int
    {
        $existing = $this->findByName($name);

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        return $this->create($name);
    }
}
