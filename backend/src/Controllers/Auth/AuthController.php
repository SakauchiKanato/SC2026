<?php
// サインアップ・ログインに対応するコントローラー

require_once __DIR__ . '/../../Models/User.php';
require_once __DIR__ . '/../../Core/Jwt.php';
require_once __DIR__ . '/../../Core/AuthMiddleware.php';

class AuthController
{
    private const MAX_NAME_LENGTH = 100;
    private const MAX_EMAIL_LENGTH = 255;
    private const MIN_PASSWORD_LENGTH = 8;
    // usersテーブルのrole列のCHECK制約(user|company)と合わせる
    private const ALLOWED_ROLES = ['user', 'company'];

    // POST /api/signup … 新規登録。成功時はログインと同様にトークンを発行する（自動ログイン）。
    public function signup(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $errors = self::validateSignup($input);
        if (!empty($errors)) {
            self::jsonResponse(422, ['errors' => $errors]);
            return;
        }

        $name = trim($input['name']);
        $email = trim($input['email']);
        $password = $input['password'];
        // roleが未指定の場合は一般ユーザーとして扱う（後方互換のためのデフォルト値）
        $role = is_string($input['role'] ?? null) ? $input['role'] : 'user';

        try {
            if (User::findByEmail($email) !== null) {
                // 409 Conflict: 既に存在するリソースとの衝突（AGENTS.md 5節）
                self::jsonResponse(409, ['error' => 'このメールアドレスは既に登録されています']);
                return;
            }

            $userId = User::create($name, $email, $password, $role);
            $user = User::findById($userId);

            $token = Jwt::encode(['sub' => $userId, 'role' => $user['role']]);

            self::jsonResponse(201, ['data' => ['token' => $token, 'user' => $user]]);
        } catch (Throwable $e) {
            // TODO(SECURITY): メールアドレス等の個人情報はログに出さない（AGENTS.md 4節）。
            // ここでは例外メッセージのみを記録する。
            error_log('[AuthController::signup] ' . $e->getMessage());
            self::jsonResponse(500, ['error' => 'サインアップに失敗しました']);
        }
    }

    // POST /api/login … ログイン。成功時にJWTを発行する。
    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $errors = self::validateLogin($input);
        if (!empty($errors)) {
            self::jsonResponse(422, ['errors' => $errors]);
            return;
        }

        $email = trim($input['email']);
        $password = $input['password'];

        try {
            $user = User::findByEmail($email);

            // メールアドレスが存在しない場合とパスワードが違う場合を区別せず、
            // どちらも同じメッセージ・401で返す（メールアドレスの登録有無を推測されないようにするため）
            if ($user === null || !User::verifyPassword($password, $user['password_hash'])) {
                self::jsonResponse(401, ['error' => 'メールアドレスまたはパスワードが正しくありません']);
                return;
            }

            $token = Jwt::encode(['sub' => (int) $user['id'], 'role' => $user['role']]);

            unset($user['password_hash']); // レスポンスにハッシュ値を含めない

            self::jsonResponse(200, ['data' => ['token' => $token, 'user' => $user]]);
        } catch (Throwable $e) {
            error_log('[AuthController::login] ' . $e->getMessage());
            self::jsonResponse(500, ['error' => 'ログインに失敗しました']);
        }
    }

    // GET /api/me … 現在ログイン中のユーザー情報を返す（トークンの有効性チェックにも使う）
    public function me(): void
    {
        $userId = AuthMiddleware::requireUserId(); // 未認証の場合はここで401を返してexitする

        try {
            $user = User::findById($userId);

            if ($user === null) {
                self::jsonResponse(404, ['error' => 'ユーザーが見つかりません']);
                return;
            }

            self::jsonResponse(200, ['data' => $user]);
        } catch (Throwable $e) {
            error_log('[AuthController::me] ' . $e->getMessage());
            self::jsonResponse(500, ['error' => 'ユーザー情報の取得に失敗しました']);
        }
    }

    // private static: テストではReflectionMethod経由で呼び出す（IdeaController::validate()と同じ方針）
    private static function validateSignup(array $input): array
    {
        $errors = [];

        $name = is_string($input['name'] ?? null) ? trim($input['name']) : '';
        $email = is_string($input['email'] ?? null) ? trim($input['email']) : '';
        $password = is_string($input['password'] ?? null) ? $input['password'] : '';

        if ($name === '') {
            $errors[] = 'name は必須です';
        } elseif (mb_strlen($name) > self::MAX_NAME_LENGTH) {
            $errors[] = 'name は' . self::MAX_NAME_LENGTH . '文字以内で入力してください';
        }

        $errors = array_merge($errors, self::validateEmail($email));
        $errors = array_merge($errors, self::validatePassword($password));
        $errors = array_merge($errors, self::validateRole($input['role'] ?? null));

        return $errors;
    }

    // roleは未指定なら'user'扱いにするが、指定された場合はDBのCHECK制約と同じ値域だけ許可する
    // （クライアントから任意の文字列を受け取ってDB挿入エラーになる/意図しない権限が付与されるのを防ぐ）
    private static function validateRole($role): array
    {
        $errors = [];

        if ($role === null) {
            return $errors;
        }

        if (!is_string($role) || !in_array($role, self::ALLOWED_ROLES, true)) {
            $errors[] = 'role は ' . implode(' または ', self::ALLOWED_ROLES) . ' のいずれかを指定してください';
        }

        return $errors;
    }

    private static function validateLogin(array $input): array
    {
        $errors = [];

        if (empty($input['email'])) {
            $errors[] = 'email は必須です';
        }

        if (empty($input['password'])) {
            $errors[] = 'password は必須です';
        }

        return $errors;
    }

    private static function validateEmail(string $email): array
    {
        $errors = [];

        if ($email === '') {
            $errors[] = 'email は必須です';
        } elseif (strlen($email) > self::MAX_EMAIL_LENGTH) {
            $errors[] = 'email は' . self::MAX_EMAIL_LENGTH . '文字以内で入力してください';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'email の形式が正しくありません';
        }

        return $errors;
    }

    private static function validatePassword(string $password): array
    {
        $errors = [];

        if ($password === '') {
            $errors[] = 'password は必須です';
        } elseif (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $errors[] = 'password は' . self::MIN_PASSWORD_LENGTH . '文字以上で入力してください';
        } elseif (strlen($password) > User::MAX_PASSWORD_LENGTH) {
            // bcryptの仕様上72バイトを超える部分は無視されてしまうため、事前に弾く
            $errors[] = 'password は' . User::MAX_PASSWORD_LENGTH . '文字以内で入力してください';
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
