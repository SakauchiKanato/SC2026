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
 *
 * NOTE: 認証済みユーザーIDは $_SESSION['user_id'] に格納されている前提で
 *       実装している（Auth担当の実装が固まり次第、要すり合わせ）。
 *       登録・更新・削除はログイン必須。クライアントから送られてきたuser_idは
 *       信用せず、必ずセッションから取得したIDを使う（Zero Trust, AGENTS.md 4章）。
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
     * ログイン中のユーザーIDをセッションから取得する
     * NOTE: Auth担当の実装方法（セッション名など）が確定次第、要調整
     */
    private function getAuthenticatedUserId(): ?int
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
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
