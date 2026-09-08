-- ============================================================
-- 「企業・自治体が実現したいこと」を、地域(area)ごとの単一項目から
-- 複数団体が投稿できる掲示板形式に変更する
-- ============================================================
-- 背景：
-- これまで areas.challenges / areas.expected_future は、その地域を登録した
-- 企業・自治体（area.user_id）だけが1件だけ持てる項目だった。
-- 新しいUIでは、地域を登録していない別の企業・自治体も、その地域に対して
-- 独自の「課題点・問題点／期待する未来」を何件でも投稿できる掲示板形式に変更する。
--
-- 1. area_challenge_requests テーブルを新設する（1地域に対して複数団体・
--    複数件の投稿が可能）。
-- 2. 既存の areas.challenges / areas.expected_future の値を、
--    area_challenge_requests の1件目（投稿者=その地域の登録者 area.user_id）
--    として移行する。
-- 3. areas.challenges / areas.expected_future カラムは役目を終えたため削除する。
-- 4. ライフスタイルデータに「その他」欄を追加する（地域登録フォーム／
--    地域詳細編集画面の追加項目。地域登録フォームPDFには無いが、今回追加された
--    地域詳細編集画面PDFで「その他、地域の特色があれば記入してください」として登場）。
-- ============================================================

CREATE TABLE IF NOT EXISTS area_challenge_requests (
    id              SERIAL PRIMARY KEY,
    area_id         INTEGER NOT NULL REFERENCES areas(id) ON DELETE CASCADE,
    user_id         INTEGER NOT NULL REFERENCES sc2026_users(id) ON DELETE CASCADE,
    challenges      TEXT    NOT NULL,  -- 課題点・問題点
    expected_future TEXT    NOT NULL,  -- 期待する未来
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_area_challenge_requests_area_id ON area_challenge_requests(area_id);

DROP TRIGGER IF EXISTS set_updated_at_area_challenge_requests ON area_challenge_requests;
CREATE TRIGGER set_updated_at_area_challenge_requests
    BEFORE UPDATE ON area_challenge_requests
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- 既存データの移行（challenges/expected_futureの両方が入力済みの地域のみ。
-- 投稿者はその地域の登録者本人＝area.user_idとして扱う）
INSERT INTO area_challenge_requests (area_id, user_id, challenges, expected_future, created_at, updated_at)
SELECT id, user_id, challenges, expected_future, created_at, updated_at
FROM areas
WHERE challenges IS NOT NULL AND challenges <> ''
  AND expected_future IS NOT NULL AND expected_future <> '';

ALTER TABLE areas DROP COLUMN IF EXISTS challenges;
ALTER TABLE areas DROP COLUMN IF EXISTS expected_future;

ALTER TABLE areas ADD COLUMN IF NOT EXISTS other TEXT;
