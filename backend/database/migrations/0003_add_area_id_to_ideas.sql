-- ============================================================
-- ideas に area_id（areasへの外部キー）を追加する
-- ============================================================
-- 背景：0002でarea_id外部キーを廃止しarea_name（自由入力文字列）に一本化したが、
-- その結果「登録されていない地域名でもアイデアを登録できてしまう」不具合が発生した。
-- Area機能（登録済み地域の一覧・登録）が実装されたため、アイデアは実在するAreaに
-- 紐づける方式に戻す。
--
-- 既存データを壊さないよう非破壊的に追加する：
--   - area_id は当面NULL許容（既存行のarea_nameから自動でarea_idへ変換できないため）
--   - area_name 列はそのまま残す（新規の登録には使わないが、過去データの参照用に残す）
--   - 新規登録時にarea_idを必須にするのはアプリ側（IdeaController）でチェックする

ALTER TABLE ideas ADD COLUMN IF NOT EXISTS area_id INTEGER REFERENCES areas(id) ON DELETE CASCADE;
CREATE INDEX IF NOT EXISTS idx_ideas_area_id ON ideas(area_id);

-- user_id も同様の理由（ログイン機能が無い時期のNULL許容データが残っている可能性）で
-- DB制約はNULL許容のまま維持し、新規登録時の必須チェックはアプリ側（IdeaController /
-- AuthMiddleware::requireUserId()）で行う。
