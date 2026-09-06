<?php

require_once __DIR__ . '/AreaValidator.php';
require_once __DIR__ . '/../../Models/Area.php';

/**
 * Area サービス
 *
 * ビジネスロジックを担当する。バリデーションは AreaValidator に委譲し、
 * このクラスはDBアクセス（Areaモデル）との橋渡しに専念する。
 * コントローラーからはこのクラス経由でのみDBアクセスを行う想定。
 */
class AreaService
{
    /** @var Area */
    private $areaModel;

    /** @var AreaValidator */
    private $validator;

    public function __construct()
    {
        $this->areaModel = new Area();
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
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     * @throws AreaValidationException
     */
    public function create(array $input): array
    {
        $data = $this->validator->validate($input);
        $id = $this->areaModel->create($data);

        return $this->areaModel->find($id);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     * @throws AreaValidationException
     */
    public function update(int $id, array $input): array
    {
        if (!$this->areaModel->exists($id)) {
            throw new \RuntimeException('Area not found', 404);
        }

        $data = $this->validator->validate($input);
        $this->areaModel->update($id, $data);

        return $this->areaModel->find($id);
    }

    public function delete(int $id): void
    {
        if (!$this->areaModel->exists($id)) {
            throw new \RuntimeException('Area not found', 404);
        }

        $this->areaModel->delete($id);
    }
}
