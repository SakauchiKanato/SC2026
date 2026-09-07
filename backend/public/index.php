<?php
// エントリーポイント：すべてのリクエストがここを通る（簡易ルーター）

declare(strict_types=1);

// 開発中はエラーを画面に出す（本番サーバーにあげる際はオフにすること）
ini_set('display_errors', '1');
error_reporting(E_ALL);

// frontendを別ドメイン/別ポートで動かす場合のCORS対応
// （frontendと同じサーバー・同じオリジンで配信するなら基本的に不要）
// ワイルドカード(*)は使わず、許可するオリジンは環境変数で管理する（AGENTS.md 4節）
// 例: Apacheなら SetEnv ALLOWED_ORIGIN "https://example.com" のように設定する
$allowedOrigin = getenv('ALLOWED_ORIGIN') ?: 'http://localhost:5173';
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../src/Controllers/Idea/IdeaController.php';
require_once __DIR__ . '/../src/Controllers/Area/AreaController.php';
// TODO: Auth機能がmainにマージされたら追加する
// require_once __DIR__ . '/../src/Controllers/Auth/AuthController.php';
// require_once __DIR__ . '/../src/Controllers/Company/CompanyController.php';

$method = $_SERVER['REQUEST_METHOD'];

// PATH_INFOを使う（REQUEST_URIから素直に切り出す方式だと、共用サーバーの
// https://example.com/~user/プロジェクト名/backend/public/index.php のように
// index.phpが深い階層に置かれた場合にパスの切り出しがずれてしまうため）。
// index.php/ideas のようにindex.phpを明示したURLでアクセスすれば、
// index.php以降の部分がPATH_INFOとしてどんな設置階層でも正しく取れる。
// （Apache + PHPの標準機能で、rewriteルールの設定は不要）
$path = $_SERVER['PATH_INFO'] ?? '/';
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

// ルーティング定義：[HTTPメソッド, パスのパターン, コントローラー, メソッド名]
$routes = [
    ['GET',    '#^/ideas$#',       'IdeaController', 'index'],
    ['GET',    '#^/ideas/(\d+)$#', 'IdeaController', 'show'],
    ['POST',   '#^/ideas$#',       'IdeaController', 'store'],

    ['GET',    '#^/areas$#',        'AreaController', 'index'],
    ['GET',    '#^/areas/(\d+)$#',  'AreaController', 'show'],
    ['POST',   '#^/areas$#',        'AreaController', 'store'],
    ['PUT',    '#^/areas/(\d+)$#',  'AreaController', 'update'],
    ['DELETE', '#^/areas/(\d+)$#',  'AreaController', 'destroy'],
    ['GET',    '#^/feature-tags$#', 'AreaController', 'tags'],

    // TODO: Auth機能がmainにマージされたら追加する
    // ['POST', '#^/signup$#',   'AuthController', 'signup'],
    // ['POST', '#^/login$#',    'AuthController', 'login'],
];

foreach ($routes as [$routeMethod, $pattern, $controllerName, $action]) {
    if ($method !== $routeMethod) {
        continue;
    }

    if (preg_match($pattern, $path, $matches)) {
        $controller = new $controllerName();
        $params = array_slice($matches, 1);
        // URLパラメータ（idなど）は数値に変換してから渡す
        $params = array_map(
            fn($p) => is_numeric($p) ? (int) $p : $p,
            $params
        );

        $controller->$action(...$params);
        exit;
    }
}

http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['message' => 'Not Found'], JSON_UNESCAPED_UNICODE);
