<?php

require_once __DIR__ . '/AreaValidator.php';
require_once __DIR__ . '/../../Models/Area.php';
require_once __DIR__ . '/../../Models/FeatureTag.php';

/**
 * Area サービス
 *
 * ビジネスロジックを担当する。バリデーションは AreaValidator に委譲する。
 * Areaはuser_idで登録者に紐づくため、更新・削除は登録者本人のみに制限する。
 *
 * 特色タグの登録・紐づけはareasテーブルとfeature_tags/area_feature_tagsテーブルの
 * 複数テーブルにまたがる更新になるため、トランザクションでまとめて整合性を保つ
 * （AGENTS.md 5章: データ整合性が必要な複数の更新処理はトランザクションでまとめる）。
 *
 * PDOを直接コンストラクタで受け取った場合はCore/Database.phpを読み込まないため、
 * Core/Database.php完成前でもテストからは動かせる。
 */
class AreaService
{
    /** @var \PDO */
    private $db;

    /** @var Area */
    private $areaModel;

    /** @var FeatureTag */
    private $featureTagModel;

    /** @var AreaValidator */
    private $validator;

    public function __construct(?\PDO $connection = null)
    {
        if ($connection !== null) {
            $this->db = $connection;
        } else {
            require_once __DIR__ . '/../../Core/Database.php';
            $this->db = Database::getPdoConnection();
        }

        // 同一のPDOインスタンスをArea/FeatureTagモデル双方に渡すことで、
        // トランザクションが両テーブルへの操作に確実に及ぶようにしている
        $this->areaModel = new Area($this->db);
        $this->featureTagModel = new FeatureTag($this->db);
        $this->validator = new AreaValidator();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        return $this->areaModel->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getById(int $id): array
    {
        $area = $this->areaModel->find($id);

        if ($area === null) {
            throw new \RuntimeException('Area not found', 404);
        }

        return $area;
    }

    /**
     * 特色タグの選択肢一覧（プルダウン用）
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllTags(): array
    {
        return $this->featureTagModel->all();
    }

    /**
     * @param int $userId 登録者（認証済みユーザー）のID
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     * @throws AreaValidationException
     */
    public function create(int $userId, array $input): array
    {
        $data = $this->validator->validate($input);
        $data['user_id'] = $userId;

        $this->db->beginTransaction();
        try {
            $id = $this->areaModel->create($data);
            $tagIds = $this->resolveTagIds($data['tags']);
            $this->areaModel->attachTags($id, $tagIds);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->areaModel->find($id);
    }

    /**
     * @param int $userId 操作しようとしている認証済みユーザーのID
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     * @throws AreaValidationException
     */
    public function update(int $id, int $userId, array $input): array
    {
        $this->assertOwnedByUser($id, $userId);

        $data = $this->validator->validate($input);

        $this->db->beginTransaction();
        try {
            $this->areaModel->update($id, $data);
            $tagIds = $this->resolveTagIds($data['tags']);
            $this->areaModel->attachTags($id, $tagIds);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->areaModel->find($id);
    }

    public function delete(int $id, int $userId): void
    {
        $this->assertOwnedByUser($id, $userId);
        $this->areaModel->delete($id);
    }

    /**
     * 指定したAreaが存在し、かつuserIdが登録者本人であることを確認する
     * 存在しなければ404、他人のAreaであれば403相当の例外を投げる
     */
    private function assertOwnedByUser(int $areaId, int $userId): void
    {
        $area = $this->areaModel->find($areaId);

        if ($area === null) {
            throw new \RuntimeException('Area not found', 404);
        }

        if ((int) $area['user_id'] !== $userId) {
            throw new \RuntimeException('この地域を編集・削除する権限がありません', 403);
        }
    }

    /**
     * タグ名の配列から、既存タグは再利用し新規タグは作成してIDの配列を返す
     *
     * @param array<string> $tagNames
     * @return array<int>
     */
    private function resolveTagIds(array $tagNames): array
    {
        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $tagIds[] = $this->featureTagModel->findOrCreateByName($tagName);
        }

        return $tagIds;
    }
}
