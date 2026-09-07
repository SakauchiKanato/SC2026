-- Areaテーブル、特色タグテーブルの作成（PostgreSQL構文）
-- 地域の地理情報・特色を保存する独立したマスターデータ（所有者(user)なし）
--
-- 「特色」は以下の2種類を別カラム・別テーブルで持たせる設計にしている：
--   - description: 長文の自由記述（1エリアにつき1つ）
--   - feature_tags: 短いタグ（複数選択可能）。誰かが入力したタグは以後
--     全員がプルダウンから再利用できるよう、マスターテーブルとして独立させている
--
-- NOTE: ファイル名の連番はチームで採番ルールを決めた際に調整してください。
--       (login/signup用のマイグレーションが001であることを想定して002にしています)

CREATE TABLE IF NOT EXISTS areas (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,             -- 地域名
    prefecture VARCHAR(50) NOT NULL,        -- 都道府県
    city VARCHAR(100) NOT NULL,             -- 市区町村
    latitude DECIMAL(9,6) NULL,             -- 緯度
    longitude DECIMAL(9,6) NULL,            -- 経度
    description TEXT NOT NULL,              -- 特色の説明文（自由記述）
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    -- NOTE: PostgreSQLには「ON UPDATE CURRENT_TIMESTAMP」に相当する構文がないため、
    --       updated_atの更新はアプリケーション側（Area::update()）で明示的に行っている
);

CREATE INDEX IF NOT EXISTS idx_areas_prefecture_city ON areas (prefecture, city);

CREATE TABLE IF NOT EXISTS feature_tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,              -- 特色タグ名（例：温泉、学生の町）
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_feature_tags_name UNIQUE (name)
);

CREATE TABLE IF NOT EXISTS area_feature_tags (
    area_id INTEGER NOT NULL,
    feature_tag_id INTEGER NOT NULL,
    PRIMARY KEY (area_id, feature_tag_id),
    CONSTRAINT fk_area_feature_tags_area
        FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    CONSTRAINT fk_area_feature_tags_tag
        FOREIGN KEY (feature_tag_id) REFERENCES feature_tags(id) ON DELETE CASCADE
);
