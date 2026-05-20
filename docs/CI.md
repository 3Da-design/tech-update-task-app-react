# CI（継続的インテグレーション）

本プロジェクトの CI は **GitHub Actions** で動作します。定義ファイルは [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) です。

テスト各ツールの詳細（PHPStan の設定、Postman の手順など）は [TESTING.md](./TESTING.md) を参照してください。

---

## 概要

| 項目 | 内容 |
|------|------|
| プラットフォーム | GitHub Actions |
| ワークフロー名 | `CI` |
| 実行環境 | `ubuntu-latest` |
| PHP | 8.4 |
| Node.js | 22 |
| テスト DB | SQLite（メモリ）— CI では PostgreSQL 不要 |

`main` / `master` への push と、それらを対象とする pull request で自動実行されます。

---

## ジョブ構成

4 つのジョブが **並列** に実行されます。いずれかが失敗するとワークフロー全体が失敗します。

```text
push / pull_request
        │
        ├─► php-tests    … PHPUnit（フロントビルド込み）
        ├─► php-quality  … Pint + PHPStan
        ├─► frontend     … ESLint + Vite build
        └─► api-tests    … Postman コレクション（Newman）
```

### 1. PHP Tests（`php-tests`）

アプリケーションの自動テストです。

| ステップ | 内容 |
|----------|------|
| Checkout | リポジトリを取得 |
| Setup PHP 8.4 | `pdo_sqlite` など必要な拡張を有効化 |
| Composer install | `composer.lock` に基づき依存をインストール（キャッシュ利用） |
| Prepare Laravel | `.env` 作成、`key:generate`、`storage` 権限 |
| Setup Node 22 | npm キャッシュ |
| Build frontend assets | `npm ci && npm run build` → `public/build/manifest.json` を生成 |
| Run tests | `composer test`（内部で `php artisan test`） |

**補足:** Breeze の画面テストは Blade 内の `@vite` により `public/build/manifest.json` が必要です。`public/build` は Git 管理外（`.gitignore`）のため、テスト前に必ず Vite ビルドを行います。

### 2. PHP Quality（`php-quality`）

PHP の静的品質チェックです。

| ステップ | 内容 |
|----------|------|
| Composer install | 依存インストール（`vendor` キャッシュ） |
| Pint | `vendor/bin/pint --test` — コードスタイル違反があれば失敗 |
| PHPStan | `composer phpstan` — 型・構造解析（Larastan level 5） |

### 3. Frontend（`frontend`）

フロントエンドの品質とビルドです。

| ステップ | 内容 |
|----------|------|
| npm ci | `package-lock.json` に基づき依存をインストール |
| ESLint | `npm run lint` |
| Vite build | `npm run build` — 本番ビルドが通るか確認 |

### 4. API Tests（`api-tests`）

Postman コレクションを **Newman** で CLI 実行し、実 HTTP（セッション認証込み）で API を検証します。

| ステップ | 内容 |
|----------|------|
| Prepare Laravel | SQLite（`database/ci.sqlite`）+ `migrate --seed`（`test@example.com` / `password`） |
| npm ci & build | ログイン画面（`GET /login`）用に Vite ビルド |
| Start server | `php artisan serve` を `127.0.0.1:8000` で起動し `/up` を待機 |
| Run Newman | `npm run test:api`（`Health` → `Auth` → `Tasks API` の順） |

**補足:** 環境ファイルは `postman/local.postman_environment.json` を使い、CI では `--env-var "baseUrl=http://127.0.0.1:8000"` で URL を上書きします。

---

## ローカルで CI と同じことを実行する

### Docker 開発環境（推奨）

```bash
# PHP 品質
docker compose exec app composer install
docker compose exec app vendor/bin/pint --test
docker compose exec app composer phpstan

# フロント
docker compose --profile node run --rm node sh -c "npm ci && npm run lint && npm run build"

# テスト（ビルド成果物が必要）
docker compose --profile node run --rm node sh -c "npm ci && npm run build"
docker compose exec app composer test

# API（アプリ起動 + Newman）
docker compose up -d
docker compose exec app php artisan migrate --seed
docker compose --profile node run --rm node sh -c "npm ci && npm run build"
docker compose --profile node run --rm node npm run test:api
```

### ホストで実行する場合

```bash
composer install
cp .env.example .env   # 未作成の場合
php artisan key:generate

vendor/bin/pint --test
composer phpstan

npm ci
npm run lint
npm run build

composer test

npm run test:api   # Docker で app が http://localhost:8000 のとき
```

`composer check` は **PHPStan + テストのみ** です（Pint / ESLint / build / Newman は含みません）。Docker 上で一式実行する場合は `./scripts/check-quality.sh` を使います。

---

## composer.json の関連スクリプト

| スクリプト | コマンド | CI での対応ジョブ |
|------------|----------|-------------------|
| `test` | `php artisan test` | php-tests |
| `phpstan` | PHPStan 解析 | php-quality |
| `check` | phpstan + test | （部分的） |
| `test:api` | Newman（Postman コレクション） | api-tests |
| `test:api:docker` | Newman（`baseUrl=http://nginx`、ローカル Docker 用） | （ローカル `check-quality.sh`） |

---

## よくある失敗と対処

### Vite manifest not found（PHPUnit が 500）

**症状:** `ViteManifestNotFoundException: ... public/build/manifest.json`

**原因:** `@vite` を使う Blade を描画するテストの前に `npm run build` していない。

**対処:**

```bash
npm ci && npm run build
composer test
```

CI では `php-tests` ジョブ内でビルド済みです。ローカルでもテスト前にビルドしてください。

### Pint が FAIL

**症状:** `fully_qualified_strict_types` や `ordered_imports` など

**対処:**

```bash
vendor/bin/pint          # 自動修正
vendor/bin/pint --test   # 再確認
```

### PHPStan エラー

**設定:** ルートの `phpstan.neon`（paths: `app`, `routes`, `config`、level: 5）

**対処:** ローカルで `composer phpstan` を実行し、指摘箇所を修正。

### ワークフロー YAML のバリデーションエラー

**症状:** `Unexpected value 'php-quality'` など

**原因:** `php-quality` や `frontend` が `jobs:` の外（インデント不足）に書かれている。

**対処:** すべてのジョブ ID を `jobs:` 直下で同じ深さに揃える。

```yaml
jobs:
  php-tests:
    ...
  php-quality:    # php-tests と同じインデント
    ...
  frontend:
    ...
```

### ESLint / npm build 失敗

**対処:**

```bash
npm ci
npm run lint
npm run build
```

エラーメッセージに従い `resources/js` 配下を修正。

---

## ブランチ保護（任意）

GitHub の **Settings → Branches → Branch protection rules** で、例えば次を設定できます。

- `main` へのマージ前に **CI 成功を必須**
- **Require branches to be up to date before merging**

---

## 関連ファイル

| ファイル | 役割 |
|----------|------|
| [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) | CI ワークフロー定義 |
| [`phpunit.xml`](../phpunit.xml) | PHPUnit 設定（テスト時 SQLite メモリ） |
| [`phpstan.neon`](../phpstan.neon) | PHPStan 設定 |
| [`pint.json`](../pint.json) | Laravel Pint 設定 |
| [`eslint.config.js`](../eslint.config.js) | ESLint 設定 |
| [TESTING.md](./TESTING.md) | テスト・Postman の詳細手順 |

---

## ステータスバッジ（任意）

README に CI 結果を表示する場合の例:

```markdown
[![CI](https://github.com/<owner>/tech-update-task-app/actions/workflows/ci.yml/badge.svg)](https://github.com/<owner>/tech-update-task-app/actions/workflows/ci.yml)
```

`<owner>` は GitHub のユーザー名または Organization 名に置き換えてください。
