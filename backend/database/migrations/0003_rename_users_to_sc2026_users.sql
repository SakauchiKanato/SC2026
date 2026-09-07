-- ============================================================
-- users テーブル名の衝突を解消する
-- ============================================================
-- 背景：本番DB（アカウント knt416 のPostgreSQL）には、このアプリとは無関係な
-- 別プロジェクトの users テーブルが既に存在していた（列: username, campus,
-- faculty, circle, avatar_url 等。friendships/matches/timetables等から参照）。
--
-- そのため 0001_create_tables.sql の CREATE TABLE users は実行時にエラーになり
-- （psql -f はデフォルトでエラーが出ても後続の文を実行し続けるため）、
-- company_profiles/areas/ideas/contacts はこのアプリ用に正しく作成された一方、
-- それらの外部キーは誤って「別プロジェクトのusersテーブル」を参照する形に
-- なってしまっていた。これがサインアップ時の500エラー
-- （name/role列が存在しないテーブルへのINSERT失敗）の原因だった。
--
-- 対応：このアプリ専用のテーブル名 sc2026_users を新規作成し、
-- 各テーブルの外部キーをそちらに向け直す。
-- 実行前に areas/ideas が0件であることを確認済み（本番データへの影響なし）。
-- 既存の（別プロジェクトの）usersテーブルは一切変更・削除しない。
-- ============================================================

CREATE TABLE IF NOT EXISTS sc2026_users (
    id            SERIAL PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          VARCHAR(20)   NOT NULL DEFAULT 'user'
                  CHECK (role IN ('user', 'company')),
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TRIGGER IF EXISTS set_updated_at_sc2026_users ON sc2026_users;
CREATE TRIGGER set_updated_at_sc2026_users
    BEFORE UPDATE ON sc2026_users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- company_profiles.user_id
ALTER TABLE company_profiles DROP CONSTRAINT IF EXISTS company_profiles_user_id_fkey;
ALTER TABLE company_profiles
    ADD CONSTRAINT company_profiles_user_id_fkey
    FOREIGN KEY (user_id) REFERENCES sc2026_users(id) ON DELETE CASCADE;

-- areas.user_id
ALTER TABLE areas DROP CONSTRAINT IF EXISTS areas_user_id_fkey;
ALTER TABLE areas
    ADD CONSTRAINT areas_user_id_fkey
    FOREIGN KEY (user_id) REFERENCES sc2026_users(id) ON DELETE CASCADE;

-- ideas.user_id（0002でNOT NULL制約は外したが、外部キー自体は残っている）
ALTER TABLE ideas DROP CONSTRAINT IF EXISTS ideas_user_id_fkey;
ALTER TABLE ideas
    ADD CONSTRAINT ideas_user_id_fkey
    FOREIGN KEY (user_id) REFERENCES sc2026_users(id) ON DELETE CASCADE;

-- contacts.company_user_id / target_user_id
ALTER TABLE contacts DROP CONSTRAINT IF EXISTS contacts_company_user_id_fkey;
ALTER TABLE contacts
    ADD CONSTRAINT contacts_company_user_id_fkey
    FOREIGN KEY (company_user_id) REFERENCES sc2026_users(id) ON DELETE CASCADE;

ALTER TABLE contacts DROP CONSTRAINT IF EXISTS contacts_target_user_id_fkey;
ALTER TABLE contacts
    ADD CONSTRAINT contacts_target_user_id_fkey
    FOREIGN KEY (target_user_id) REFERENCES sc2026_users(id) ON DELETE CASCADE;
