# デプロイ手順（本番環境）

本番URL: https://gms.gdl.jp/~knt416/SC2026/

このアプリは大学・研究室の共用サーバー（Apache + PHP + PostgreSQL）上で動いています。
CIやSSHでの自動デプロイは無く、**手元でビルドしたファイルをサーバーへ手動でアップロードする**運用です。
この手順書は、その手動デプロイを毎回同じ手順・同じ抜け漏れなく行うためのチェックリストです。

---

## 0. 全体の流れ

1. `main` ブランチを最新化する
2. （DBスキーマの変更があれば）マイグレーションを本番PostgreSQLに適用する
3. フロントエンドを本番用の設定でビルドする
4. バックエンド一式・フロントエンドのビルド成果物をサーバーへアップロードする
5. サーバー側の設定ファイル・隠しファイルが揃っているか確認する
6. 本番サイトで動作確認する

---

## 1. 事前準備

```bash
git checkout main
git pull origin main
```

デプロイ対象のブランチ・コミットに漏れがないか、`git log` で確認してください。

---

## 2. DBマイグレーションの適用

- マイグレーションSQLは `backend/database/migrations/` にあります。
- **このプロジェクトには「どこまで適用済みか」を記録する仕組み（migrationsテーブル等）がありません。** 前回のデプロイ以降に追加された、まだ本番に当てていないファイルだけを選んで実行してください（何を適用済みか分からなくなった場合は、対象のテーブル・カラムが既に存在するかを本番DBで直接確認するのが確実です）。
- 実行順はファイル名の若い順（`0001_...` → `0002_...` → …）です。`002_create_areas_table.sql` だけ命名の付け方が違いますが、`areas` テーブルさえ作成済みであれば（＝`0001_create_tables.sql` 適用後であれば）どのタイミングで実行しても問題ありません。
- 実行方法は普段お使いのツール（`psql -f <ファイル>` や pgAdmin など）で構いません。
- 一部のファイル（`0002`〜`0004`）は `ALTER TABLE ... ADD COLUMN` に `IF NOT EXISTS` を付けていないものがあるため、**同じファイルを2回実行するとエラーになります**。適用済みのものを誤って再実行しないよう注意してください（`0005`・`0006` は `IF NOT EXISTS` 付きなので再実行しても安全です）。

2026年9月8日時点のファイル一覧（参考。最新は必ず `backend/database/migrations/` を確認してください）:

| ファイル | 内容 |
|---|---|
| `0001_create_tables.sql` | 初期テーブル一式（users, company_profiles, areas, ideas, contacts） |
| `002_create_areas_table.sql` | 特色タグ機能（feature_tags, area_feature_tags） |
| `0002_align_ideas_with_frontend.sql` | ideasテーブルをフロントのAPI仕様に合わせて調整 |
| `0003_rename_users_to_sc2026_users.sql` | usersテーブル名の衝突解消（sc2026_usersへリネーム） |
| `0004_area_challenges_and_idea_area_link.sql` | areasに課題点・期待する未来を追加、ideasをarea_idで再度紐付け |
| `0005_area_lifestyle_data.sql` | areasにライフスタイルデータ（人口・昼夜人口比率など）を追加 |
| `0006_idea_company_evaluation.sql` | ideasのstatus/reasonを任意化し、企業・自治体による評価用の列を追加 |

---

## 3. フロントエンドのビルド

**必ず `frontend/` ディレクトリに移動してから**実行してください（リポジトリのルートで `npm run build` すると `package.json` が見つからずエラーになります）。

サブパス配信（`/~knt416/SC2026/` 配下での公開）に対応させるため、`--base` とAPIのベースURLを明示的に指定します。

```bash
cd frontend
npm install   # package.jsonに変更がある場合のみ
VITE_API_BASE_URL=/~knt416/SC2026/backend/public/index.php \
  npm run build -- --base=/~knt416/SC2026/
```

成果物は `frontend/dist/` に生成されます。この中身（`index.html`, `assets/`, `.htaccess` を含む）をそのままアップロードします。

---

## 4. サーバーへのアップロード

普段お使いのFTP/SFTPクライアントやファイルマネージャーで、以下のように配置してください。

- `frontend/dist/` の中身 → `https://gms.gdl.jp/~knt416/SC2026/` 直下
- `backend/` フォルダ一式 → `https://gms.gdl.jp/~knt416/SC2026/backend/`
  - ただし `backend/config/database.local.php` と `backend/config/jwt.local.php` は `.gitignore` 対象（本番の接続情報・シークレットを含むため）なので、**Gitの変更に含まれません**。サーバー上に既にある設定ファイルを上書きしないよう注意してください（初回セットアップ時や、DB接続情報・JWTシークレットを変更したいときだけ、手動で用意・更新します）。

**⚠️ 注意：隠しファイル（`.htaccess`）を忘れずにアップロードすること**

`frontend/dist/.htaccess`（SPAのリロード・直接URLアクセス対策）と `backend/public/.htaccess`（`AcceptPathInfo` 設定）は、ファイル名が`.`から始まる隠しファイルです。FTPクライアントによっては初期設定で表示されず、アップロードし忘れる事故が過去に実際に発生しています。アップロード前に「隠しファイルを表示する」設定を必ず確認してください。

---

## 5. サーバー側の設定確認（チェックリスト）

- [ ] `backend/config/database.local.php` が存在し、本番DBの接続情報（host / port / dbname / user / password）が正しいか
- [ ] `backend/config/jwt.local.php` が存在し、`secret` が設定されているか（未設定だとAPIが例外を返し全滅する）
- [ ] `frontend/dist/.htaccess` がアップロードされているか（直接URLアクセス・リロード時の404対策）
- [ ] `backend/public/.htaccess` がアップロードされているか（`AcceptPathInfo On`）
- [ ] 上記2つの`.htaccess`が効いているか（大学サーバー側で`AllowOverride`が制限されていると、ファイルをアップロードしても無効化されることがあります。過去に実際に発生しており、管理者へ`AllowOverride FileInfo`等の確認を依頼する必要があった事例があります）

---

## 6. 動作確認

1. ヘルスチェック：`https://gms.gdl.jp/~knt416/SC2026/backend/public/index.php/health` が `{"status":"ok"}` を返すか
2. トップページが表示されるか（`https://gms.gdl.jp/~knt416/SC2026/`）
3. **`/areas` や `/login/proposer` など、トップページ以外のURLに直接アクセス・リロードしても404にならないか**（.htaccessが効いているかの実地確認）
4. 発案者ログイン→アイデア登録、企業・自治体ログイン→アイデア評価、など主要な画面遷移が一通り動くか

---

## 参考：既知の注意点

- フロントエンドを `frontend/` 以外のディレクトリでビルドすると `Could not read package.json` エラーになる（`chore/add-deploy-script` ブランチに、どこから実行しても自動で `frontend/` に移動してビルドする `deploy-frontend.sh` スクリプトあり。未マージのため使う場合は先にmainへ取り込むこと）。
- デプロイ先のパス（`/~knt416/SC2026/`）を変更する場合は、`frontend/public/.htaccess` 内のハードコードされたパスと、ビルド時の `--base` / `VITE_API_BASE_URL` の両方を合わせて変更する必要がある。
- 本番サーバーへの直接ログイン（SSH等）の手段が無いため、`.htaccess`が効いているかどうかの切り分けは実際にURLへアクセスして確認する以外に方法が無い。
