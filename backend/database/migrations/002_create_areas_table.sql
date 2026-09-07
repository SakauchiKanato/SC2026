-- 特色タグ機能の追加（feature_tags / area_feature_tags）
--
-- NOTE: areasテーブル本体は、初期スキーマ（users/company_profiles/ideas/contacts等と
-- 一緒に作成されたもの）で既に定義済みのため、ここでは作成しない。
-- （当初このファイルでareasも作成する想定だったが、既に別のマイグレーションで
--  作成済みだったため、feature_tags関連のみに変更した）
--
-- areasの実際のカラム: id, user_id, name, features, latitude, longitude,
--                      address, created_at, updated_at
-- 「特色」は既存の features（自由記述・任意）に加えて、複数選択可能な短いタグを
-- feature_tags / area_feature_tags で追加できるようにしている。
-- 誰かが入力したタグは以後全員がプルダウンから再利用できる。

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
