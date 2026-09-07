<?php

/**
 * Area コントローラー
 *
 * ルーティング（public/index.php）:
 *   GET    /api/areas       -> index()
 *   GET    /api/areas/{id}  -> show($id)
 *   POST   /api/areas       -> store()
 *   PUT    /api/areas/{id}  -> update($id)
 *   DELETE /api/areas/{id}  -> destroy($id)
 *
 * 各メソッドはレスポンスをJSONで出力し、適切なHTTPステータスコードを設定する。
 *
 * NOTE(認証方式): 元々は $_SESSION['user_id'] を前提に実装されていたが、
 *       Auth機能はJWT（Authorization: Bearerヘッダー）で実装されたため、
 *       AuthMiddleware::requireUserId() に統一した（アプリ全体で認証方式は
 *       JWTの1本にする）。登録・更新・削除はログイン必須。クライアントから
 *       送られてきたuser_idは信用せず、必ずトークンから取得したIDを使う
 *       （Zero Trust, AGENTS.md 4章）。
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
        $userId = $this->getAuthenticatedUserId();
        if ($userId === null) {
            $this->respond(401, ['error' => 'ログインが必要です']);
            return;
        }

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
        $userId = $this->getAuthenticatedUserId();
        if ($userId === null) {
            $this->respond(401, ['error' => 'ログインが必要です']);
            return;
        }

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
        $userId = $this->getAuthenticatedUserId();
        if ($userId === null) {
            $this->respond(401, ['error' => 'ログインが必要です']);
            return;
        }

        try {
            $this->areaService->delete($id, $userId);
            http_response_code(204);
        } catch (\RuntimeException $e) {
            $this->respond($this->resolveErrorStatus($e), ['error' => $e->getMessage()]);
        }
    }

    /**
     * ログイン中のユーザーIDをJWTから取得する
     * 未ログイン／トークン不正の場合はAuthMiddleware側で401を返してexitするため、
     * ここでは基本的にnullは返らないが、呼び出し側の既存の null チェックは
     * 無害なので変更せず残している。
     */
    private function getAuthenticatedUserId(): ?int
    {
        return AuthMiddleware::requireUserId();
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
