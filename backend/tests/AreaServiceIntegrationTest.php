<?php

/**
 * AreaService の統合テスト（実際のPostgreSQLに接続する）
 *
 * NOTE:
 * - backend/src/Core/Database.php はまだ存在しないため、
 *   backend/tests/support/TestDatabase.php で直接PDO接続している
 * - areasテーブルは他メンバー実装の初期スキーマで作成済み（user_id NOT NULL）。
 *   このテストは、テスト用のダミーユーザーを2件作成してそのIDを使う
 * - AreaService::create()/update() は内部で $pdo->beginTransaction() を呼ぶため、
 *   このテスト側で外側のトランザクションを張ることはできない（PDOはネストした
 *   トランザクションをサポートしていない）。そのため、ロールバックではなく
 *   最後に明示的な後片付け（テスト用ユーザーの削除）を行う。
 *   users -> areas -> area_feature_tags はON DELETE CASCADEで連動しているため、
 *   テスト用ユーザーを削除するだけでAreaと紐づけも一緒に消える。
 *   feature_tags（温泉、学生の町など）自体は「登録され続けるマスターデータ」
 *   という設計上、テストで作られたタグ名はそのまま残るが実害はない。
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

$existsStmt = $pdo->query("SELECT to_regclass('public.areas') IS NOT NULL AS table_exists");
$tableExists = $existsStmt->fetchColumn();

if (!$tableExists || $tableExists === 'f') {
    echo "areasテーブルが見つかりません。初期スキーマが適用されているか確認してください。\n";
    exit(1);
}

$testUserId = null;
$otherUserId = null;

try {
    // テスト用のダミーユーザーを作成（areas.user_idのFK制約を満たすため）
    // NOTE: usersテーブルの実際のカラムは id, uname, upass, email の4つのみ
    $userStmt = $pdo->prepare(
        "INSERT INTO users (uname, upass, email)
         VALUES (:uname, :upass, :email)
         RETURNING id"
    );
    $userStmt->execute([
        'uname' => '統合テスト用ユーザー',
        'upass' => 'dummy-hash',
        'email' => 'area-integration-test@example.com',
    ]);
    $testUserId = (int) $userStmt->fetchColumn();
    assertTrue($testUserId > 0, 'テスト用ユーザーを作成できる');

    $otherUserStmt = $pdo->prepare(
        "INSERT INTO users (uname, upass, email)
         VALUES (:uname, :upass, :email)
         RETURNING id"
    );
    $otherUserStmt->execute([
        'uname' => '別のユーザー',
        'upass' => 'dummy-hash',
        'email' => 'area-integration-test-other@example.com',
    ]);
    $otherUserId = (int) $otherUserStmt->fetchColumn();

    $service = new AreaService($pdo);

    echo "\n登録: タグ付きでAreaを新規作成\n";
    $created = $service->create($testUserId, [
        'name' => 'テスト地区',
        'features' => '統合テスト用の特色',
        'address' => 'テスト県テスト市1-1-1',
        'tags' => ['温泉', '自然が多い'],
    ]);
    assertTrue($created['id'] > 0, '作成されたAreaにIDが振られる（RETURNING idが機能している）');
    assertEquals($testUserId, (int) $created['user_id'], 'user_idが登録者として保存される');
    assertEquals(2, count($created['tags']), 'タグが2件紐づく');

    echo "\n登録: 既存タグを再利用し、新規タグは作らない\n";
    $tagCountBefore = (int) $pdo->query(
        "SELECT COUNT(*) FROM feature_tags WHERE name = '温泉'"
    )->fetchColumn();

    $created2 = $service->create($testUserId, [
        'name' => 'テスト地区2',
        'features' => '2件目の特色',
        'tags' => ['温泉', '学生の町'],
    ]);

    $tagCountAfter = (int) $pdo->query(
        "SELECT COUNT(*) FROM feature_tags WHERE name = '温泉'"
    )->fetchColumn();

    assertEquals($tagCountBefore, $tagCountAfter, '既存タグ「温泉」は新規作成されず再利用される（find-or-create）');

    echo "\n更新: 登録者本人による更新は成功する\n";
    $updated = $service->update($created['id'], $testUserId, [
        'name' => 'テスト地区',
        'features' => '更新後の特色',
        'tags' => ['学生の町'],
    ]);
    $updatedTagNames = array_column($updated['tags'], 'name');
    assertEquals(['学生の町'], $updatedTagNames, 'attachTags()によりタグが指定通りに入れ替わる');

    echo "\n異常系: 他人のAreaは更新できない（403相当）\n";
    $forbidden = false;
    try {
        $service->update($created['id'], $otherUserId, ['name' => '乗っ取り']);
    } catch (\RuntimeException $e) {
        $forbidden = ($e->getCode() === 403);
    }
    assertTrue($forbidden, '登録者以外が更新しようとすると403相当の例外になる');

    echo "\n異常系: 他人のAreaは削除できない（403相当）\n";
    $forbiddenDelete = false;
    try {
        $service->delete($created['id'], $otherUserId);
    } catch (\RuntimeException $e) {
        $forbiddenDelete = ($e->getCode() === 403);
    }
    assertTrue($forbiddenDelete, '登録者以外が削除しようとすると403相当の例外になる');

    echo "\n削除: 登録者本人による削除は成功し、中間テーブルの紐づけもCASCADEで消える\n";
    $service->delete($created2['id'], $testUserId);

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
        $service->create($testUserId, ['name' => '']);
        assertTrue(false, '例外が投げられるべき');
    } catch (AreaValidationException $e) {
        $countAfter = (int) $pdo->query('SELECT COUNT(*) FROM areas')->fetchColumn();
        assertEquals($countBefore, $countAfter, 'バリデーションエラー時はレコードが増えない');
    }
} finally {
    // テスト用ユーザーを削除する。ON DELETE CASCADEにより、
    // そのユーザーが作成したAreaと中間テーブルの紐づけも一緒に消える。
    // feature_tags自体（温泉、学生の町など）は育っていくマスターデータのため残すが、実害はない。
    if ($testUserId !== null) {
        $pdo->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $testUserId]);
    }
    if ($otherUserId !== null) {
        $pdo->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $otherUserId]);
    }
}

echo "\n--------------------------------\n";
echo "結果: {$passCount} PASS / {$failureCount} FAIL\n";
echo "(注: テスト用ユーザー・Areaは後片付け済みです。feature_tagsのみ残ります)\n";

exit($failureCount > 0 ? 1 : 0);
