-- Areaテーブルのfeaturesカラムをdescriptionにリネームし、
-- 特色タグ（feature_tags）関連テーブルを追加する
--
-- 背景: 「特色」を長文の自由記述(description)と、複数選択可能な短いタグ
-- (feature_tags)の2種類に分けることにしたため。
-- 002は既にコミット・適用済みのため直接編集せず、新しいマイグレーションとして
-- 変更を追加している。

ALTER TABLE areas
    CHANGE COLUMN features description TEXT NOT NULL COMMENT '特色の説明文（自由記述）';

CREATE TABLE IF NOT EXISTS feature_tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL COMMENT '特色タグ名（例：温泉、学生の町）',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_feature_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS area_feature_tags (
    area_id INT UNSIGNED NOT NULL,
    feature_tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (area_id, feature_tag_id),
    CONSTRAINT fk_area_feature_tags_area
        FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    CONSTRAINT fk_area_feature_tags_tag
        FOREIGN KEY (feature_tag_id) REFERENCES feature_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
