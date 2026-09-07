<?php

/**
 * AreaValidator の単体テスト
 *
 * フレームワークを使わず、PHP標準機能のみで実行できる簡易テストスクリプト。
 * Composer/PHPUnit不使用のため、assert関数を自前で用意している。
 *
 * 実行方法:
 *   php backend/tests/AreaValidatorTest.php
 *
 * Core/Database.php や実際のDB接続には依存しないため、
 * 他メンバーの実装状況に関わらず今すぐ実行できる。
 */

require_once __DIR__ . '/../src/Services/Area/AreaValidator.php';

$failureCount = 0;
$passCount = 0;

/**
 * @param mixed $expected
 * @param mixed $actual
 */
function assertEquals($expected, $actual, string $message): void
{
    global $failureCount, $passCount;

    if ($expected === $actual) {
        echo "  PASS: {$message}\n";
        $passCount++;
        return;
    }

    echo "  FAIL: {$message}\n";
    echo '    expected: ' . var_export($expected, true) . "\n";
    echo '    actual:   ' . var_export($actual, true) . "\n";
    $failureCount++;
}

function assertTrue(bool $condition, string $message): void
{
    global $failureCount, $passCount;

    if ($condition) {
        echo "  PASS: {$message}\n";
        $passCount++;
        return;
    }

    echo "  FAIL: {$message}\n";
    $failureCount++;
}

$validator = new AreaValidator();

// --- 正常系 ---
// name・challenges（課題点・問題点）・expected_future（期待する未来）は必須。
// features/address/latitude/longitude/tagsは任意項目（街タネUIの地域登録フォームに合わせる）
echo "正常系: 必須項目のみ入力（他は任意項目なので省略可）\n";
$result = $validator->validate([
    'name' => '〇〇地区',
    'challenges' => '若者の流出が課題',
    'expected_future' => '週末に人が集まる街にしたい',
]);
assertEquals('〇〇地区', $result['name'], 'nameが正しく格納される');
assertEquals(null, $result['features'], 'features未入力時はnullになる');
assertEquals(null, $result['address'], 'address未入力時はnullになる');
assertEquals([], $result['tags'], 'タグ未指定時は空配列になる');
assertEquals(null, $result['latitude'], '緯度未入力時はnullになる');
assertEquals('若者の流出が課題', $result['challenges'], 'challengesが正しく格納される');
assertEquals('週末に人が集まる街にしたい', $result['expected_future'], 'expected_futureが正しく格納される');

echo "\n正常系: 全項目入力\n";
$result = $validator->validate([
    'name' => '〇〇地区',
    'features' => 'IT企業が多く集まる地区',
    'address' => '東京都渋谷区〇〇1-2-3',
    'latitude' => '35.6595',
    'longitude' => '139.7005',
    'tags' => ['温泉', '自然が多い', '温泉'], // 重複あり
    'challenges' => '若者の流出が課題',
    'expected_future' => '週末に人が集まる街にしたい',
]);
assertEquals(35.6595, $result['latitude'], '緯度が数値に変換される');
assertEquals(139.7005, $result['longitude'], '経度が数値に変換される');
assertEquals(['温泉', '自然が多い'], $result['tags'], 'タグの重複が除去される');
assertEquals('東京都渋谷区〇〇1-2-3', $result['address'], 'addressが保存される');

// --- 異常系 ---
echo "\n異常系: nameが空\n";
try {
    $validator->validate(['name' => '', 'challenges' => '課題', 'expected_future' => '未来']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['name']), 'nameのエラーが含まれる');
}

echo "\n異常系: nameが最大文字数を超える\n";
try {
    $validator->validate(['name' => str_repeat('あ', 256), 'challenges' => '課題', 'expected_future' => '未来']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['name']), '文字数超過のエラーが含まれる');
}

echo "\n異常系: 緯度が範囲外\n";
try {
    $validator->validate(['name' => '〇〇地区', 'latitude' => '999', 'challenges' => '課題', 'expected_future' => '未来']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['latitude']), '緯度の範囲エラーが含まれる');
}

echo "\n異常系: 緯度が数値でない\n";
try {
    $validator->validate(['name' => '〇〇地区', 'latitude' => 'abc', 'challenges' => '課題', 'expected_future' => '未来']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['latitude']), '緯度が数値でない旨のエラーが含まれる');
}

echo "\n異常系: タグが配列でない\n";
try {
    $validator->validate(['name' => '〇〇地区', 'tags' => '温泉', 'challenges' => '課題', 'expected_future' => '未来']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['tags']), 'タグ形式不正のエラーが含まれる');
}

echo "\n異常系: タグの数が上限(10個)を超える\n";
try {
    $validator->validate([
        'name' => '〇〇地区',
        'tags' => array_map(fn($i) => "タグ{$i}", range(1, 11)),
        'challenges' => '課題',
        'expected_future' => '未来',
    ]);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['tags']), 'タグ数超過のエラーが含まれる');
}

echo "\n異常系: addressが最大文字数(255文字)を超える\n";
try {
    $validator->validate([
        'name' => '〇〇地区',
        'address' => str_repeat('あ', 256),
        'challenges' => '課題',
        'expected_future' => '未来',
    ]);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['address']), 'address文字数超過のエラーが含まれる');
}

echo "\n異常系: challenges（課題点・問題点）が空\n";
try {
    $validator->validate(['name' => '〇〇地区', 'expected_future' => '未来']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['challenges']), 'challengesのエラーが含まれる');
}

echo "\n異常系: expected_future（期待する未来）が空\n";
try {
    $validator->validate(['name' => '〇〇地区', 'challenges' => '課題']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['expected_future']), 'expected_futureのエラーが含まれる');
}

echo "\n--------------------------------\n";
echo "結果: {$passCount} PASS / {$failureCount} FAIL\n";

exit($failureCount > 0 ? 1 : 0);
