-- ============================================================
-- アイデアの評価を「発案者の自己申告」から「企業・自治体による評価」へ変更
-- ============================================================
-- 背景：
-- これまではアイデア登録時に発案者自身が status（達成/未達成）・reason（理由）
-- を入力する仕様だったが、これを廃止し、登録時点では status/reason は
-- 未入力（NULL）とする。かわりに、そのアイデアが紐づく地域（area）を
-- 所有する企業・自治体アカウントが、後から達成/未達成を評価する形にする。
--
-- 1. ideas.status / ideas.reason を登録時必須(NOT NULL)から任意(NULLABLE)に変更する。
--    ※ status の CHECK (status IN ('success','failure')) 制約はそのまま残す。
--      PostgreSQLのCHECK制約はNULL値に対しては常に真と評価されるため、
--      NOT NULLを外すだけでNULLを許容できる（制約定義自体の変更は不要）。
-- 2. どの企業・自治体アカウントが、いつ評価したかを記録するための列を追加する。
--    evaluated_by: 評価した企業・自治体アカウントのsc2026_users.id
--    evaluated_at: 評価日時
-- ============================================================

ALTER TABLE ideas ALTER COLUMN status DROP NOT NULL;
ALTER TABLE ideas ALTER COLUMN reason DROP NOT NULL;

ALTER TABLE ideas ADD COLUMN IF NOT EXISTS evaluated_by INTEGER REFERENCES sc2026_users(id) ON DELETE SET NULL;
ALTER TABLE ideas ADD COLUMN IF NOT EXISTS evaluated_at TIMESTAMP;
