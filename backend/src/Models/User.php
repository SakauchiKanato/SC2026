<?php
// ユーザー（usersテーブル）に対するDB操作をまとめたクラス。
// パスワードのハッシュ化・検証もここに閉じ込め、
// 呼び出し側（Controller）が平文のまま保存してしまう事故を防ぐ（AGENTS.md 4節）。

require_once __DIR__ . '/../Core/Database.php';

class User
{
    // bcryptは72バイトを超える部分を無視する仕様のため、それより長いパスワードは
    // 意図せず切り詰められる。事前に弾けるようcontroller側のバリデーションでも使う。
    public const MAX_PASSWORD_LENGTH = 72;

    // メールアドレスでユーザーを検索する（ログイン・重複チェックで使用）
    public static function findByEmail(string $email): ?array
    {
        $conn = Database::getConnection();

        $sql = "
            SELECT id, name, email, password_hash, role, created_at
            FROM sc2026_users
            WHERE email = $1
        ";

        $result = pg_query_params($conn, $sql, [$email]);

        if ($result === false) {
            throw new RuntimeException('ユーザー検索に失敗しました: ' . pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);

        return $row ?: null;
    }

    // IDでユーザーを検索する（/me やトークン検証後の再取得で使用）
    public static function findById(int $id): ?array
    {
        $conn = Database::getConnection();

        $sql = "
            SELECT id, name, email, role, created_at
            FROM sc2026_users
            WHERE id = $1
        ";

        $result = pg_query_params($conn, $sql, [$id]);

        if ($result === false) {
            throw new RuntimeException('ユーザー検索に失敗しました: ' . pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);

        return $row ?: null;
    }

    // ユーザーを新規登録する（サインアップ）。
    // role はAuthController側でバリデーション済み（'user'|'company'）の値を受け取る。
    // company_profiles（会社名など）の登録は別機能（Company）で扱うためここでは行わない。
    public static function create(string $name, string $email, string $plainPassword, string $role): int
    {
        $conn = Database::getConnection();

        $passwordHash = self::hashPassword($plainPassword);

        $sql = "
            INSERT INTO sc2026_users (name, email, password_hash, role)
            VALUES ($1, $2, $3, $4)
            RETURNING id
        ";

        $result = pg_query_params($conn, $sql, [$name, $email, $passwordHash, $role]);

        if ($result === false) {
            throw new RuntimeException('ユーザーの登録に失敗しました: ' . pg_last_error($conn));
        }

        $row = pg_fetch_assoc($result);

        return (int) $row['id'];
    }

    // パスワードをbcryptでハッシュ化する（平文保存の禁止：AGENTS.md 4節）
    public static function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    // 平文パスワードとハッシュを比較する
    public static function verifyPassword(string $plainPassword, string $passwordHash): bool
    {
        return password_verify($plainPassword, $passwordHash);
    }
}
