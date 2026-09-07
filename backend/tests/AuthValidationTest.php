<?php
// AuthController::validateSignup / validateLogin の単体テスト。
// これらはprivate staticメソッド（IdeaController::validate()と同じ方針で非公開にしている）なので、
// ReflectionMethodでアクセスして呼び出す。DB接続は発生しない。
// 実行: php backend/tests/AuthValidationTest.php

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';
require_once __DIR__ . '/../src/Controllers/Auth/AuthController.php';

function callPrivateStatic(string $class, string $method, array $args)
{
    $reflection = new ReflectionMethod($class, $method);
    $reflection->setAccessible(true);
    return $reflection->invokeArgs(null, $args);
}

function validateSignup(array $input): array
{
    return callPrivateStatic('AuthController', 'validateSignup', [$input]);
}

function validateLogin(array $input): array
{
    return callPrivateStatic('AuthController', 'validateLogin', [$input]);
}

echo "AuthValidationTest\n";

test('signup: 全項目が正しければエラーなし', function () {
    $errors = validateSignup([
        'name' => '山田太郎',
        'email' => 'taro@example.com',
        'password' => 'password123',
    ]);
    assertEmpty($errors);
});

test('signup: nameが空だとエラー', function () {
    $errors = validateSignup(['name' => '', 'email' => 'taro@example.com', 'password' => 'password123']);
    assertContains('name', $errors);
});

test('signup: emailが空だとエラー', function () {
    $errors = validateSignup(['name' => '山田太郎', 'email' => '', 'password' => 'password123']);
    assertContains('email', $errors);
});

test('signup: emailの形式が不正だとエラー', function () {
    $errors = validateSignup(['name' => '山田太郎', 'email' => 'not-an-email', 'password' => 'password123']);
    assertContains('email', $errors);
});

test('signup: passwordが短すぎる(8文字未満)とエラー', function () {
    $errors = validateSignup(['name' => '山田太郎', 'email' => 'taro@example.com', 'password' => 'short']);
    assertContains('password', $errors);
});

test('signup: passwordが72文字を超えるとエラー(bcryptの仕様上の上限)', function () {
    $errors = validateSignup([
        'name' => '山田太郎',
        'email' => 'taro@example.com',
        'password' => str_repeat('a', 73),
    ]);
    assertContains('password', $errors);
});

test('signup: nameが未指定(キー自体が無い)でも例外にならずエラーを返す', function () {
    $errors = validateSignup(['email' => 'taro@example.com', 'password' => 'password123']);
    assertContains('name', $errors);
});

test('signup: roleが未指定なら一般ユーザーとして扱われエラーにならない（後方互換のデフォルト値）', function () {
    $errors = validateSignup(['name' => '山田太郎', 'email' => 'taro@example.com', 'password' => 'password123']);
    assertEmpty($errors);
});

test("signup: role='company' は許可される（地域活性化を検討する企業アカウント）", function () {
    $errors = validateSignup([
        'name' => '株式会社サンプル',
        'email' => 'company@example.com',
        'password' => 'password123',
        'role' => 'company',
    ]);
    assertEmpty($errors);
});

test("signup: role='user' は許可される", function () {
    $errors = validateSignup([
        'name' => '山田太郎',
        'email' => 'taro@example.com',
        'password' => 'password123',
        'role' => 'user',
    ]);
    assertEmpty($errors);
});

test('signup: roleがusers/companyテーブルのCHECK制約に無い値だとエラー', function () {
    $errors = validateSignup([
        'name' => '山田太郎',
        'email' => 'taro@example.com',
        'password' => 'password123',
        'role' => 'admin',
    ]);
    assertContains('role', $errors);
});

test('login: email/passwordがどちらもあればエラーなし', function () {
    $errors = validateLogin(['email' => 'taro@example.com', 'password' => 'password123']);
    assertEmpty($errors);
});

test('login: emailが無いとエラー', function () {
    $errors = validateLogin(['password' => 'password123']);
    assertContains('email', $errors);
});

test('login: passwordが無いとエラー', function () {
    $errors = validateLogin(['email' => 'taro@example.com']);
    assertContains('password', $errors);
});

exit(testSummary());
