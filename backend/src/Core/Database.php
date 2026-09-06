<?php
// DB接続を1つだけ作って使い回すためのクラス（pg_connectを使用）

class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            $config = require __DIR__ . '/../../config/database.php';

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
}
