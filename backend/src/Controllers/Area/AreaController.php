<?php

/**
 * Area コントローラー
 *
 * NOTE: ルーティング（public/index.php）はまだ存在しない（他メンバー実装予定）。
 *       このコントローラーは以下の呼び出し規約を想定して実装している：
 *
 *   GET    /api/areas       -> index()
 *   GET    /api/areas/{id}  -> show($id)
 *   POST   /api/areas       -> store()
 *   PUT    /api/areas/{id}  -> update($id)
 *   DELETE /api/areas/{id}  -> destroy($id)
 *
 * 各メソッドはレスポンスをJSONで出力し、適切なHTTPステータスコードを設定する。
 * ルーター実装時にこの規約と合わない場合は調整をお願いします。
 */

require_once __DIR__ . '/../../Services/Area/AreaService.php';

class AreaController
{
    /** @var AreaService */
    private $areaService;

    public function __construct()
    {
        $this->areaService = new AreaService();
    }

    public function index(): void
    {
        $areas = $this->areaService->getAll();
        $this->respond(200, $areas);
    }

    public function show(int $id): void
    {
        try {
            $area = $this->areaService->getById($id);
            $this->respond(200, $area);
        } catch (\RuntimeException $e) {
            $this->respond($this->resolveErrorStatus($e), ['error' => $e->getMessage()]);
        }
    }

    public function store(): void
    {
        $input = $this->readJsonBody();

        try {
            $area = $this->areaService->create($input);
            $this->respond(201, $area);
        } catch (AreaValidationException $e) {
            $this->respond(422, ['errors' => $e->getErrors()]);
        }
    }

    public function update(int $id): void
    {
        $input = $this->readJsonBody();

        try {
            $area = $this->areaService->update($id, $input);
            $this->respond(200, $area);
        } catch (AreaValidationException $e) {
            $this->respond(422, ['errors' => $e->getErrors()]);
        } catch (\RuntimeException $e) {
            $this->respond($this->resolveErrorStatus($e), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): void
    {
        try {
            $this->areaService->delete($id);
            http_response_code(204);
        } catch (\RuntimeException $e) {
            $this->respond($this->resolveErrorStatus($e), ['error' => $e->getMessage()]);
        }
    }

    /**
     * リクエストボディのJSONを配列として取得する
     * クライアントからの入力はここでも信用せず、必ずAreaService側でバリデーションする
     *
     * @return array<string, mixed>
     */
    private function readJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $decoded = json_decode((string)$raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function resolveErrorStatus(\RuntimeException $e): int
    {
        $code = (int) $e->getCode();

        return $code >= 400 && $code < 600 ? $code : 404;
    }

    /**
     * @param array<mixed>|array<string, mixed> $data
     */
    private function respond(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
