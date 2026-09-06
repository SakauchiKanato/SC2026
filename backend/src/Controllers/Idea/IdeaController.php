<?php
// アイデア入力・アイデア閲覧に対応するコントローラー

require_once __DIR__ . '/../../Models/Idea.php';

class IdeaController
{
    // GET /api/ideas … アイデア一覧（アイデア閲覧）
    public function index(): void
    {
        try {
            $ideas = Idea::all();
            self::jsonResponse(200, ['data' => $ideas]);
        } catch (Throwable $e) {
            self::jsonResponse(500, ['error' => $e->getMessage()]);
        }
    }

    // GET /api/ideas/{id} … アイデア詳細（アイデア閲覧）
    public function show(int $id): void
    {
        try {
            $idea = Idea::find($id);

            if ($idea === null) {
                self::jsonResponse(404, ['error' => '指定されたアイデアが見つかりません']);
                return;
            }

            self::jsonResponse(200, ['data' => $idea]);
        } catch (Throwable $e) {
            self::jsonResponse(500, ['error' => $e->getMessage()]);
        }
    }

    // POST /api/ideas … アイデア登録（アイデア入力）
    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $errors = self::validate($input);

        if (!empty($errors)) {
            self::jsonResponse(422, ['errors' => $errors]);
            return;
        }

        try {
            $id = Idea::create($input);
            self::jsonResponse(201, ['data' => ['id' => $id]]);
        } catch (Throwable $e) {
            self::jsonResponse(500, ['error' => $e->getMessage()]);
        }
    }

    private static function validate(array $input): array
    {
        $errors = [];

        // TODO(SECURITY): user_id はリクエストからそのまま信用せず、ログイン機能実装後は
        // 認証トークン（セッション/JWTなど）から取得するように修正する（AGENTS.md 4節）
        // 現時点ではログイン機能が未実装のため、暫定的にリクエストから受け取っている
        if (empty($input['area_id'])) {
            $errors[] = 'area_id は必須です';
        }

        if (empty($input['user_id'])) {
            $errors[] = 'user_id は必須です';
        }

        if (empty($input['title'])) {
            $errors[] = 'title は必須です';
        }

        if (empty($input['summary'])) {
            $errors[] = 'summary は必須です';
        }

        return $errors;
    }

    private static function jsonResponse(int $status, array $body): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}
