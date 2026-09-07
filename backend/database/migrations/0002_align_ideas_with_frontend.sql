-- ============================================================
-- ideas テーブルを frontend のAPI仕様に合わせて変更する
-- ============================================================
-- 背景：0001で作ったideasテーブルはarea_id(外部キー)/summary/result_status/
-- result_noteという設計だったが、既にfrontend側で実装されているIdea入力・
-- 閲覧画面はarea_name(文字列)/content/status/reasonという形でAPIを呼び出す
-- ようになっていた。手戻りを減らすため、backend側をfrontendの仕様に合わせる。

-- area_id（Areaテーブルへの外部キー）をやめ、frontendが送ってくる
-- area_name（地域名の自由入力文字列）をそのまま保存する方式に変更する。
-- TODO: 将来的にAreaマスタ（areasテーブル）との紐付けが必要になったら、
--       area_nameからareas.nameを検索してarea_idを引く形への変更を検討する。
ALTER TABLE ideas DROP CONSTRAINT IF EXISTS ideas_area_id_fkey;
DROP INDEX IF EXISTS idx_ideas_area_id;
ALTER TABLE ideas DROP COLUMN IF EXISTS area_id;
ALTER TABLE ideas ADD COLUMN area_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE ideas ALTER COLUMN area_name DROP DEFAULT;
CREATE INDEX idx_ideas_area_name ON ideas(area_name);

-- summary → content にリネーム（frontendのフィールド名に合わせる）
ALTER TABLE ideas RENAME COLUMN summary TO content;

-- result_status → status にリネームし、値をsuccess/failureの2値に統一する
-- （frontendのIdea入力フォームは「成功」「失敗」の2択のみのため、ongoingは廃止）
ALTER TABLE ideas DROP CONSTRAINT IF EXISTS ideas_result_status_check;
ALTER TABLE ideas RENAME COLUMN result_status TO status;
ALTER TABLE ideas ALTER COLUMN status DROP DEFAULT;
ALTER TABLE ideas ADD CONSTRAINT ideas_status_check CHECK (status IN ('success', 'failure'));

-- result_note → reason にリネームし、必須項目にする
-- （frontendのフォームでは成功/失敗の理由の入力を必須にしているため）
ALTER TABLE ideas RENAME COLUMN result_note TO reason;
ALTER TABLE ideas ALTER COLUMN reason SET NOT NULL;

-- business_plan（どうやってビジネスに繋げるか）は現在のfrontendフォームに
-- 入力欄が無いため一旦削除する。
-- TODO: 企業向けの事業化提案の入力欄は将来追加を検討する（アプリ概要には記載あり）。
ALTER TABLE ideas DROP COLUMN IF EXISTS business_plan;

-- user_id: ログイン機能が未実装で、frontendからもuser_idは送られてこないため
-- 当面NULLを許容する。
-- TODO(SECURITY): ログイン機能実装後、認証トークン（セッション等）から取得した
--                 user_idを必須（NOT NULL）に戻すこと。
ALTER TABLE ideas ALTER COLUMN user_id DROP NOT NULL;
