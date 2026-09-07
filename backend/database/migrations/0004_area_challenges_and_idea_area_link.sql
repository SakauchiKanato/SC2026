-- ============================================================
-- 「街タネ」UIリニューアルに伴うスキーマ拡張
-- ============================================================
-- 1. areasに「課題点・問題点」「期待する未来」を追加する
--    （地域登録フォームの新しい入力項目。既存のfeatures/latitude/longitude/
--     特色タグ機能は引き続きDBには残すが、新UIの画面には表示しない）
-- 2. ideasをarea_id（外部キー）でareasに紐付け直す
--    （0002でarea_name自由入力に変更していたが、新UIでは発案者が
--     「特定の地域ページ」からアイデア登録に進む導線になるため、
--     再びareasテーブルとの正式な紐付けに戻す）
--
-- 本番のideasは0件であることを確認済みのため、area_name列の削除による
-- データ損失は無い。念のためNOT NULL制約は付けず、必須チェックは
-- アプリケーション側（IdeaController/AreaController）で行う。
-- ============================================================

ALTER TABLE areas ADD COLUMN IF NOT EXISTS challenges TEXT;
ALTER TABLE areas ADD COLUMN IF NOT EXISTS expected_future TEXT;

ALTER TABLE ideas ADD COLUMN IF NOT EXISTS area_id INTEGER REFERENCES areas(id) ON DELETE CASCADE;
CREATE INDEX IF NOT EXISTS idx_ideas_area_id ON ideas(area_id);

DROP INDEX IF EXISTS idx_ideas_area_name;
ALTER TABLE ideas DROP COLUMN IF EXISTS area_name;
