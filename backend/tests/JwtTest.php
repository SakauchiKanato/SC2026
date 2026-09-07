<?php
// Jwt::encode / Jwt::decode の単体テスト。
// 実行: php backend/tests/JwtTest.php
// config/jwt.local.php （またはJWT_SECRET環境変数）が必要。

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';
require_once __DIR__ . '/../src/Core/Jwt.php';

echo "JwtTest\n";

test('encodeしたトークンをdecodeすると同じペイロードが戻る', function () {
    $token = Jwt::encode(['sub' => 42, 'role' => 'user']);
    $payload = Jwt::decode($token);

    assertSame(42, $payload['sub']);
    assertSame('user', $payload['role']);
});

test('発行したトークンにはexp(有効期限)とiat(発行時刻)が自動で付与される', function () {
    $token = Jwt::encode(['sub' => 1]);
    $payload = Jwt::decode($token);

    assertTrue(isset($payload['exp']), 'expが無い');
    assertTrue(isset($payload['iat']), 'iatが無い');
    assertTrue($payload['exp'] > $payload['iat'], 'expはiatより未来のはず');
});

test('ペイロード部分を改ざんしたトークンはdecodeで例外になる（署名検証）', function () {
    $token = Jwt::encode(['sub' => 1, 'role' => 'user']);
    [$header, $payload, $signature] = explode('.', $token);

    // roleをcompanyに書き換えた改ざんペイロードを作る
    $tamperedPayload = rtrim(strtr(base64_encode(json_encode(['sub' => 1, 'role' => 'company'])), '+/', '-_'), '=');
    $tamperedToken = "$header.$tamperedPayload.$signature";

    assertThrows(fn() => Jwt::decode($tamperedToken), '改ざんされたトークンはdecodeで例外を投げるべき');
});

test('形式が不正なトークン(ドット区切りが3つでない)はdecodeで例外になる', function () {
    assertThrows(fn() => Jwt::decode('not-a-jwt'));
});

test('有効期限切れのトークンはdecodeで例外になる', function () {
    $expiredToken = Jwt::encode(['sub' => 1, 'exp' => time() - 10]);
    assertThrows(fn() => Jwt::decode($expiredToken));
});

exit(testSummary());
