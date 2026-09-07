<?php
// backend/tests 配下の *Test.php をすべて実行するランナー。
// Composer/PHPUnitを使わない方針のため、各テストファイルを別プロセスで実行し、
// 終了コード(0=成功,1=失敗)を集計する。
//
// 実行: php backend/tests/run_tests.php

declare(strict_types=1);

$testFiles = glob(__DIR__ . '/*Test.php');
sort($testFiles);

if (empty($testFiles)) {
    echo "テストファイルが見つかりませんでした\n";
    exit(1);
}

$hasFailure = false;

foreach ($testFiles as $testFile) {
    echo '=== ' . basename($testFile) . " ===\n";
    passthru('php ' . escapeshellarg($testFile), $exitCode);
    echo "\n";

    if ($exitCode !== 0) {
        $hasFailure = true;
    }
}

if ($hasFailure) {
    echo "失敗したテストがあります\n";
    exit(1);
}

echo "すべてのテストが成功しました\n";
exit(0);
