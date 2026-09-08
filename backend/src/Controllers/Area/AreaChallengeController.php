<?php
// 「企業・自治体が実現したいこと」（課題点・問題点／期待する未来）の投稿に対応するコントローラー。
//
// 地域を登録していない企業・自治体も含め、企業・自治体アカウント（role=company）は
// 誰でも・何件でも、任意の地域に対してこの投稿ができる（掲示板形式）。
// 一覧の閲覧自体はログイン不要（発案者もアイデア登録前の参考として見られる）。
//
//   GET    /api/areas/{areaId}/challenges -> index($areaId)  投稿一覧
//   POST   /api/areas/{areaId}/challenges -> store($areaId)  新規投稿（company限定）
//   PUT    /api/challenges/{id}           -> update($id)     更新（投稿者本人のみ）
//   DELETE /api/challenges/{id}           -> destroy($id)    削除（投稿者本人のみ）

require_once __DIR__ . '/../../Models/AreaChallengeRequest.php';
require_once __DIR__ . '/../../Models/Area.php';
require_once __DIR__ . '/../../Core/AuthMiddleware.php';

class AreaChallengeController
{
    private const MAX_CHALLENGES_LENGTH = 2000;
    private const MAX_EXPECTED_FUTURE_LENGTH = 2000;

    // GET /api/areas/{areaId}/challenges … 地域ごとの投稿一覧
    public function index(int $areaId): void
    {
        try {
            if (!(new Area())->exists($areaId)) {
                self::jsonResponse(404, ['message' => '指定された地域が見つかりません']);
                return;
            }

            $requests = (new AreaChallengeRequest())->allForArea($areaId);
            self::jsonResponse(200, $requests);
        } catch (Throwable $e) {
            self::jsonResponse(500, ['message' => $e->getMessage()]);
        }
    }

    // POST /api/areas/{areaId}/challenges … 新規投稿
    // 認可: 企業・自治体アカウント（role=company）のみ。地域の登録者本人である必要はない
    //       （地域を登録していない別の企業・自治体からの投稿も許可する掲示板形式のため）。
    public function store(int $areaId): void
    {
        $auth = AuthMiddleware::requireAuth();

        if ($auth['role'] !== 'company') {
            self::jsonResponse(403, ['message' => 'この投稿は企業・自治体アカウントのみ行えます']);
            return;
        }

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
            $model = new AreaChallengeRequest();
            $id = $model->create([
                'area_id'         => $areaId,
                'user_id'         => $auth['id'],
                'challenges'      => trim($input['challenges']),
                'expected_future' => trim($input['expected_future']),
            ]);

            self::jsonResponse(201, $model->find($id));
        } catch (Throwable $e) {
            self::jsonResponse(500, ['message' => $e->getMessage()]);
        }
    }

    // PUT /api/challenges/{id} … 更新（投稿者本人のみ）
    public function update(int $id): void
    {
        $auth = AuthMiddleware::requireAuth();

        $model = new AreaChallengeRequest();
        $request = $model->find($id);

        if ($request === null) {
            self::jsonResponse(404, ['message' => '指定された投稿が見つかりません']);
            return;
        }

        if ((int) $request['user_id'] !== $auth['id']) {
            self::jsonResponse(403, ['message' => 'この投稿を編集する権限がありません']);
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
            $model->update($id, [
                'challenges'      => trim($input['challenges']),
                'expected_future' => trim($input['expected_future']),
            ]);

            self::jsonResponse(200, $model->find($id));
        } catch (Throwable $e) {
            self::jsonResponse(500, ['message' => $e->getMessage()]);
        }
    }

    // DELETE /api/challenges/{id} … 削除（投稿者本人のみ）
    public function destroy(int $id): void
    {
        $auth = AuthMiddleware::requireAuth();

        $model = new AreaChallengeRequest();
        $request = $model->find($id);

        if ($request === null) {
            self::jsonResponse(404, ['message' => '指定された投稿が見つかりません']);
            return;
        }

        if ((int) $request['user_id'] !== $auth['id']) {
            self::jsonResponse(403, ['message' => 'この投稿を削除する権限がありません']);
            return;
        }

        try {
            $model->delete($id);
            http_response_code(204);
        } catch (Throwable $e) {
            self::jsonResponse(500, ['message' => $e->getMessage()]);
        }
    }

    // フロントのバリデーションだけに頼らず、バックエンド側でも入力値を検証する（Zero Trust）
    private static function validate(array $input): array
    {
        $errors = [];

        $challenges = trim((string) ($input['challenges'] ?? ''));
        if ($challenges === '') {
            $errors[] = '課題点・問題点を入力してください';
        } elseif (mb_strlen($challenges) > self::MAX_CHALLENGES_LENGTH) {
            $errors[] = '課題点・問題点は' . self::MAX_CHALLENGES_LENGTH . '文字以内で入力してください';
        }

        $expectedFuture = trim((string) ($input['expected_future'] ?? ''));
        if ($expectedFuture === '') {
            $errors[] = '期待する未来を入力してください';
        } elseif (mb_strlen($expectedFuture) > self::MAX_EXPECTED_FUTURE_LENGTH) {
            $errors[] = '期待する未来は' . self::MAX_EXPECTED_FUTURE_LENGTH . '文字以内で入力してください';
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
