-- ============================================================
-- 地域活性化アイデア共有アプリ 初期テーブル作成（PostgreSQL）
-- ============================================================

-- ユーザー（一般ユーザー／企業ユーザー共通のアカウント）
CREATE TABLE users (
    id            SERIAL PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          VARCHAR(20)   NOT NULL DEFAULT 'user'
                  CHECK (role IN ('user', 'company')), -- 'user'=発案者, 'company'=地域活性化を検討する企業
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 企業プロフィール（role='company' のユーザーに紐づく追加情報）
CREATE TABLE company_profiles (
    id            SERIAL PRIMARY KEY,
    user_id       INTEGER       NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    company_name  VARCHAR(255)  NOT NULL,
    description   TEXT,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 地域（Area）: 特色・地理情報を記録
CREATE TABLE areas (
    id            SERIAL PRIMARY KEY,
    user_id       INTEGER       NOT NULL REFERENCES users(id) ON DELETE CASCADE, -- 登録者
    name          VARCHAR(255)  NOT NULL,   -- 地域名
    features      TEXT,                     -- 特色
    latitude      DECIMAL(9,6),             -- 地理情報（緯度）
    longitude     DECIMAL(9,6),             -- 地理情報（経度）
    address       VARCHAR(255),             -- 住所など補足情報
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- アイデア: 概要・ビジネス化の方法・成功/失敗の記録
CREATE TABLE ideas (
    id             SERIAL PRIMARY KEY,
    area_id        INTEGER       NOT NULL REFERENCES areas(id) ON DELETE CASCADE,
    user_id        INTEGER       NOT NULL REFERENCES users(id) ON DELETE CASCADE, -- 発案者
    title          VARCHAR(255)  NOT NULL,  -- アイデアタイトル
    summary        TEXT          NOT NULL,  -- アイデア概要
    business_plan  TEXT,                    -- どうやってビジネスに繋げるか
    result_status  VARCHAR(20)   NOT NULL DEFAULT 'ongoing'
                   CHECK (result_status IN ('success', 'failure', 'ongoing')),
    result_note    TEXT,                    -- 成功／失敗した理由
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 企業からuserへのコンタクト（メール連絡の記録）
CREATE TABLE contacts (
    id               SERIAL PRIMARY KEY,
    company_user_id  INTEGER  NOT NULL REFERENCES users(id) ON DELETE CASCADE, -- 送信元（企業）
    target_user_id   INTEGER  NOT NULL REFERENCES users(id) ON DELETE CASCADE, -- 送信先（発案者）
    idea_id          INTEGER  REFERENCES ideas(id) ON DELETE SET NULL,          -- きっかけとなったアイデア
    message          TEXT     NOT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 検索・一覧表示でよく使うカラムにインデックスを付与
CREATE INDEX idx_areas_user_id      ON areas(user_id);
CREATE INDEX idx_ideas_area_id      ON ideas(area_id);
CREATE INDEX idx_ideas_user_id      ON ideas(user_id);
CREATE INDEX idx_contacts_target    ON contacts(target_user_id);
CREATE INDEX idx_contacts_company   ON contacts(company_user_id);

-- updated_at を自動更新するためのトリガー関数
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER set_updated_at_users
    BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER set_updated_at_company_profiles
    BEFORE UPDATE ON company_profiles
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER set_updated_at_areas
    BEFORE UPDATE ON areas
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER set_updated_at_ideas
    BEFORE UPDATE ON ideas
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
