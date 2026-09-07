<?php
// User::hashPassword / User::verifyPassword の単体テスト（DB接続は使わない）。
// 実行: php backend/tests/UserPasswordTest.php

declare(strict_types=1);

require_once __DIR__ . '/TestHelper.php';
require_once __DIR__ . '/../src/Models/User.php';

echo "UserPasswordTest\n";

test('パスワードは平文のまま保存されず、ハッシュ化される', function () {
    $hash = User::hashPassword('correct-horse-battery-staple');
    assertTrue($hash !== 'correct-horse-battery-staple', 'ハッシュ化されていない（平文と一致してしまっている）');
});

test('bcryptハッシュであること（$2y$ プレフィックス）', function () {
    $hash = User::hashPassword('correct-horse-battery-staple');
    assertTrue(str_starts_with($hash, '$2y$'), 'bcryptのハッシュ形式ではない: ' . $hash);
});

test('正しいパスワードならverifyPasswordがtrueを返す', function () {
    $hash = User::hashPassword('correct-horse-battery-staple');
    assertTrue(User::verifyPassword('correct-horse-battery-staple', $hash));
});

test('間違ったパスワードならverifyPasswordがfalseを返す', function () {
    $hash = User::hashPassword('correct-horse-battery-staple');
    assertFalse(User::verifyPassword('wrong-password', $hash));
});

test('同じ平文パスワードでもハッシュ化のたびに異なるハッシュ値になる（ソルトが効いている）', function () {
    $hashA = User::hashPassword('same-password');
    $hashB = User::hashPassword('same-password');
    assertTrue($hashA !== $hashB, 'ソルトなしで毎回同じハッシュになっている');
});

exit(testSummary());
