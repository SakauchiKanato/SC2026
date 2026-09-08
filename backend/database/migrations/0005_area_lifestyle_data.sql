-- ============================================================
-- 地域登録フォームの「ライフスタイルデータ」セクション追加
-- ============================================================
-- これまで地域詳細ページの「ライフスタイルデータ」は実データの持ち先が無く、
-- 「データは準備中です」という固定文言のプレースホルダー表示だった。
-- また地域登録フォーム側では「ライフスタイルデータ」という入力欄が
-- コピー＆ペースト由来のミスで「住所」欄とid/v-modelを共有してしまっており、
-- 入力内容が住所欄と衝突する不具合があった（QA報告書 2026-09-08 参照）。
--
-- 今回、企画側の「地域登録フォーム」PDFに合わせて、ライフスタイルデータを
-- 次の項目を持つ任意入力の情報として正式に実装する:
--   人口・昼夜人口比率・平均年齢・主要産業・交通アクセス
-- （住所は既存のaddress列をそのまま「ライフスタイルデータ」セクションの
--  1項目として引き続き使う。新しい列は追加しない）
--
-- すべて「分かる範囲で構いません」（PDF記載）の任意項目のため、
-- 数値の四則演算等は行わずフリーテキスト（VARCHAR）として保持する
-- （例:「約22.6万人」「約230%」のような概算表記を許容するため）。
-- ============================================================

ALTER TABLE areas ADD COLUMN IF NOT EXISTS population VARCHAR(255);
ALTER TABLE areas ADD COLUMN IF NOT EXISTS day_night_population_ratio VARCHAR(255);
ALTER TABLE areas ADD COLUMN IF NOT EXISTS average_age VARCHAR(255);
ALTER TABLE areas ADD COLUMN IF NOT EXISTS main_industry VARCHAR(255);
ALTER TABLE areas ADD COLUMN IF NOT EXISTS transit_access VARCHAR(255);
