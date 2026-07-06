# S2 スタックプロファイル — React + Laravel API

## 概要

| 項目 | 内容 |
|------|------|
| スタック ID | **S2** |
| 名称 | React + Laravel API |
| 設計 | improved（TaskService / TaskRepository 維持） |
| フロント | React 18 + Vite + TypeScript（SPA） |
| バックエンド | Laravel 13 REST API |
| 認証 | Laravel Sanctum（SPA / Cookie 方式） |
| fork 元 | `tech-update-task-app`（S0 improved） |

## アーキテクチャ

```text
Browser (React SPA — frontend/)
    │ axios（withCredentials + withXSRFToken）
    │ GET /sanctum/csrf-cookie → XSRF-TOKEN Cookie
    │ 以降のリクエストに X-XSRF-TOKEN ヘッダーを自動付与
    ▼
API\TaskController
    ▼
TaskService
    ▼
TaskRepository → Task (Model)
```

### S0 Blade / S1 HTML+JS との主な違い

| 観点 | S0 Blade | S1 HTML/JS | S2 React |
|------|----------|------------|----------|
| タスク UI | Blade（サーバサイド描画） | 静的 HTML + fetch | React コンポーネント（SPA） |
| ルーティング | Laravel Router（Web） | 単一ページ + JS 分岐 | React Router（クライアントサイド） |
| Web\TaskController | 有 | **削除** | **削除** |
| ログイン画面 | Breeze Blade | Breeze Blade を継続利用 | **React 完結**（Blade `/login` 不使用） |
| 認証状態管理 | サーバサイドセッション + Blade 再描画 | セッション Cookie + JS 側で都度判定 | `AuthContext`（React State）+ `/api/user` |
| CSRF 送出 | Blade `@csrf` | `XSRF-TOKEN` Cookie → 手動で `X-XSRF-TOKEN` ヘッダー付与 | axios `withXSRFToken: true` で自動付与 |
| ビルド | Vite（Blade に asset 埋め込み） | ビルドなし（静的配信） | Vite（`frontend/` → `public/` に出力、nginx 配信） |

**React Router を採用した理由:** SPA 内のページ遷移（`/login` ↔ `/`）をクライアントサイドで完結させ、ハードリロードなしの画面遷移・保護ルート（`ProtectedRoute`）をシンプルに実装できるため。本アプリの規模ではルート数が少なく他のルーティングライブラリの高度な機能は不要だが、Laravel 側 nginx 設定（`try_files ... /index.html`）と組み合わせることで、ブラウザの直接アクセス・リロードにも対応できる標準的な構成として選定した。

## ポート・コンテナ構成

| サービス | コンテナ名 | ポート |
|----------|-----------|--------|
| Nginx | `tech-update-task-app-react-nginx` | 8004 |
| PHP-FPM | `tech-update-task-app-react-php` | 9000（内部） |
| PostgreSQL | `tech-update-task-app-react-postgres` | 5436 |
| Node（開発 / ビルド時のみ） | profile: node | 5175 → コンテナ内 5173 |

## ファイル構成

### フロントエンド（`frontend/`）

```text
frontend/
├── index.html                      … Vite エントリ HTML
└── src/
    ├── main.tsx                    … React エントリポイント
    ├── App.tsx                     … React Router のルート定義
    ├── types.ts                    … Task / User 等の型定義
    ├── index.css                   … 共通スタイル
    ├── api/
    │   ├── client.ts               … axios インスタンス（withCredentials, withXSRFToken）
    │   ├── auth.ts                 … login / logout / fetchCurrentUser
    │   └── tasks.ts                … タスク CRUD・一覧フィルタ API 呼び出し
    ├── context/
    │   └── AuthContext.tsx         … 認証状態（user, isLoading）を React Context で管理
    ├── components/
    │   ├── ProtectedRoute.tsx      … 未ログイン時に /login へリダイレクト
    │   ├── TaskForm.tsx            … タスク作成・編集フォーム
    │   ├── TaskFilterBar.tsx       … タイトル検索・ステータス絞込・期限日ソート
    │   ├── TaskTable.tsx           … タスク一覧テーブル
    │   └── StatusLabel.tsx         … ステータス表示ラベル・選択肢定義
    └── pages/
        ├── LoginPage.tsx           … ログイン画面（SPA 完結、Blade 不使用）
        └── TasksPage.tsx           … タスク一覧ページ（フィルタ・CRUD の統合）
```

- Vite のルートは `frontend/`（`vite.config.ts` の `root: 'frontend'`）。
- ビルド成果物は `../public`（Laravel の `public/`）に出力し、nginx がそのまま静的配信する（`emptyOutDir: false` で Laravel 側の `public/index.php` 等を消さない）。
- 開発時は `docker compose --profile node run --rm --service-ports node npm run dev` で Vite dev サーバ（5175）を別オリジンとして起動し、axios が `http://localhost:8004` の API を CORS 経由で呼ぶ。

### バックエンド（API・維持）

```text
app/Http/Controllers/API/TaskController.php  … REST API エンドポイント
app/Services/TaskService.php                 … ビジネスロジック
app/Repositories/TaskRepository.php          … データアクセス
app/Repositories/Contracts/TaskRepositoryInterface.php
app/Http/Resources/TaskResource.php          … JSON レスポンス整形
app/Http/Requests/                           … バリデーション
```

- S0 / S1 と同一の `TaskService` / `TaskRepository` をそのまま維持（Fat Controller 化していない）。

### 削除・無効化したファイル

| ファイル | 理由 |
|----------|------|
| `app/Http/Controllers/Web/TaskController.php` | API 一本化のため削除（タスク操作は `/api/tasks` のみ） |
| `resources/views/tasks/*.blade.php` | React コンポーネントに置き換え |
| `resources/views/welcome.blade.php` | React SPA の `/` に置き換え |
| `resources/views/auth/login.blade.php` | React の `LoginPage` に置き換え（SPA 完結） |
| `resources/views/layouts/navigation.blade.php` | Blade ナビゲーション不要（React 側でヘッダー描画） |
| `resources/js/`, `resources/css/app.css`, `tailwind.config.js`, `postcss.config.js` | Blade 向けの旧フロント資産（`frontend/` に置き換え） |

### 残存する Blade

| ファイル | 理由 |
|----------|------|
| `resources/views/auth/register.blade.php` | Breeze 標準の登録・パスワード再設定フロー（本研究のスコープ外機能のため最小限維持） |
| `resources/views/layouts/partials/*.blade.php` | 上記ページの共通レイアウト部品 |

## 認証方式（Sanctum SPA / Cookie）

1. React アプリ起動時（`AuthContext` の `useEffect`）に `GET /api/user` を呼び、既存セッションの有無を確認（未ログインなら 401 → `user: null`）。
2. ログインフォーム送信時:
   - `GET /sanctum/csrf-cookie` で `XSRF-TOKEN` Cookie（暗号化済み）と `laravel-session` Cookie を取得。
   - `POST /api/login`（`withCredentials: true`）でメール・パスワードを送信。axios の `withXSRFToken: true` が `XSRF-TOKEN` Cookie を自動的に復号し `X-XSRF-TOKEN` ヘッダーへ設定する。
   - ログイン成功後、Laravel がセッションを再生成（`session()->regenerate()`）し、新しい `XSRF-TOKEN` / `laravel-session` を発行。
3. 以降の `/api/tasks` 等へのリクエストは `withCredentials: true` で Cookie を送信し、ミューテーション系（POST/PUT/DELETE）では `X-XSRF-TOKEN` ヘッダーも自動付与される。
4. `Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful` は **GET を含む全リクエスト**で `Referer` または `Origin` ヘッダーを `SANCTUM_STATEFUL_DOMAINS` と照合し、一致した場合のみ Cookie セッションを信頼する。ブラウザは通常のナビゲーション・fetch で自動的にこれらのヘッダーを送るため、フロント側で明示的に設定する必要はない（Postman/Newman など生 HTTP クライアントを使う場合は明示的に設定が必要。後述）。
5. ログアウト: `POST /api/logout` → セッション破棄 → React 側で `user: null` に戻し `/login` へ。

### 環境変数（`.env` / `.env.example`）

```
SANCTUM_STATEFUL_DOMAINS=localhost:5175,localhost:8004,127.0.0.1:5175,127.0.0.1:8004,nginx
FRONTEND_URLS=http://localhost:5175,http://localhost:8004,http://nginx
```

- `SANCTUM_STATEFUL_DOMAINS`: Cookie セッションを信頼するフロントの `host:port`。開発時の Vite dev サーバ（5175）と本番相当の nginx 配信（8004）の両方を含む。`nginx` は Docker ネットワーク内から Newman が呼ぶ場合のホスト名。
- `FRONTEND_URLS`: `config/cors.php` が読む CORS 許可オリジン（`supports_credentials: true` のため `*` 不可）。

### ミドルウェア構成（`bootstrap/app.php`）

```php
$middleware->statefulApi();
$middleware->api(prepend: [
    PrefersJsonResponses::class,
]);
$middleware->redirectGuestsTo('/login');
```

- `statefulApi()` が Sanctum の `EnsureFrontendRequestsAreStateful` を API ミドルウェアスタックの先頭に追加する。
- `redirectGuestsTo('/login')`: 名前付きルート `login` が存在しない（React SPA が `/login` を担当）ため、パス直書きでリダイレクト先を指定。

## Postman / Newman での CSRF 再現（ハマりどころ）

ブラウザは Cookie → ヘッダー変換や `Referer`/`Origin` の付与を自動で行うが、Newman（Postman CLI）はそうではない。`postman/Task-API.postman_collection.json` のコレクションレベル prerequest/test スクリプトで以下を明示的に再現している。

1. **全リクエストに `Referer` / `Origin` / `X-Requested-With` を付与**（GET も含む）。`EnsureFrontendRequestsAreStateful` は GET リクエストでもこれらのヘッダーでフロントかどうか判定するため、POST 系だけに付与すると `GET /api/tasks` が 401 になる。
2. **`X-XSRF-TOKEN` ヘッダーは `XSRF-TOKEN` Cookie の値を URL デコードしたもの**（さらに復号はしない）。Laravel の `VerifyCsrfToken::getTokenFromRequest()` が `X-XSRF-TOKEN` ヘッダーの値を暗号化されたままの状態で受け取り、内部で `decrypt()` する実装のため。
3. **`pm.cookies.get()` は直前のレスポンスの test スクリプト内でしか確実に取得できない**（次リクエストの prerequest スクリプトからは空になることがある = Newman 特有の挙動）。そのため、コレクションレベルの `test` イベントで毎レスポンス後に `XSRF-TOKEN` の値を `collectionVariables.csrfCookie` に退避し、prerequest スクリプトはそれをフォールバックとして参照する。ログイン時はセッション再生成で CSRF トークンも変わるため、初回の `GET /sanctum/csrf-cookie` だけでなく毎レスポンス後に更新する必要がある。

## API エンドポイント

| メソッド | パス | 説明 |
|----------|------|------|
| GET | `/sanctum/csrf-cookie` | CSRF Cookie 発行 |
| POST | `/api/login` | ログイン（Cookie セッション発行） |
| POST | `/api/logout` | ログアウト |
| GET | `/api/user` | 現在のログインユーザー取得 |
| GET | `/api/tasks` | 一覧（フィルタ・ソート対応） |
| POST | `/api/tasks` | 作成（201） |
| PUT | `/api/tasks/{id}` | 更新 |
| DELETE | `/api/tasks/{id}` | 削除（204） |

### クエリパラメータ（GET /api/tasks）

| パラメータ | 型 | 説明 |
|------------|-----|------|
| `title` | string | 部分一致検索 |
| `status` | string | 完全一致（`todo` / `in_progress` / `done`） |
| `due_date_sort` | string | `asc`（昇順・NULL 先頭）/ `desc`（降順） |

## テスト構成

| テスト | ファイル | 内容 |
|--------|----------|------|
| API CRUD | `tests/Feature/TaskApiTest.php` | 8 テスト |
| API フィルタ | `tests/Feature/TaskListFilterTest.php` | 4 テスト（API のみ） |
| 認証 | `tests/Feature/Auth/*.php` | Sanctum SPA 向けに調整済み |
| Newman | `postman/Task-API.postman_collection.json` | 13 assertions（Health / Auth / Tasks API） |
| フロント | `eslint.config.js` + `tsc --noEmit` | 型チェック・Lint（自動テストは未実装、手動確認で parity 検証） |

### S0 からの変更点

- `TaskWebTest` を削除（Web 経由のタスク Blade ルート自体が存在しないため）。
- `TaskListFilterTest`: Web フィルタテスト（Blade 経由）を削除、API フィルタテスト（4 件）のみ維持。

## 品質ゲート

```bash
./scripts/check-quality.sh
```

実行順: PHPStan → npm ci（Docker） → ESLint → Vite build（`tsc --noEmit` 含む） → PHPUnit → Newman

## 開発手順

### 初回セットアップ

```bash
cp .env.example .env
docker compose build app
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
composer npm:docker-build
```

### 開発時（Vite dev サーバ）

```bash
docker compose --profile node run --rm --service-ports node npm run dev
```

- `http://localhost:5175` で HMR 付きの開発サーバが起動する。
- axios は `http://localhost:8004` の API を CORS + Cookie 付きで呼ぶ（別オリジン）。

### シードユーザー

| メール | パスワード |
|--------|-----------|
| `test@example.com` | `password` |

### 手動確認手順

1. `docker compose up -d` でスタック起動（nginx / php-fpm / postgres）。
2. `composer npm:docker-build` で `frontend/` をビルドし `public/` に出力（本番相当の確認をする場合）。
3. ブラウザで `http://localhost:8004/` にアクセス → 未ログインのため `/login`（React SPA）にリダイレクト。
4. `test@example.com` / `password` でログイン → タスク一覧ページへ遷移。
5. 「新規作成」→ タイトル・説明・ステータス・期限日を入力 → 「保存」→ 一覧に追加。
6. 「編集」→ フィールド変更（例: ステータスを進行中に）→ 「保存」→ 一覧に反映。
7. 「削除」→ 確認ダイアログ → 一覧から消去。
8. タイトル検索（部分一致）→ 「適用」→ 絞り込みを確認。
9. ステータス絞込（すべて/未着手/進行中/完了）→ 「適用」→ 絞り込みを確認。
10. 期限日ソート（指定なし/昇順/降順）→ 「適用」→ 並び替えを確認（NULL は先頭）。
11. 「ログアウト」→ ログイン画面に遷移し、`/` への直接アクセスが `/login` にリダイレクトされることを確認。

上記手順は本セッションで Playwright（headless Chromium）による自動操作で実施済み。ログイン・作成・フィルタ（タイトル/ステータス）・ソート・編集・削除のすべてで期待どおりの結果を確認した。

## ベースライン仕様

- タスク属性: `title` / `description` / `due_date` / `status` の **4 項目のみ**
- `priority` 等のシナリオ変更は未実装
- ベースラインタグ: `experiment-baseline-v1`
