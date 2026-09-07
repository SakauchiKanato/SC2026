<?php
// DB接続を1つだけ作って使い回すためのクラス
//
// 2種類の接続方法を提供している:
//   - getConnection()    : pg_connect（生のPostgreSQL関数）。Idea機能はこちらを使う。
//   - getPdoConnection() : PDO（pdo_pgsqlドライバ）。Area機能はこちらを使う。
//
// なぜ2つあるのか:
//   Idea機能とArea機能がそれぞれ別々の担当者によって、別々の接続方式を前提に
//   実装されていた（Idea側はpg_query系、Area側は$db->query()->fetchAll(PDO::FETCH_ASSOC)
//   のようなPDOのAPI）。どちらも同じPostgreSQLサーバーに接続するだけなので、
//   接続方式を無理に統一せず、両方のシングルトンを用意することで
//   お互いのモデル/サービスのコードを書き換えずに共存させている。
//   （Area::__construct()やAreaService::__construct()が \PDO 型ヒントを
//   要求しているため、pg_connectの結果（PgSql\Connection）を渡すと
//   TypeErrorになり、画面が真っ白になる不具合の原因になっていた）

class Database
{
    private static $connection = null;
    private static $pdoConnection = null;

    private static function loadConfig(): array
    {
        return require __DIR__ . '/../../config/database.php';
    }

    public static function getConnection()
    {
        if (self::$connection === null) {
            $config = self::loadConfig();

            $connString = sprintf(
                "host=%s port=%s dbname=%s user=%s password=%s",
                $config['host'],
                $config['port'],
                $config['dbname'],
                $config['user'],
                $config['password']
            );

            self::$connection = pg_connect($connString);

            if (self::$connection === false) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'DB接続に失敗しました: ' . pg_last_error()], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        return self::$connection;
    }

    public static function getPdoConnection(): \PDO
    {
        if (self::$pdoConnection === null) {
            $config = self::loadConfig();

            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $config['host'],
                $config['port'],
                $config['dbname']
            );

            try {
                self::$pdoConnection = new \PDO(
                    $dsn,
                    $config['user'],
                    $config['password'],
                    [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
            } catch (\PDOException $e) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'DB接続に失敗しました: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        return self::$pdoConnection;
    }
}
