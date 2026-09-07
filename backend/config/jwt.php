<?php
// JWT関連の設定。
// database.php と同じ方針：ローカル開発では jwt.local.php（gitで管理しない）を使い、
// 本番サーバーなど jwt.local.php が無い環境では環境変数から読み込む。

$localConfigPath = __DIR__ . '/jwt.local.php';

if (file_exists($localConfigPath)) {
    return require $localConfigPath;
}

return [
    // JWT_SECRET は必須。未設定の場合は Jwt::getSecret() が例外を投げる
    // （AGENTS.md 6節：環境変数は起動時にバリデーションし、未設定のまま動かさない）。
    'secret'        => getenv('JWT_SECRET') ?: '',
    'expirySeconds' => (int) (getenv('JWT_EXPIRY_SECONDS') ?: 86400), // デフォルト24時間
];
