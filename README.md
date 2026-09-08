# 🏘️ 地域活性化アイデア共有アプリ

地域の特徴・地理情報と、地域活性化に関するアイデアを蓄積・共有するWebアプリケーションです。

成功したアイデアだけでなく、**失敗した理由や実施結果**も記録することで、似た地域で地域活性化を考える人にとっての「道しるべ」となることを目指します。

また、将来的には地域活性化を検討している企業が、アイデアの発案者（user）に直接コンタクトし、協業につなげられる機能も予定しています。

本番環境へのデプロイ手順は [DEPLOY.md](./DEPLOY.md) を参照してください。

---

## 📌 アプリ概要

### 🎯 目的

地域活性化に関するアイデアや実施結果をデータとして蓄積し、

- どのような地域で
- どのようなアイデアを実施し
- 成功したのか、失敗したのか
- なぜ成功／失敗したのか

を共有できるようにします。

これにより、別の地域で地域活性化を検討する際に、過去の事例を参考にできるようにします。

### 👥 想定ユーザー

| ユーザー | 主な用途 |
|---|---|
| 👤 発案者（user） | 地域やアイデアを登録・共有 |
| 🏢 企業（company） | 地域活性化アイデアを検索・発案者へコンタクト |

---

# 🛠️ 技術スタック

## Backend

- 生PHP
- フレームワークなし
- Composerなし
- REST API

## Frontend

- Vue.js
- Vite
- SPA
- vue-router
- Composition API

## Database / Authentication

- MySQL
- JWT
- bcrypt（`password_hash()`）

---

# 📁 プロジェクト構成

BackendとFrontendは、それぞれ独立したプロジェクトとして分離しています。

```text
SC2026/
│
├── backend/
│   ├── public/
│   │   └── index.php
│   │
│   ├── src/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── login
│   │   │   │   ├── signup
│   │   │   │   └── me
│   │   │   │
│   │   │   ├── Area/
│   │   │   ├── Idea/
│   │   │   └── Company/
│   │   │
│   │   ├── Models/
│   │   │   ├── User
│   │   │   ├── Area
│   │   │   ├── Idea
│   │   │   └── Company
│   │   │
│   │   ├── Services/
│   │   │   ├── Area/
│   │   │   ├── Idea/
│   │   │   └── Company/
│   │   │
│   │   └── Core/
│   │       ├── DB
│   │       ├── JWT
│   │       └── Authentication
│   │
│   ├── config/
│   │   ├── database.php
│   │   ├── database.local.php
│   │   ├── database.local.php.example
│   │   ├── jwt.php
│   │   ├── jwt.local.php
│   │   └── jwt.local.php.example
│   │
│   ├── database/
│   │   ├── migrations/
│   │   └── seeds/
│   │
│   ├── storage/
│   │   └── logs/
│   │
│   └── tests/
│       ├── TestHelper.php
│       └── run_tests.php
│
└── frontend/
    ├── src/
    │   ├── views/
    │   │   ├── Auth/
    │   │   ├── Area/
    │   │   ├── Idea/
    │   │   └── Company/
    │   │
    │   ├── components/
    │   │   ├── Auth/
    │   │   ├── Area/
    │   │   ├── Idea/
    │   │   └── Company/
    │   │
    │   ├── router/
    │   ├── store/
    │   ├── api/
    │   ├── assets/
    │   └── ...
    │
    ├── public/
    ├── .env
    └── .env.example
