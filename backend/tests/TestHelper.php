<?php
// Composer/PHPUnitを使わない最小限のテストハーネス。
// このbackendはフレームワーク・Composerなし方針（README参照）のため、
// テストも標準PHPだけで完結させる。
//
// 使い方（各 *Test.php から呼ぶ）：
//   require_once __DIR__ . '/TestHelper.php';
//   test('説明', function () { assertSame(1, 1); });
//   exit(testSummary());

$GLOBALS['__test_pass_count'] = 0;
$GLOBALS['__test_fail_count'] = 0;

function test(string $description, callable $fn): void
{
    try {
        $fn();
        $GLOBALS['__test_pass_count']++;
        echo "  ✓ {$description}\n";
    } catch (Throwable $e) {
        $GLOBALS['__test_fail_count']++;
        echo "  ✗ {$description}\n";
        echo "    " . get_class($e) . ': ' . $e->getMessage() . "\n";
    }
}

function assertSame($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $expectedStr = var_export($expected, true);
        $actualStr = var_export($actual, true);
        throw new RuntimeException(
            ($message !== '' ? "$message: " : '') . "expected {$expectedStr}, got {$actualStr}"
        );
    }
}

function assertTrue(bool $value, string $message = 'expected true'): void
{
    if ($value !== true) {
        throw new RuntimeException($message);
    }
}

function assertFalse(bool $value, string $message = 'expected false'): void
{
    if ($value !== false) {
        throw new RuntimeException($message);
    }
}

function assertContains(string $needle, array $haystack, string $message = ''): void
{
    foreach ($haystack as $item) {
        if (is_string($item) && str_contains($item, $needle)) {
            return;
        }
    }
    throw new RuntimeException(
        ($message !== '' ? "$message: " : '') . "'{$needle}' not found in " . var_export($haystack, true)
    );
}

function assertEmpty($value, string $message = 'expected empty value'): void
{
    if (!empty($value)) {
        throw new RuntimeException($message . ': ' . var_export($value, true));
    }
}

function assertThrows(callable $fn, string $message = 'expected an exception to be thrown'): void
{
    try {
        $fn();
    } catch (Throwable $e) {
        return;
    }
    throw new RuntimeException($message);
}

// 呼び出したテストファイルのpass/fail件数を表示し、終了コード（0=成功,1=失敗あり）を返す
function testSummary(): int
{
    $pass = $GLOBALS['__test_pass_count'];
    $fail = $GLOBALS['__test_fail_count'];
    echo "\n{$pass} passed, {$fail} failed\n";
    return $fail > 0 ? 1 : 0;
}
