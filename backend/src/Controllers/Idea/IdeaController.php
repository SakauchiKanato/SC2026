<?php
// アイデア入力・アイデア閲覧に対応するコントローラー
// レスポンス／エラーの形はfrontend側（src/api/client.js）の実装に合わせている
// （成功時はdataをそのまま返す、エラー時は{ message: string }を返す）
//
// 「街タネ」UIでは、発案者が特定の地域ページ（/areas/{areaId}）から
// アイデア一覧・登録に進むため、すべて地域に紐づくエンドポイントになっている。
//   GET  /api/areas/{areaId}/ideas       -> index($areaId)  新着／過去のアイデア一覧
//   POST /api/areas/{areaId}/ideas       -> store($areaId)  アイデア登録（ログイン必須）
//   GET  /api/ideas/{id}                 -> show($id)       アイデア詳細

require_once __DIR__ . '/../../Models/Idea.php';
require_once __DIR__ . '/../../Models/Area.php';
require_once __DIR__ . '/../../Core/AuthMiddleware.php';

class IdeaController
{
    private const MAX_TITLE_LENGTH = 60;
    private const MAX_CONTENT_LENGTH = 1000;
    private const MAX_REASON_LENGTH = 1000;

    // GET /api/areas/{areaId}/ideas?status=... … 地域ごとのアイデア一覧（新着／過去のアイデア）
    public function index(int $areaId): void
    {
        try {
            if (!(new Area())->exists($areaId)) {
                self::jsonResponse(404, ['message' => '指定された地域が見つかりません']);
                return;
            }

            $filters = [
                'status' => $_GET['status'] ?? null,
            ];

            $ideas = Idea::allForArea($areaId, $filters);
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

    // POST /api/areas/{areaId}/ideas … アイデア登録（アイデア入力）
    // 認証: ログイン中のユーザーIDはAuthMiddleware::requireUserId()経由で
    //       Authorizationヘッダー（JWT）から取得する。トークンが無い/不正な
    //       場合はAuthMiddleware側で401を返して処理を終了する（ログイン必須）。
    public function store(int $areaId): void
    {
        // トークンが無い/不正な場合はAuthMiddleware内で401を返してexitする
        $userId = AuthMiddleware::requireUserId();

        if (!(new Area())->exists($areaId)) {
            self::jsonResponse(404, ['message' => '指定された地域が見つかりません']);
            return;
        }

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
                'area_id'   => $areaId,
                'title'     => trim($input['title']),
                'status'    => $input['status'],
                'content'   => trim($input['content']),
                'reason'    => trim($input['reason']),
                'user_id'   => $userId,
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

    private static function jsonResponse(int $status, $body): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}
