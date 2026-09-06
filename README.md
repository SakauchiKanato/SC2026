# 地域活性化アイデア共有アプリ

## アプリ概要
地域（Area）の特性・地理情報を記録し、成功／失敗したアイデアとその理由を蓄積することで、
似た土地で地域活性化を目指す発案者の参考（道しるべ）になることを目的としたアプリ。
また、地域活性化を検討する企業がアイデア発案者（user）に直接メールでコンタクトし、
協業につなげる機能も想定している。

## 技術スタック
- Backend: 生PHP（フレームワーク・Composerなし） / API サーバー
- Frontend: Vue.js（Vite） / SPA

`backend` と `frontend` は別プロジェクトとしてディレクトリを分離している。

## ディレクトリ構成

```
backend/    生PHP（フレームワークなし、Composerも未使用）
  public/
    index.php     … エントリーポイント（フロントコントローラー / 簡易ルーター）
  src/
    Controllers/
      Auth/      … login, signup
      Area/      … Area登録（特色・地理情報）
      Idea/      … アイデア入力・アイデア閲覧
      Company/   … 企業からuserへのコンタクト機能
    Models/       … User, Area, Idea, Company などのデータ操作クラス
    Services/
      Area/ Idea/ Company/           … 各機能のビジネスロジック
    Core/         … DB接続・共通処理など（require/includeで読み込む）
  config/
    database.php  … DB接続設定など
  database/
    migrations/   … テーブル作成用SQL
    seeds/        … 初期データ投入用SQL
  storage/
    logs/
  tests/

frontend/   Vue.js（Vite, SPA）
  src/
    views/
      Auth/      … ログイン・サインアップ画面
      Area/      … Area登録画面
      Idea/      … アイデア入力・一覧・詳細画面
      Company/   … 企業向け画面（気になるuserへの連絡など）
    components/
      Auth/ Area/ Idea/ Company/     … 各画面で使う部品
    router/       … 画面遷移の定義
    store/        … 状態管理（Pinia等を想定）
    api/          … PHP APIを呼び出すクライアント
    assets/
  public/
```

## 今日のToDoとの対応
- login, signup → `backend/src/Controllers/Auth`, `frontend/src/views/Auth`
- Area登録（特色・地理情報） → `backend/src/.../Area`, `frontend/.../Area`
- アイデア入力フォーム → `backend/src/.../Idea`, `frontend/.../Idea`
- アイデア閲覧フォーム → `backend/src/.../Idea`, `frontend/.../Idea`
- （将来）企業からuserへのメール連絡機能 → `backend/src/.../Company`, `frontend/.../Company`

※ 各ディレクトリはまだ空（.gitkeep のみ）。中身の実装はこれから進める。
※ backendはComposerを使わない生PHPのため、クラスは `require_once` 等で読み込む想定。
