<?php
// DB接続設定
// 実際の接続情報は database.local.php（gitで管理しない）に書く。
// database.local.php が無い環境（本番サーバーなど）では環境変数から読み込む。

$localConfigPath = __DIR__ . '/database.local.php';

if (file_exists($localConfigPath)) {
    return require $localConfigPath;
}

return [
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'port'     => getenv('DB_PORT') ?: '5432',
    'dbname'   => getenv('DB_NAME') ?: '',
    'user'     => getenv('DB_USER') ?: '',
    'password' => getenv('DB_PASSWORD') ?: '',
];
