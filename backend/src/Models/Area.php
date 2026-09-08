<?php

/**
 * Area モデル
 *
 * 地域（Area）の特色・地理情報を扱うデータアクセスクラス。
 * areasテーブル本体は初期スキーマ（他メンバー実装）で作成済みのため、
 * ここでは既存のカラム構成（user_id, name, features, latitude, longitude,
 * address, created_at, updated_at）に合わせて実装している。
 *
 * Areaはuser_idで登録者に紐づく（所有者あり）。特色タグ(feature_tags)との
 * 紐づけ(area_feature_tags)は、featuresカラムに上乗せする追加機能として
 * このクラスで扱う。
 *
 * NOTE: backend/src/Core/Database.php はまだ存在しない（他メンバー実装予定）。
 *       PDOを直接コンストラクタで受け取った場合はCore/Database.phpを読み込まないため、
 *       Core/Database.php完成前でもテストからは動かせる。
 *
 * NOTE: updated_atはDBトリガー(set_updated_at_areas)で自動更新されるため、
 *       このクラスのUPDATE文では明示的に更新していない。
 */
class Area
{
    private const TABLE = 'areas';
    private const PIVOT_TABLE = 'area_feature_tags';

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
     * 全件取得（一覧表示用）。各Areaに紐づくタグも含める
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT id, user_id, name, features, latitude, longitude, address,
                    population, day_night_population_ratio, average_age,
                    main_industry, transit_access,
                    challenges, expected_future, created_at, updated_at,
                    (SELECT COUNT(*) FROM ideas i WHERE i.area_id = ' . self::TABLE . '.id) AS ideas_count
             FROM ' . self::TABLE . '
             ORDER BY created_at DESC'
        );
        $areas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($areas)) {
            return [];
        }

        // N+1問題を避けるため、タグはループ内で1件ずつ取得せずまとめて取得する
        $areaIds = array_column($areas, 'id');
        $tagsByAreaId = $this->getTagsForAreaIds($areaIds);

        foreach ($areas as &$area) {
            $area['tags'] = $tagsByAreaId[$area['id']] ?? [];
        }
        unset($area);

        return $areas;
    }

    /**
     * IDで1件取得。タグも含める
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_id, name, features, latitude, longitude, address,
                    population, day_night_population_ratio, average_age,
                    main_industry, transit_access,
                    challenges, expected_future, created_at, updated_at,
                    (SELECT COUNT(*) FROM ideas i WHERE i.area_id = ' . self::TABLE . '.id) AS ideas_count
             FROM ' . self::TABLE . '
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        $area = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($area === false) {
            return null;
        }

        $tagsByAreaId = $this->getTagsForAreaIds([$id]);
        $area['tags'] = $tagsByAreaId[$id] ?? [];

        return $area;
    }

    /**
     * 新規登録（タグの紐づけは含まない。呼び出し側でattachTagsを呼ぶこと）
     *
     * @param array<string, mixed> $data バリデーション済みのデータ（user_idを含む）
     * @return int 作成されたAreaのID
     */
    public function create(array $data): int
    {
        // NOTE: PostgreSQLではPDO::lastInsertId()がMySQLほど素直に使えないため、
        //       INSERT文にRETURNING idを付けて直接IDを取得している
        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . '
             (user_id, name, features, latitude, longitude, address,
              population, day_night_population_ratio, average_age, main_industry, transit_access,
              challenges, expected_future, created_at, updated_at)
             VALUES (:user_id, :name, :features, :latitude, :longitude, :address,
                     :population, :day_night_population_ratio, :average_age, :main_industry, :transit_access,
                     :challenges, :expected_future, NOW(), NOW())
             RETURNING id'
        );

        $stmt->execute([
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'features' => $data['features'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'address' => $data['address'] ?? null,
            'population' => $data['population'] ?? null,
            'day_night_population_ratio' => $data['day_night_population_ratio'] ?? null,
            'average_age' => $data['average_age'] ?? null,
            'main_industry' => $data['main_industry'] ?? null,
            'transit_access' => $data['transit_access'] ?? null,
            'challenges' => $data['challenges'] ?? null,
            'expected_future' => $data['expected_future'] ?? null,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * 更新（タグの紐づけは含まない。呼び出し側でattachTagsを呼ぶこと）
     * user_id（所有者）は更新しない
     *
     * @param array<string, mixed> $data バリデーション済みのデータ
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ' . self::TABLE . '
             SET name = :name,
                 features = :features,
                 latitude = :latitude,
                 longitude = :longitude,
                 address = :address,
                 population = :population,
                 day_night_population_ratio = :day_night_population_ratio,
                 average_age = :average_age,
                 main_industry = :main_industry,
                 transit_access = :transit_access,
                 challenges = :challenges,
                 expected_future = :expected_future
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'features' => $data['features'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'address' => $data['address'] ?? null,
            'population' => $data['population'] ?? null,
            'day_night_population_ratio' => $data['day_night_population_ratio'] ?? null,
            'average_age' => $data['average_age'] ?? null,
            'main_industry' => $data['main_industry'] ?? null,
            'transit_access' => $data['transit_access'] ?? null,
            'challenges' => $data['challenges'] ?? null,
            'expected_future' => $data['expected_future'] ?? null,
        ]);
    }

    /**
     * 削除（area_feature_tagsの紐づけはFKのON DELETE CASCADEで自動削除される）
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
        $stmt = $this->db->prepare('SELECT 1 FROM ' . self::TABLE . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() !== false;
    }

    /**
     * Areaに紐づくタグを置き換える（既存の紐づけを全削除してから新しいものを挿入）
     *
     * @param array<int> $tagIds
     */
    public function attachTags(int $areaId, array $tagIds): void
    {
        $deleteStmt = $this->db->prepare(
            'DELETE FROM ' . self::PIVOT_TABLE . ' WHERE area_id = :area_id'
        );
        $deleteStmt->execute(['area_id' => $areaId]);

        if (empty($tagIds)) {
            return;
        }

        $insertStmt = $this->db->prepare(
            'INSERT INTO ' . self::PIVOT_TABLE . ' (area_id, feature_tag_id) VALUES (:area_id, :tag_id)'
        );

        foreach (array_unique($tagIds) as $tagId) {
            $insertStmt->execute(['area_id' => $areaId, 'tag_id' => $tagId]);
        }
    }

    /**
     * 複数AreaのタグをまとめてN+1にならずに取得する
     *
     * @param array<int> $areaIds
     * @return array<int, array<int, array<string, mixed>>> area_id => タグ配列
     */
    private function getTagsForAreaIds(array $areaIds): array
    {
        if (empty($areaIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($areaIds), '?'));
        $stmt = $this->db->prepare(
            'SELECT aft.area_id, ft.id, ft.name
             FROM ' . self::PIVOT_TABLE . ' aft
             JOIN feature_tags ft ON ft.id = aft.feature_tag_id
             WHERE aft.area_id IN (' . $placeholders . ')
             ORDER BY ft.name ASC'
        );
        $stmt->execute(array_values($areaIds));

        $result = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[(int) $row['area_id']][] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
            ];
        }

        return $result;
    }
}
