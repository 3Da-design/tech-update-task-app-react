# tech-update-task-app-react

技術更新に強い Web アプリ基盤の検討（第2章 — スタック比較）における **S2: React + Laravel API** 実験台です。  
設計を improved 固定（`TaskService` / `TaskRepository` 維持）としたまま、フロントを **React（Vite + TypeScript）SPA** に置き換え、S0（Blade）・S1（HTML/JS）と技術更新時の修正のしやすさを比較します。

---

## 目次

1. [研究における位置づけ](#研究における位置づけ)
2. [アーキテクチャ](#アーキテクチャ)
3. [技術スタック](#技術スタック)
4. [クイックスタート](#クイックスタート)
5. [テストと品質ゲート](#テストと品質ゲート)
6. [API エンドポイント](#api-エンドポイント)
7. [実験ワークフロー](#実験ワークフロー)
8. [ドキュメント索引](#ドキュメント索引)

---

## 研究における位置づけ

| 項目 | 内容 |
|------|------|
| 研究テーマ | 技術更新に強い Web アプリ基盤の検討 |
| 章 | **第2章** — スタック比較（設計は improved 固定） |
| スタック ID | **S2** — React + Laravel API |
| fork 元 | `tech-update-task-app`（improved / S0 Blade） |
| 比較対象 | S0（Blade 一体型）・S1（HTML/JS + API） |
| 第1章 legacy | 本リポジトリでは扱わない（参照のみ） |

研究全体の設計・制約は [`../EXPERIMENT-STACK.md`](../EXPERIMENT-STACK.md) を単一の参照元とします。本リポジトリ固有の実装方針・絶対制約は [`CLAUDE.md`](CLAUDE.md) を参照してください。

---

## アーキテクチャ

```text
Browser (React SPA — frontend/)
    │ axios（withCredentials + withXSRFToken）
    │ Sanctum Cookie 認証
    ▼
API\TaskController
    ▼
TaskService
    ▼
TaskRepository → Task (Model)
```

| レイヤ | クラス / 配置 |
|--------|--------------|
| フロント | `frontend/src/`（React + TypeScript, Vite） |
| API Controller | `App\Http\Controllers\API\TaskController` |
| Service | `App\Services\TaskService` |
| Repository | `App\Repositories\TaskRepository` |
| Interface | `App\Repositories\Contracts\TaskRepositoryInterface` |
| 認証 | Laravel Sanctum（SPA / Cookie 方式） |

S0 / S1 と同一の `TaskService` / `TaskRepository` を維持しており（Fat Controller 化していない）、Web 向け Blade タスクルートは削除済みです。詳細は [`docs/STACK-PROFILE.md`](docs/STACK-PROFILE.md) を参照してください。

---

## 技術スタック

| 区分 | 技術 |
|------|------|
| バックエンド | Laravel 13、PHP 8.4 |
| 認証 | Laravel Sanctum（SPA / Cookie） |
| DB | PostgreSQL（Docker Compose） |
| フロント | React 18、Vite、TypeScript、React Router |
| 品質 | PHPStan (Larastan)、Laravel Pint、ESLint |
| テスト | PHPUnit、Postman / Newman |
| 開発環境 | Docker Compose |

---

## クイックスタート

### 前提

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) など Compose v2 対応環境
- 開発フローは **Docker Compose のみ**（ホストで `php artisan serve` は使わない）
- **フロント（npm）は Docker の `node` サービスのみ**（ホストで `npm install` しない）

### ポート構成

| 項目 | 値 |
|------|-----|
| Web（Laravel/nginx） | `http://localhost:8004` |
| Vite dev（フロント） | `http://localhost:5175` |
| DB 公開ポート | `5436` |

### 初回セットアップ

```bash
cp .env.example .env

docker compose build app
docker compose up -d

docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

composer npm:docker-build
```

ブラウザで `http://localhost:8004` を開きます。シードユーザー: `test@example.com` / `password`

開発時（Vite dev サーバで HMR を有効にする場合）:

```bash
docker compose --profile node run --rm --service-ports node npm run dev
```

`http://localhost:5175` で React アプリが起動し、axios が `http://localhost:8004` の API を CORS + Cookie 付きで呼び出します。

### よく使うコマンド

```bash
docker compose logs -f
docker compose down              # DB ボリュームは残る
docker compose down -v           # DB ごと削除
```

### API の疎通確認

```bash
./scripts/curl-api-smoke.sh
```

---

## テストと品質ゲート

### ローカル一括（推奨）

```bash
./scripts/check-quality.sh
```

実行順: PHPStan → npm ci（Docker）→ ESLint → Vite build（`tsc --noEmit` 含む）→ PHPUnit → Newman

### 個別実行

```bash
docker compose exec app composer phpstan
docker compose exec app composer test
docker compose --profile node run --rm node npm run lint
docker compose --profile node run --rm node npm run build
npm run test:api:docker   # Newman（Docker ネットワーク経由）
```

---

## API エンドポイント

| メソッド | パス | 説明 |
|----------|------|------|
| GET | `/sanctum/csrf-cookie` | CSRF Cookie 発行 |
| POST | `/api/register` | ユーザー登録 |
| POST | `/api/login` | ログイン（Cookie セッション発行） |
| POST | `/api/logout` | ログアウト |
| GET | `/api/user` | 現在のログインユーザー取得 |
| GET | `/api/tasks` | 一覧（`title` 部分一致 / `status` 絞込 / `due_date_sort` 対応） |
| POST | `/api/tasks` | 作成（201） |
| PUT | `/api/tasks/{id}` | 更新 |
| DELETE | `/api/tasks/{id}` | 削除（204） |

詳細（クエリパラメータ・CSRF 再現の注意点等）は [`docs/STACK-PROFILE.md`](docs/STACK-PROFILE.md) を参照してください。

---

## 実験ワークフロー

```bash
composer experiment:metrics -- --phase baseline --diff-ref experiment-baseline-v1
composer experiment:metrics -- --phase after_update --diff-ref experiment-baseline-v1
composer experiment:metrics -- --phase after_fix --diff-ref experiment-baseline-v1
```

- 主指標: `git_app`（`after_fix` フェーズの変更ファイル数・行数）
- 比較基準: 第1章 S0 improved の同一シナリオ数値（[`../EXPERIMENT-STACK.md`](../EXPERIMENT-STACK.md) 第1章結果表）
- シナリオ変更は必ず `experiment-baseline-v1` から切った `exp/*` ブランチで実施し、`main` を汚染しない

---

## ドキュメント索引

| ドキュメント | 内容 |
|--------------|------|
| [`../EXPERIMENT-STACK.md`](../EXPERIMENT-STACK.md) | 研究全体（第1章・第2章）の単一参照元 |
| [`CLAUDE.md`](CLAUDE.md) | 本リポジトリ（S2）の実装方針・絶対制約 |
| [`docs/STACK-PROFILE.md`](docs/STACK-PROFILE.md) | S2 固有の構成（アーキテクチャ詳細・認証方式・ファイル構成） |
| [`docs/EXPERIMENT.md`](docs/EXPERIMENT.md) | 指標定義（参照） |

---

## ライセンス

MIT（Laravel プロジェクトスケルトンに準拠）
