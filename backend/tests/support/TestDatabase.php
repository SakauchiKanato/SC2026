<?php

/**
 * テスト専用のDB接続ヘルパー
 *
 * backend/src/Core/Database.php（他メンバー実装予定、まだ存在しない）とは別に、
 * このテストスイートから直接PostgreSQLへ接続するために用意している。
 *
 * config/database.php（共有ファイル）は読み込むだけで、変更は一切していない。
 * Core/Database.phpの実装が完了したら、このヘルパーは不要になる想定。
 */
class TestDatabase
{
    public static function connect(): \PDO
    {
        $config = require __DIR__ . '/../../config/database.php';

        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $config['host'],
            $config['port'],
            $config['dbname']
        );

        $pdo = new \PDO($dsn, $config['user'], $config['password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return $pdo;
    }
}
