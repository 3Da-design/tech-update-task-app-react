# CLAUDE.md

このファイルは、Claude Code (claude.ai/code) が本リポジトリで作業する際のガイダンスです。

## 必読（この順）

1. `../EXPERIMENT-STACK.md` — 研究全体（第1章完了・第2章方針・制約）
2. 本ファイル
3. `docs/STACK-PROFILE.md` — 本スタック固有の構成（ベースライン完成後に整備）
4. （参考）`../tech-update-task-app-htmljs/docs/STACK-PROFILE.md` — S1 完了後の Sanctum 設定参考

## 言語設定

ユーザーとのやり取り・説明・コミットメッセージ・コメントは **日本語**。コード識別子は英語可。

## リポジトリの位置づけ

| 項目 | 内容 |
|------|------|
| 研究テーマ | 技術更新に強い Web アプリ基盤の検討 |
| 章 | **第2章** — スタック比較 |
| スタック ID | **S2** — React + Laravel API |
| 設計 | **improved 固定**（TaskService / TaskRepository を維持） |
| fork 元 | `../tech-update-task-app`（improved / S0 Blade） |
| 第1章 legacy | 本リポジトリでは扱わない。参照のみ |

**目的:** improved 設計を維持したまま、Blade を **React（Vite + TypeScript）SPA** に置き換え、S1（HTML/JS）・S0（Blade）と修正のしやすさを比較する。

## 絶対に守る制約

1. **Fat Controller 化禁止** — Service/Repository を削除・バイパスしない。
2. **ベースライン汚染禁止** — シナリオ変更は `exp/*` のみ。
3. **Docker Compose のみ** — ホストで `npm install` / `php artisan serve` を実行しない。
4. **ベースライン仕様** — タスク属性4項目のみ（シナリオ前）。
5. **S1 と認証を揃える** — Laravel Sanctum（SPA 向け設定）。

## 開発環境

| 項目 | 値 |
|------|-----|
| Web（Laravel/nginx） | `http://localhost:8004` |
| Vite dev（フロント） | `http://localhost:5175` |
| DB 公開ポート | `5436` |
| Compose 名 | `tech-update-task-app-react` |
| シードユーザー | `test@example.com` / `password` |

### 初回セットアップ

```bash
cp .env.example .env
docker compose build app
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
composer npm:docker-build
docker compose --profile node run --rm --service-ports node npm run dev
```

### よく使うコマンド

```bash
./scripts/check-quality.sh
docker compose exec app composer test
docker compose --profile node run --rm node npm run lint
docker compose --profile node run --rm node npm run build
npm run test:api:docker
./scripts/curl-api-smoke.sh
```

## スタック固有の実装方針（S2）

### フロント

- 配置: **`frontend/`**（Vite + React + **TypeScript**）。
- Blade タスク UI は削除し、SPA に置き換え。
- ルーティング: React Router 等（選定理由を `docs/STACK-PROFILE.md` に記載）。
- タスク CRUD・一覧フィルタ・ソートは S0 / S1 と **機能 parity**。

### バックエンド

- Laravel REST API は improved のまま（`API\TaskController` → `TaskService` → `TaskRepository`）。
- `Web\TaskController` のタスク Blade ルートは削除または無効化。
- 認証: **Laravel Sanctum（SPA）** — `SANCTUM_STATEFUL_DOMAINS` に `localhost:5175` 等を設定。

### ビルド・CI

- 本番相当の資産は `composer npm:docker-build`（node コンテナ）でビルド。
- `check-quality.sh` が ESLint + build + PHPUnit + Newman を通すこと。

### テスト

- PHPUnit / Newman は S0 と同様に維持。
- `postman/local.postman_environment.json` の `baseUrl` は `http://localhost:8004`。

## アーキテクチャ（improved・維持）

```text
Browser (React SPA)
    │ fetch + Sanctum Cookie
    ▼
API\TaskController
    ▼
TaskService
    ▼
TaskRepository → Task (Model)
```

## 実験ワークフロー

```bash
composer experiment:metrics -- --phase baseline --diff-ref experiment-baseline-v1
composer experiment:metrics -- --phase after_update --diff-ref experiment-baseline-v1
composer experiment:metrics -- --phase after_fix --diff-ref experiment-baseline-v1
```

- 主指標: `git_app`（`after_fix`）
- 第2章比較の基準: 第1章 S0 improved の同一シナリオ数値（`../EXPERIMENT-STACK.md` 第1章結果表）

## ベースライン完了の定義

- [ ] React からタスク CRUD・フィルタ・ソートが動作
- [ ] Sanctum SPA 認証が動作
- [ ] `npm run build` / `check-quality.sh` 成功
- [ ] `experiment-baseline-v1` タグ作成
- [ ] `docs/STACK-PROFILE.md` 完成

## 関連ドキュメント

| ファイル | 内容 |
|----------|------|
| `../EXPERIMENT-STACK.md` | 研究全体 |
| `docs/STACK-PROFILE.md` | S2 固有 |
| `docs/EXPERIMENT.md` | 指標定義（参照） |
