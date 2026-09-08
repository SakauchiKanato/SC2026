<?php
// Authorizationヘッダーからログインユーザーを取り出す共通処理。
// 今後 Idea/Area/Company のコントローラーでも「client_idを信用せずトークンから取る」
// (AGENTS.md 4節) 対応をする際に、この AuthMiddleware::requireUserId() を使う想定。

require_once __DIR__ . '/Jwt.php';

class AuthMiddleware
{
    // 認証必須のエンドポイント用。トークンが無い/不正な場合は401を返してexitする。
    public static function requireUserId(): int
    {
        return self::requireAuth()['id'];
    }

    // ユーザーIDに加えてロール（user/company）も必要な場合はこちらを使う。
    // 例：企業・自治体アカウントのみに許可したい操作の認可チェック。
    // 返り値: ['id' => int, 'role' => ?string]（roleはトークンに含まれない場合はnull）
    /**
     * @return array{id: int, role: ?string}
     */
    public static function requireAuth(): array
    {
        $token = self::getBearerToken();

        if ($token === null) {
            self::unauthorized('認証トークンがありません');
        }

        try {
            $payload = Jwt::decode($token);
        } catch (Throwable $e) {
            self::unauthorized('認証トークンが無効です');
        }

        if (!isset($payload['sub'])) {
            self::unauthorized('認証トークンが不正です');
        }

        return [
            'id' => (int) $payload['sub'],
            'role' => isset($payload['role']) && is_string($payload['role']) ? $payload['role'] : null,
        ];
    }

    // Authorization: Bearer <token> からトークン文字列だけを取り出す。
    // Apache環境ではHTTP_AUTHORIZATIONが渡らないことがあるため、
    // getallheaders() とフォールバック用のREDIRECT_HTTP_AUTHORIZATIONも見る。
    private static function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? null;

        if ($header === null && function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (strtolower($name) === 'authorization') {
                    $header = $value;
                    break;
                }
            }
        }

        if ($header === null || !preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return null;
        }

        return $matches[1];
    }

    private static function unauthorized(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
