<?php

/**
 * Area コントローラー
 *
 *   GET    /api/areas       -> index()
 *   GET    /api/areas/{id}  -> show($id)
 *   POST   /api/areas       -> store()
 *   PUT    /api/areas/{id}  -> update($id)
 *   DELETE /api/areas/{id}  -> destroy($id)
 *
 * 各メソッドはレスポンスをJSONで出力し、適切なHTTPステータスコードを設定する。
 *
 * 認証: ログイン中のユーザーIDはAuthMiddleware::requireUserId()経由で
 *       Authorizationヘッダー（JWT）から取得する（Auth機能はJWT方式のため）。
 *       クライアントから送られてきたuser_idは信用せず、必ずトークンから
 *       取得したIDを使う（Zero Trust, AGENTS.md 4章）。
 *       登録・更新・削除はログイン必須。トークンが無い/不正な場合は
 *       AuthMiddleware側で401を返して処理を終了する。
 */

require_once __DIR__ . '/../../Services/Area/AreaService.php';
require_once __DIR__ . '/../../Core/AuthMiddleware.php';

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

    // GET /feature-tags … 特色タグの選択肢一覧（AreaFormViewのプルダウン用）
    // NOTE: AreaService::getAllTags()は既にあったが、呼び出す口が無かったため追加。
    public function tags(): void
    {
        $tags = $this->areaService->getAllTags();
        $this->respond(200, $tags);
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
        // トークンが無い/不正な場合はAuthMiddleware内で401を返してexitする
        $userId = AuthMiddleware::requireUserId();

        $input = $this->readJsonBody();

        try {
            $area = $this->areaService->create($userId, $input);
            $this->respond(201, $area);
        } catch (AreaValidationException $e) {
            $this->respond(422, ['errors' => $e->getErrors()]);
        }
    }

    public function update(int $id): void
    {
        $userId = AuthMiddleware::requireUserId();

        $input = $this->readJsonBody();

        try {
            $area = $this->areaService->update($id, $userId, $input);
            $this->respond(200, $area);
        } catch (AreaValidationException $e) {
            $this->respond(422, ['errors' => $e->getErrors()]);
        } catch (\RuntimeException $e) {
            $this->respond($this->resolveErrorStatus($e), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): void
    {
        $userId = AuthMiddleware::requireUserId();

        try {
            $this->areaService->delete($id, $userId);
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
