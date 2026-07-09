# シナリオ: API 仕様変更 — 既存属性の型変更（`api-spec-change-status-int`）

## 目次

- [0. この実験について](#0-この実験について)
- [1. 概要](#1-概要)
- [2. 事前条件チェック](#2-事前条件チェック)
- [3. 修正対象ファイル一覧](#3-修正対象ファイル一覧)
- [4. 実施手順](#4-実施手順)
  - [Phase 0: ブランチ作成](#phase-0-ブランチ作成)
  - [Phase 1: baseline メトリクス](#phase-1-baseline-メトリクス)
  - [Phase 2: 変更適用（テスト・Postman 未着手）](#phase-2-変更適用テストpostman-未着手)
  - [Phase 3: after_update メトリクス](#phase-3-after_update-メトリクス)
  - [Phase 4: テスト・Postman 修正 → CI 緑](#phase-4-テストpostman-修正-ci-緑)
  - [Phase 5: after_fix メトリクス・記録](#phase-5-after_fix-メトリクス・記録)
- [5. 完了条件](#5-完了条件)
- [6. 触らないファイルとその理由](#6-触らないファイルとその理由)
- [関連](#関連)

## 0. この実験について

本実験は、タスクの `status` を string（`todo` / `in_progress` / `done`）から integer（`0` / `1` / `2`）へ変更する破壊的 API 仕様変更を適用し、その波及範囲を計測する。既存フィールドの型変更は、属性追加（priority）や DB スキーマ変更（タイトル検索）と対比し、技術更新時の修正コスト・失敗パターンの違いを評価するのに適している。improved 構成では Controller は HTTP 受け渡しのみとし、正規化は `TaskService`、永続化・一覧クエリは `TaskRepository`、HTTP バリデーションは FormRequest×3、表示ラベルは `config/task.php`（バックエンド）と React の `StatusLabel.tsx`（フロント）に修正が集まる想定である。S2 のフロントは **`frontend/src/types.ts` の `TaskStatus` 型を `0|1|2` に変える**のが起点で、TypeScript が string→int の不整合（select の Number 変換漏れ等）を早期検出する点（H3）を観察できる。`TaskResource` は Model の integer cast により JSON も自動的に int になるため触らない。

## 1. 概要

| 項目 | 値 |
| --- | --- |
| リポジトリ | tech-update-task-app-react（S2 / improved） |
| 実験の内容 | `status` を string から integer（0/1/2）へ変更 |
| ブランチ名 | exp/api-spec-change-status-int |
| 参照MD | docs/scenarios/api-spec-change-status-int.md |

## 2. 事前条件チェック

- [ ]  experiment-baseline-v1 または CI 緑 — 変更前の正しいベースラインと diff 比較の基準点が必要なため
- [ ]  Docker 起動 — `docker compose exec app` でマイグレーション・テスト・Newman を実行するため
- [ ]  PostgreSQL（status数値化・タイトル検索のみ） — マイグレーションが PostgreSQL 専用 `ALTER COLUMN ... USING CASE` を使うため

## 3. 修正対象ファイル一覧

| # | ファイルパス | 修正箇所 | フェーズ | 作業内容 | 解説（なぜ触るか） |
| --- | --- | --- | --- | --- | --- |
| 1 | `database/migrations/*_change_status_to_integer_on_tasks_table.php` | `up()` / `down()` | 2 | 新規作成 | DB カラム型変更と既存データ移行のため |
| 2 | `config/task.php` | `status_values`, `status_labels` | 2 | int マッピング定義 | 全層が参照する status の正規値・表示ラベルの単一ソース |
| 3 | `app/Models/Task.php` | PHPDoc 15行目、`casts()` | 2 | integer cast 追加 | Eloquent が status を int として扱うため |
| 4 | `app/Http/Requests/StoreTaskRequest.php` | `rules()` 29行目 | 2 | `string` → `integer` | POST 入力の HTTP 境界バリデーション |
| 5 | `app/Http/Requests/UpdateTaskRequest.php` | `rules()` 29行目 | 2 | `string` → `integer` | PUT 入力の HTTP 境界バリデーション |
| 6 | `app/Http/Requests/IndexTaskRequest.php` | `rules()` 43行目 | 2 | `string` → `integer` | 一覧フィルタ `?status=` のバリデーション |
| 7 | `app/Services/TaskService.php` | `normalizeListFilters()` 74–92行目、`normalizeTaskPayload()` 126行前 | 2 | int 正規化ロジック | Controller を触らずユースケース層で型を揃えるため |
| 8 | `app/Repositories/TaskRepository.php` | `getFiltered()` 20–23行目 | 2 | `is_string` → `is_int` | 一覧クエリの status 比較を int に合わせるため |
| 9 | `app/Repositories/Contracts/TaskRepositoryInterface.php` | PHPDoc 11行目 | 2 | `status?: string` → `status?: int` | フィルタ型の契約を実装と一致させるため |
| 10 | `frontend/src/types.ts` | `TaskStatus` 型 1行目 | 2 | `'todo'\|...` → `0\|1\|2`（起点） | TS 型定義（H3 の起点） |
| 11 | `frontend/src/components/StatusLabel.tsx` | `STATUS_OPTIONS` value 3–7行目 | 2 | value を int に | 表示ラベルの単一ソース（フロント） |
| 12 | `frontend/src/components/TaskForm.tsx` | `EMPTY_FORM` / status select onChange | 2 | int 既定値・`Number()` 変換 | フォーム送信値を int に |
| 13 | `frontend/src/components/TaskFilterBar.tsx` | status state / onChange | 2 | 空文字以外は `Number()` | フィルタ送信値を int に |
| 14 | `frontend/src/api/tasks.ts` | `listTasks` status param 7行目 | 2 | `status=0` を落とさない送出 | 一覧クエリ（int 対応） |
| 15 | `tests/Feature/TaskApiTest.php` | 全 `status` 参照（39, 54, 67, 78, 93, 99, 111, 125行目ほか） | 4 | 期待値を int に | API テストを新仕様に合わせるため |
| 16 | `tests/Feature/TaskListFilterTest.php` | 98行目のクエリ、127–151行目の seed | 4 | `?status=2` 等に変更 | status フィルタテスト（S2 は API のみ） |
| 17 | `postman/Task-API.postman_collection.json` | 195, 255行目の request body | 4 | `"status": 0` / `1` に変更 | Newman が新 API 仕様で通るようにするため |

## 4. 実施手順

### Phase 0: ブランチ作成

**この Phase の目的:** 実験用ブランチを baseline タグから切り、以降の変更を独立して計測できるようにする。

**Step 0-1.** 実験ブランチを作成する。

```bash
git checkout -b exp/api-spec-change-status-int experiment-baseline-v1
```

---

### Phase 1: baseline メトリクス

**この Phase の目的:** 変更前の状態を記録し、あとで diff 比較できるようにする。

**Step 1-1.** baseline フェーズのメトリクスを取得する。

```bash
composer experiment:metrics -- --phase baseline --diff-ref experiment-baseline-v1
```

---

### Phase 2: 変更適用（テスト・Postman 未着手）

**この Phase の目的:** 仕様変更を本番コードに適用し、テスト未修正の失敗状態（after_update）を計測できるようにする。

**Step 2-1.** マイグレーションファイルを生成する。

```bash
docker compose exec app php artisan make:migration change_status_to_integer_on_tasks_table
```

**Step 2-2.** 生成されたマイグレーションを編集する。

- **ファイル:** `database/migrations/*_change_status_to_integer_on_tasks_table.php`（Step 2-1 で生成されたパス）
- **場所:** `up()` / `down()` メソッド全体
- **解説:** PostgreSQL 専用 SQL で既存 string 値を int に移行し、カラム型を smallint に変更する。
- **変更前:** （空の `up()` / `down()` スタブ）
- **変更後:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  public function up(): void
  {
    DB::statement("
      ALTER TABLE tasks
      ALTER COLUMN status TYPE smallint
      USING (
        CASE status
          WHEN 'todo' THEN 0
          WHEN 'in_progress' THEN 1
          WHEN 'done' THEN 2
          ELSE 0
        END
      )
    ");
  }

  public function down(): void
  {
    DB::statement("
      ALTER TABLE tasks
      ALTER COLUMN status TYPE varchar(255)
      USING (
        CASE status
          WHEN 0 THEN 'todo'
          WHEN 1 THEN 'in_progress'
          WHEN 2 THEN 'done'
          ELSE 'todo'
        END
      )
    ");
  }
};
```

**Step 2-3.** マイグレーションを適用する。

```bash
docker compose exec app php artisan migrate
```

**Step 2-4.** config の status 定義を int ベースに変更する。

- **ファイル:** `config/task.php`
- **場所:** 5行目 `status_values`
- **解説:** 全層が参照する status の許可値と表示ラベルを int ベースで定義する。
- **変更前:**

```php
  'status_values' => ['todo', 'in_progress', 'done'],
```

- **変更後:**

```php
  'status_values' => [0, 1, 2],
  'status_labels' => [
    0 => 'todo',
    1 => 'in_progress',
    2 => 'done',
  ],
```

**Step 2-5.** Model に integer cast と PHPDoc を追加する。

- **ファイル:** `app/Models/Task.php`
- **場所:** PHPDoc 15行目、`casts()` 21–26行目
- **解説:** Eloquent が DB の smallint を PHP int として扱い、JSON 出力も int になるようにする。
- **変更前:**

```php
 * @property string $status
```

```php
  protected function casts(): array
  {
    return [
      'due_date' => 'date',
    ];
  }
```

- **変更後:**

```php
 * @property int $status
```

```php
  protected function casts(): array
  {
    return [
      'status' => 'integer',
      'due_date' => 'date',
    ];
  }
```

**Step 2-6.** StoreTaskRequest の status バリデーションを integer に変更する。

- **ファイル:** `app/Http/Requests/StoreTaskRequest.php`
- **場所:** `rules()` 29行目
- **解説:** POST リクエストの status を int として受け付ける HTTP 境界にする。
- **変更前:**

```php
      'status' => ['required', 'string', Rule::in(config('task.status_values'))],
```

- **変更後:**

```php
      'status' => ['required', 'integer', Rule::in(config('task.status_values'))],
```

**Step 2-7.** UpdateTaskRequest の status バリデーションを integer に変更する。

- **ファイル:** `app/Http/Requests/UpdateTaskRequest.php`
- **場所:** `rules()` 29行目
- **解説:** PUT リクエストの status を int として受け付ける HTTP 境界にする。
- **変更前:**

```php
      'status' => ['sometimes', 'required', 'string', Rule::in(config('task.status_values'))],
```

- **変更後:**

```php
      'status' => ['sometimes', 'required', 'integer', Rule::in(config('task.status_values'))],
```

**Step 2-8.** IndexTaskRequest の status フィルタバリデーションを integer に変更する。

- **ファイル:** `app/Http/Requests/IndexTaskRequest.php`
- **場所:** `rules()` 43行目
- **解説:** 一覧クエリ `?status=` を int として検証する HTTP 境界にする。
- **変更前:**

```php
      'status' => ['nullable', 'string', Rule::in(config('task.status_values'))],
```

- **変更後:**

```php
      'status' => ['nullable', 'integer', Rule::in(config('task.status_values'))],
```

**Step 2-9.** TaskService の normalizeListFilters を int 正規化に変更する。

- **ファイル:** `app/Services/TaskService.php`
- **場所:** `normalizeListFilters()` 74–92行目
- **解説:** Controller を触らず、一覧フィルタの status を許可された int に正規化して Repository に渡す。
- **変更前:**

```php
   * @return array{title?: string, status?: string, due_date_sort?: string}
   */
  private function normalizeListFilters(array $query): array
  {
    $filters = [];

    if (isset($query['title']) && is_string($query['title'])) {
      $title = trim($query['title']);
      if ($title !== '') {
        $filters['title'] = $title;
      }
    }

    if (isset($query['status']) && is_string($query['status'])) {
      $status = trim($query['status']);
      if ($status !== '') {
        $filters['status'] = $status;
      }
    }
```

- **変更後:**

```php
   * @return array{title?: string, status?: int, due_date_sort?: string}
   */
  private function normalizeListFilters(array $query): array
  {
    $filters = [];

    if (isset($query['title']) && is_string($query['title'])) {
      $title = trim($query['title']);
      if ($title !== '') {
        $filters['title'] = $title;
      }
    }

    if (isset($query['status']) && is_numeric($query['status'])) {
      $status = (int) $query['status'];
      if (in_array($status, config('task.status_values'), true)) {
        $filters['status'] = $status;
      }
    }
```

**Step 2-10.** TaskService の normalizeTaskPayload に status の int キャストを追加する。

- **ファイル:** `app/Services/TaskService.php`
- **場所:** `normalizeTaskPayload()` `return $data;` の直前（126行前）
- **解説:** 作成・更新ペイロードの status を永続化前に int に揃える。
- **変更前:**

```php
    }

    return $data;
  }
}
```

- **変更後:**

```php
    }

    if (array_key_exists('status', $data)) {
      $data['status'] = (int) $data['status'];
    }

    return $data;
  }
}
```

**Step 2-11.** TaskRepository の status フィルタ比較を is_int に変更する。

- **ファイル:** `app/Repositories/TaskRepository.php`
- **場所:** `getFiltered()` 20–23行目
- **解説:** 正規化済み int フィルタで DB クエリを組み立てる。
- **変更前:**

```php
    $status = $filters['status'] ?? null;
    if (is_string($status) && $status !== '') {
      $query->where('status', $status);
    }
```

- **変更後:**

```php
    $status = $filters['status'] ?? null;
    if (is_int($status)) {
      $query->where('status', $status);
    }
```

**Step 2-12.** TaskRepositoryInterface の PHPDoc を更新する。

- **ファイル:** `app/Repositories/Contracts/TaskRepositoryInterface.php`
- **場所:** PHPDoc 11行目
- **解説:** インターフェースのフィルタ型注釈を実装と一致させる（メソッドシグネチャは変更しない）。
- **変更前:**

```php
   * @param  array{title?: string, status?: string, due_date_sort?: string}  $filters
```

- **変更後:**

```php
   * @param  array{title?: string, status?: int, due_date_sort?: string}  $filters
```

**Step 2-13.** `frontend/src/types.ts` の `TaskStatus` 型を int union に変更する（**フロント修正の起点**）。

- **ファイル:** `frontend/src/types.ts`
- **場所:** 1行目
- **解説:** ここを直すと TypeScript が `STATUS_OPTIONS` の value・select の onChange・API params など未対応箇所で型エラーを出し、修正箇所を機械的に洗い出せる（H3 の観察材料）。`Task` / `TaskFormInput` / `TaskListQuery` は `TaskStatus` を参照するため自動追従する。
- **変更前:**

```ts
export type TaskStatus = 'todo' | 'in_progress' | 'done';
```

- **変更後:**

```ts
export type TaskStatus = 0 | 1 | 2;
```

**Step 2-14.** `frontend/src/components/StatusLabel.tsx` の選択肢 value を int にする。

- **ファイル:** `frontend/src/components/StatusLabel.tsx`
- **場所:** `STATUS_OPTIONS` 3–7行目
- **解説:** 選択肢の value を int に、表示ラベル（`未着手` 等）はそのまま維持する。`statusLabel()` は value 一致で引くため変更不要。
- **変更前:**

```ts
export const STATUS_OPTIONS: { value: TaskStatus; label: string }[] = [
  { value: 'todo', label: '未着手' },
  { value: 'in_progress', label: '進行中' },
  { value: 'done', label: '完了' },
];
```

- **変更後:**

```ts
export const STATUS_OPTIONS: { value: TaskStatus; label: string }[] = [
  { value: 0, label: '未着手' },
  { value: 1, label: '進行中' },
  { value: 2, label: '完了' },
];
```

**Step 2-15.** `frontend/src/components/TaskForm.tsx` の既定値と onChange を int 化する。

- **ファイル:** `frontend/src/components/TaskForm.tsx`
- **解説:** `EMPTY_FORM` の status 既定値を int にし、select の onChange で `Number()` 変換する（`event.target.value` は文字列のため、これを怠ると型エラーになる — H3 の検出点）。
- **`EMPTY_FORM` 変更後:**

```ts
const EMPTY_FORM: TaskFormInput = {
  title: '',
  description: '',
  status: 0,
  due_date: '',
};
```

- **status select の onChange 変更後:**

```tsx
          onChange={(event) => setForm({ ...form, status: Number(event.target.value) as TaskFormInput['status'] })}
```

**Step 2-16.** `frontend/src/components/TaskFilterBar.tsx` の status フィルタを int 化する。

- **ファイル:** `frontend/src/components/TaskFilterBar.tsx`
- **解説:** フィルタ state は「未選択（空文字）」を保持しつつ、値選択時は int に変換する。`StatusFilter` 型は `TaskStatus | ''` のため型は自動追従し、onChange の変換のみ直す。
- **status select の onChange 変更後:**

```tsx
          onChange={(event) => {
            const value = event.target.value;
            setStatus(value === '' ? '' : (Number(value) as TaskStatus));
          }}
```

**Step 2-17.** `frontend/src/api/tasks.ts` の status クエリ送出を int 対応にする。

- **ファイル:** `frontend/src/api/tasks.ts`
- **場所:** `listTasks` 7行目
- **解説:** `status` が `0`（todo）のとき `if (query.status)` が falsy になり脱落するのを防ぐ。`params` は `Record<string, string>` のため `String()` で明示変換する。
- **変更前:**

```ts
  if (query.status) params.status = query.status;
```

- **変更後:**

```ts
  if (query.status !== undefined && query.status !== '') params.status = String(query.status);
```

**Step 2-18.** フロント資産を Docker 経由でビルドする（`tsc` の型チェックが通ることを確認）。

```bash
composer npm:docker-build
```

---

### Phase 3: after_update メトリクス

**この Phase の目的:** テスト未修正のまま、どれだけ壊れたかを数値化する。

**Step 3-1.** 変更をコミットする。

```bash
git add database/ config/ app/ frontend/
git commit -m "$(cat <<'EOF'
feat: change task status from string to integer
Migration, config, model, requests, TaskService, TaskRepository, and React frontend.
Tests and Postman are intentionally unchanged for after_update measurement.
EOF
)"
```

**Step 3-2.** after_update フェーズのメトリクスを取得する（PHPUnit / Newman の失敗数を記録）。

```bash
composer experiment:metrics -- --phase after_update --diff-ref experiment-baseline-v1
```

---

### Phase 4: テスト・Postman 修正 → CI 緑

**この Phase の目的:** 仕様変更に合わせてテストと Postman を直し、修正工数（after_fix）を計測可能にする。

**Step 4-1.** TaskApiTest の status 期待値を int に書き換える。

- **ファイル:** `tests/Feature/TaskApiTest.php`
- **場所:** ファイル全体の `status` 参照
- **解説:** API Feature テストが新しい int status 仕様と一致するようにする。
- **変更前（代表箇所）:**

```php
      'status' => 'todo',
```

```php
      'status' => 'in_progress',
```

```php
      'status' => 'invalid',
```

- **変更後（代表箇所）:**

```php
      'status' => 0,
```

```php
      'status' => 1,
```

```php
      'status' => 'invalid',  // 422 テストは文字列のまま（バリデーションエラー確認用）
```

全置換対応表:

| 変更前 | 変更後 |
| --- | --- |
| `'status' => 'todo'` | `'status' => 0` |
| `'status' => 'in_progress'` | `'status' => 1` |
| `'status' => 'done'` | `'status' => 2` |

**Step 4-2.** TaskListFilterTest の status フィルタと seed データを int に書き換える。

- **ファイル:** `tests/Feature/TaskListFilterTest.php`
- **場所:** 98行目、127–151行目
- **解説:** フィルタクエリとシードデータを int status に合わせ、`done` 相当は `2` で検証する。S2 は一覧フィルタが API のみのため、Web ルート `/tasks?status=` のクエリは存在しない（API `getJson` のみ）。
- **変更前:**

```php
    $response = $this->actingAs($this->user)->getJson('/api/tasks?status=done');
```

```php
      'status' => 'todo',
```

```php
      'status' => 'done',
```

```php
      'status' => 'in_progress',
```

- **変更後:**

```php
    $response = $this->actingAs($this->user)->getJson('/api/tasks?status=2');
```

```php
      'status' => 0,
```

```php
      'status' => 2,
```

```php
      'status' => 1,
```

**Step 4-3.** Postman コレクションの request body を int status に更新する。

- **ファイル:** `postman/Task-API.postman_collection.json`
- **場所:** 195行目、255行目（`raw` body）
- **解説:** Newman が POST/PUT で int status を送り、新 API 仕様で通るようにする。422 テスト（226行目）は `"invalid"` のまま。
- **変更前:**

```json
"raw": "{\n  \"title\": \"Postman task\",\n  \"status\": \"todo\"\n}"
```

```json
"raw": "{\n  \"title\": \"Updated from Postman\",\n  \"status\": \"in_progress\"\n}"
```

- **変更後:**

```json
"raw": "{\n  \"title\": \"Postman task\",\n  \"status\": 0\n}"
```

```json
"raw": "{\n  \"title\": \"Updated from Postman\",\n  \"status\": 1\n}"
```

**Step 4-4.** CI 相当の品質チェックを実行し、すべて緑にする。

```bash
./scripts/check-quality.sh
```

---

### Phase 5: after_fix メトリクス・記録

**Step 5-1.** 変更をコミットする。

```bash
git add tests/ postman/
git commit -m "$(cat <<'EOF'
test: update tests and Postman for status integer

Align Feature tests and Newman collection with 0/1/2 status values.
EOF
)"
```

**Step 5-2.** after_fix フェーズのメトリクスを取得する。

```bash
composer experiment:metrics -- --phase after_fix --diff-ref experiment-baseline-v1
```

**Step 5-3.** 実験記録を生成する。

```bash
composer experiment:record -- --scenario api-spec-change-status-int --write
```

**Step 5-4.** 結果を experiment/results/ に公開する。

```bash
./scripts/publish-experiment-results.sh --scenario api-spec-change-status-int
```

**Step 5-5.** 手動項目を追加する。

**Step 5-6.** 結果をコミット・プッシュする。

```bash
git add experiment/results/api-spec-change-status-int/
git commit -m "docs(experiment): publish api-spec-change-status-int results"
git push -u origin exp/api-spec-change-status-int
```

**Step 5-7.** PRを作成し、CIを確認する。

```bash
gh pr create --base main --head exp/api-spec-change-status-int \
  --title "exp: api-spec-change-status-int（improved）" \
  --body "$(cat <<'EOF'
## Summary
- タスク `status` を string から integer（0/1/2）へ変更する実験
- improved 構成（TaskService / TaskRepository に集約）

## Test plan
- [ ] GitHub Actions 4 ジョブすべて成功
- [ ] `experiment/results/api-spec-change-status-int/` に 3 フェーズ JSON + RECORD.md がある

実験用 PR。マージはしない。
EOF
)"
```

**Step 5-8.** 結果を公開ディレクトリにコピーする。

```bash
./scripts/publish-experiment-results.sh --scenario api-spec-change-status-int
```

**Step 5-9.** 結果を手動で変更し、コミット・プッシュする。

```bash
git add experiment/results/api-spec-change-status-int/RECORD.md
git commit -m "$(cat <<'EOF'
docs: fill manual experiment record for api-spec-change-status-int

Add CI, work time, commits, and notes to the manual recording table.
EOF
)"
git push origin exp/api-spec-change-status-int
```

---

## 5. 完了条件

- [ ]  GitHub Actions 4 ジョブすべて成功（`./scripts/check-quality.sh` がローカルで緑）
- [ ]  `baseline` / `after_update` / `after_fix` の 3 フェーズ JSON が `experiment/metrics/` に存在する
- [ ]  `experiment/results/` に結果がコピーされている（`publish-experiment-results.sh` 実行済み）
- [ ]  従来構成リポジトリ（`tech-update-task-app-legacy`）で同一手順を実施済み（比較実験）

## 6. 触らないファイルとその理由

| ファイル | 理由 |
| --- | --- |
| `app/Http/Controllers/API/TaskController.php` | improved 構成では Controller は HTTP 受け渡しのみとし、正規化は TaskService に集約する実験設計のため。S2 は API 一本化のため Web Controller は存在しない。S0（Blade）との差（Controller 内修正の有無）を計測する対照群でもある |
| `app/Http/Resources/TaskResource.php` | Model の `integer` cast により JSON 出力の `status` も自動的に int になるため変更不要 |
| `frontend/src/components/TaskTable.tsx` | `statusLabel(task.status)` で表示するため、`STATUS_OPTIONS` の value を int にすれば自動追従（変更不要） |
| `database/migrations/2026_05_12_052659_create_tasks_table.php` | 既存マイグレーションは変更せず、新規マイグレーションで型変更・データ移行を行うため |

## 関連

- [EXPERIMENT.md](../../EXPERIMENT.md) — 実験設計・主評価指標の定義
- [EXPERIMENT.md — メトリクス記録テンプレート](../EXPERIMENT.md#メトリクス記録テンプレート) — スプレッドシート列定義
- [api-spec-change-priority.md](./api-spec-change-priority.md) — 属性追加シナリオ（対比用）
- [db-schema-change.md](./db-schema-change.md) — クエリ変更シナリオ
