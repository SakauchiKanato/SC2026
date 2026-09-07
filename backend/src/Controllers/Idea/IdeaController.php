<?php
// アイデア入力・アイデア閲覧に対応するコントローラー
// レスポンス／エラーの形はfrontend側（src/api/client.js）の実装に合わせている
// （成功時はdataをそのまま返す、エラー時は{ message: string }を返す）

require_once __DIR__ . '/../../Models/Idea.php';
require_once __DIR__ . '/../../Core/AuthMiddleware.php';

class IdeaController
{
    private const MAX_TITLE_LENGTH = 60;
    private const MAX_CONTENT_LENGTH = 1000;
    private const MAX_REASON_LENGTH = 1000;

    // GET /api/ideas?area_name=...&area_id=...&status=... … アイデア一覧（アイデア閲覧）
    public function index(): void
    {
        try {
            $filters = [
                'area_name' => $_GET['area_name'] ?? null,
                'area_id'   => $_GET['area_id'] ?? null,
                'status'    => $_GET['status'] ?? null,
            ];

            $ideas = Idea::all($filters);
            self::jsonResponse(200, $ideas);
        } catch (Throwable $e) {
            self::jsonResponse(500, ['message' => $e->getMessage()]);
        }
    }

    // GET /api/ideas/{id} … アイデア詳細（アイデア閲覧）
    public function show(int $id): void
    {
        try {
            $idea = Idea::find($id);

            if ($idea === null) {
                self::jsonResponse(404, ['message' => '指定されたアイデアが見つかりません']);
                return;
            }

            self::jsonResponse(200, $idea);
        } catch (Throwable $e) {
            self::jsonResponse(500, ['message' => $e->getMessage()]);
        }
    }

    // POST /api/ideas … アイデア登録（アイデア入力）
    // ログイン必須。未ログインの場合はAuthMiddleware::requireUserId()が401を返してexitする
    // （AGENTS.md 4節: client_idを信用せず、必ず認証トークンからuser_idを取得する）。
    public function store(): void
    {
        $userId = AuthMiddleware::requireUserId();

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $errors = self::validate($input);

        if (!empty($errors)) {
            self::jsonResponse(422, [
                'message' => $errors[0],
                'errors'  => $errors,
            ]);
            return;
        }

        try {
            $id = Idea::create([
                'area_id' => (int) $input['area_id'],
                'title'   => trim($input['title']),
                'status'  => $input['status'],
                'content' => trim($input['content']),
                'reason'  => trim($input['reason']),
                'user_id' => $userId,
            ]);

            $idea = Idea::find($id);
            self::jsonResponse(201, $idea);
        } catch (Throwable $e) {
            self::jsonResponse(500, ['message' => $e->getMessage()]);
        }
    }

    // フロントのバリデーションだけに頼らず、バックエンド側でも入力値を検証する（Zero Trust）
    private static function validate(array $input): array
    {
        $errors = [];

        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            $errors[] = 'タイトルを入力してください';
        } elseif (mb_strlen($title) > self::MAX_TITLE_LENGTH) {
            $errors[] = 'タイトルは' . self::MAX_TITLE_LENGTH . '文字以内で入力してください';
        }

        $errors = array_merge($errors, self::validateAreaId($input['area_id'] ?? null));

        $status = $input['status'] ?? '';
        if (!in_array($status, ['success', 'failure'], true)) {
            $errors[] = '結果はsuccess（成功）かfailure（失敗）のいずれかを指定してください';
        }

        $content = trim((string) ($input['content'] ?? ''));
        if ($content === '') {
            $errors[] = 'アイデアの内容を入力してください';
        } elseif (mb_strlen($content) > self::MAX_CONTENT_LENGTH) {
            $errors[] = 'アイデアの内容は' . self::MAX_CONTENT_LENGTH . '文字以内で入力してください';
        }

        $reason = trim((string) ($input['reason'] ?? ''));
        if ($reason === '') {
            $errors[] = '理由を入力してください';
        } elseif (mb_strlen($reason) > self::MAX_REASON_LENGTH) {
            $errors[] = '理由は' . self::MAX_REASON_LENGTH . '文字以内で入力してください';
        }

        return $errors;
    }

    // area_id は「実在する登録済み地域」を指していることまで確認する
    // （地域名を自由入力にすると、存在しない地域でもアイデアを登録できてしまうため）
    private static function validateAreaId($areaId): array
    {
        $errors = [];

        if ($areaId === null || $areaId === '') {
            $errors[] = '地域を選択してください（先に地域の登録が必要です）';
            return $errors;
        }

        if (!is_numeric($areaId) || (int) $areaId <= 0) {
            $errors[] = '地域の指定が不正です';
            return $errors;
        }

        if (!Idea::areaExists((int) $areaId)) {
            $errors[] = '指定された地域が見つかりません。先に地域を登録してください';
        }

        return $errors;
    }

    private static function jsonResponse(int $status, $body): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}
