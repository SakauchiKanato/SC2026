<?php

/**
 * AreaService の統合テスト（実際のPostgreSQLに接続する）
 *
 * NOTE:
 * - backend/src/Core/Database.php はまだ存在しないため、
 *   backend/tests/support/TestDatabase.php で直接PDO接続している
 *   （Core/Database.php完成後は、このテストをDatabase::getConnection()経由に
 *   置き換えて問題ない。AreaService/Area/FeatureTagはPDOを注入できる作りにしてある）
 * - 実行前に、backend/database/migrations/002_create_areas_table.sql を
 *   対象のPostgreSQLに一度適用しておくこと
 * - このテストは全ての変更を1つのトランザクション内で行い、最後に必ずロールバックする。
 *   実行してもDBにデータは残らない
 *
 * 実行方法:
 *   php backend/tests/AreaServiceIntegrationTest.php
 */

require_once __DIR__ . '/support/TestDatabase.php';
require_once __DIR__ . '/../src/Services/Area/AreaService.php';

$failureCount = 0;
$passCount = 0;

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

$pdo = TestDatabase::connect();

// areasテーブルが存在するか事前確認（マイグレーション未実行なら案内して終了する）
$existsStmt = $pdo->query("SELECT to_regclass('public.areas') IS NOT NULL AS table_exists");
$tableExists = $existsStmt->fetchColumn();

if (!$tableExists || $tableExists === 'f') {
    echo "areasテーブルが見つかりません。先に以下を実行してください:\n";
    echo "  psql -h <host> -U <user> -d <dbname> -f backend/database/migrations/002_create_areas_table.sql\n";
    exit(1);
}

// 全ての変更をロールバックするため、テスト全体を1つのトランザクションで囲む
$pdo->beginTransaction();

try {
    $service = new AreaService($pdo);

    echo "登録: タグ付きでAreaを新規作成\n";
    $created = $service->create([
        'name' => 'テスト地区',
        'prefecture' => 'テスト県',
        'city' => 'テスト市',
        'description' => '統合テスト用の説明文',
        'tags' => ['温泉', '自然が多い'],
    ]);
    assertTrue($created['id'] > 0, '作成されたAreaにIDが振られる（RETURNING idが機能している）');
    assertEquals('テスト地区', $created['name'], 'nameが保存される');
    assertEquals(2, count($created['tags']), 'タグが2件紐づく');

    echo "\n登録: 既存タグを再利用し、新規タグは作らない\n";
    $tagCountBefore = (int) $pdo->query(
        "SELECT COUNT(*) FROM feature_tags WHERE name = '温泉'"
    )->fetchColumn();

    $created2 = $service->create([
        'name' => 'テスト地区2',
        'prefecture' => 'テスト県',
        'city' => 'テスト市2',
        'description' => '2件目の説明文',
        'tags' => ['温泉', '学生の町'],
    ]);

    $tagCountAfter = (int) $pdo->query(
        "SELECT COUNT(*) FROM feature_tags WHERE name = '温泉'"
    )->fetchColumn();

    assertEquals($tagCountBefore, $tagCountAfter, '既存タグ「温泉」は新規作成されず再利用される（find-or-create）');

    echo "\n更新: タグの入れ替え\n";
    $updated = $service->update($created['id'], [
        'name' => 'テスト地区',
        'prefecture' => 'テスト県',
        'city' => 'テスト市',
        'description' => '更新後の説明文',
        'tags' => ['学生の町'], // 温泉・自然が多い を外し、学生の町だけにする
    ]);
    $updatedTagNames = array_column($updated['tags'], 'name');
    assertEquals(['学生の町'], $updatedTagNames, 'attachTags()によりタグが指定通りに入れ替わる');

    echo "\n削除: Areaを削除すると中間テーブルの紐づけもCASCADEで消える\n";
    $service->delete($created2['id']);

    $isDeleted = false;
    try {
        $service->getById($created2['id']);
    } catch (\RuntimeException $e) {
        $isDeleted = true;
    }
    assertTrue($isDeleted, '削除後に取得しようとすると404相当の例外になる');

    $pivotStmt = $pdo->prepare('SELECT COUNT(*) FROM area_feature_tags WHERE area_id = :id');
    $pivotStmt->execute(['id' => $created2['id']]);
    assertEquals(0, (int) $pivotStmt->fetchColumn(), '削除されたAreaの中間テーブルの紐づけも消える(ON DELETE CASCADE)');

    echo "\n異常系: バリデーションエラー時はDBに書き込まれない\n";
    $countBefore = (int) $pdo->query('SELECT COUNT(*) FROM areas')->fetchColumn();
    try {
        $service->create([
            'name' => '',
            'prefecture' => 'テスト県',
            'city' => 'テスト市',
            'description' => '',
        ]);
        assertTrue(false, '例外が投げられるべき');
    } catch (AreaValidationException $e) {
        $countAfter = (int) $pdo->query('SELECT COUNT(*) FROM areas')->fetchColumn();
        assertEquals($countBefore, $countAfter, 'バリデーションエラー時はレコードが増えない');
    }
} finally {
    // テストで作成したデータは全てロールバックし、DBには何も残さない
    $pdo->rollBack();
}

echo "\n--------------------------------\n";
echo "結果: {$passCount} PASS / {$failureCount} FAIL\n";
echo "(注: 全ての変更はロールバック済みです。DBにテストデータは残っていません)\n";

exit($failureCount > 0 ? 1 : 0);
