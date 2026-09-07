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
echo "正常系: nameのみ入力（他は任意項目なので省略可）\n";
$result = $validator->validate(['name' => '〇〇地区']);
assertEquals('〇〇地区', $result['name'], 'nameが正しく格納される');
assertEquals(null, $result['features'], 'features未入力時はnullになる');
assertEquals(null, $result['address'], 'address未入力時はnullになる');
assertEquals([], $result['tags'], 'タグ未指定時は空配列になる');
assertEquals(null, $result['latitude'], '緯度未入力時はnullになる');

echo "\n正常系: 全項目入力\n";
$result = $validator->validate([
    'name' => '〇〇地区',
    'features' => 'IT企業が多く集まる地区',
    'address' => '東京都渋谷区〇〇1-2-3',
    'latitude' => '35.6595',
    'longitude' => '139.7005',
    'tags' => ['温泉', '自然が多い', '温泉'], // 重複あり
]);
assertEquals(35.6595, $result['latitude'], '緯度が数値に変換される');
assertEquals(139.7005, $result['longitude'], '経度が数値に変換される');
assertEquals(['温泉', '自然が多い'], $result['tags'], 'タグの重複が除去される');
assertEquals('東京都渋谷区〇〇1-2-3', $result['address'], 'addressが保存される');

// --- 異常系 ---
echo "\n異常系: nameが空\n";
try {
    $validator->validate(['name' => '']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['name']), 'nameのエラーが含まれる');
}

echo "\n異常系: nameが最大文字数を超える\n";
try {
    $validator->validate(['name' => str_repeat('あ', 256)]);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['name']), '文字数超過のエラーが含まれる');
}

echo "\n異常系: 緯度が範囲外\n";
try {
    $validator->validate(['name' => '〇〇地区', 'latitude' => '999']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['latitude']), '緯度の範囲エラーが含まれる');
}

echo "\n異常系: 緯度が数値でない\n";
try {
    $validator->validate(['name' => '〇〇地区', 'latitude' => 'abc']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['latitude']), '緯度が数値でない旨のエラーが含まれる');
}

echo "\n異常系: タグが配列でない\n";
try {
    $validator->validate(['name' => '〇〇地区', 'tags' => '温泉']);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['tags']), 'タグ形式不正のエラーが含まれる');
}

echo "\n異常系: タグの数が上限(10個)を超える\n";
try {
    $validator->validate([
        'name' => '〇〇地区',
        'tags' => array_map(fn($i) => "タグ{$i}", range(1, 11)),
    ]);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['tags']), 'タグ数超過のエラーが含まれる');
}

echo "\n異常系: addressが最大文字数(255文字)を超える\n";
try {
    $validator->validate(['name' => '〇〇地区', 'address' => str_repeat('あ', 256)]);
    assertTrue(false, '例外が投げられるべき');
} catch (AreaValidationException $e) {
    assertTrue(isset($e->getErrors()['address']), 'address文字数超過のエラーが含まれる');
}

echo "\n--------------------------------\n";
echo "結果: {$passCount} PASS / {$failureCount} FAIL\n";

exit($failureCount > 0 ? 1 : 0);
