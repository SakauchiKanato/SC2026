-- Areaテーブル作成
-- 地域の特色・地理情報を保存する独立したマスターデータ（所有者(user)なし）
--
-- NOTE: ファイル名の連番はチームで採番ルールを決めた際に調整してください。
--       (login/signup用のマイグレーションが001であることを想定して002にしています)

CREATE TABLE IF NOT EXISTS areas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT '地域名',
    prefecture VARCHAR(50) NOT NULL COMMENT '都道府県',
    city VARCHAR(100) NOT NULL COMMENT '市区町村',
    latitude DECIMAL(9,6) NULL COMMENT '緯度',
    longitude DECIMAL(9,6) NULL COMMENT '経度',
    features TEXT NOT NULL COMMENT '特色（自由記述）',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_prefecture_city (prefecture, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
