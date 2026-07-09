# シナリオ: API 仕様変更 — 属性追加（`api-spec-change-priority`）

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

タスク REST API と React SPA（`frontend/`）に新属性 `priority`（`low` / `medium` / `high`、デフォルト `medium`）を追加する実験です。既存クライアントが `priority` を送らなくても動作する**非破壊的な属性追加**であり、S0（Blade）と比べて修正がフロント／バックエンドにどう分散するかを `git_frontend` / `git_backend` で測ります。**完全版**では CRUD に加え、一覧の**表示・フィルタ（`?priority=`）・並び替え（`?priority_sort=asc|desc`）**まで S0 と parity を取ります。improved では属性の正規化は `TaskService::normalizeTaskPayload`、一覧クエリは `IndexTaskRequest` → `normalizeListFilters` → `TaskRepository::getFiltered` に集約されます。S2 のフロント修正は **`frontend/src/types.ts` の型追加を起点**として `StatusLabel` / `TaskForm` / `TaskFilterBar` / `TaskTable` / `api/tasks.ts` / `pages/TasksPage.tsx` に波及する想定で、TypeScript が型不整合を早期検出する点（H3）の観察材料になります。

---

## 1. 概要

| 項目 | 値 |
| --- | --- |
| リポジトリ | tech-update-task-app-react（S2 / improved） |
| 実験の内容 | API 仕様変更 — priority 属性追加（CRUD + 一覧列 + フィルタ + 並び替え） |
| ブランチ名 | `exp/api-spec-change-priority` |
| 参照MD | `docs/scenarios/api-spec-change-priority.md` |
| 一覧クエリパラメータ | `priority`（フィルタ）, `priority_sort`（`asc` / `desc`、デフォルト `asc` = low → medium → high） |

---

## 2. 事前条件チェック

- [ ]  `experiment-baseline-v1` または CI 緑 — ベースラインは 4 属性のみで比較の起点になる
- [ ]  Docker 起動 — migrate / PHPUnit / Newman / `check-quality.sh` がコンテナ前提のため
- [ ]  PostgreSQL（status 数値化・タイトル検索のみ） — 本シナリオの migration は標準 `Schema::table` で足りる

---

## 3. 修正対象ファイル一覧

| # | ファイルパス | 修正箇所 | フェーズ | 作業内容 | 解説 |
| --- | --- | --- | --- | --- | --- |
| 1 | `database/migrations/*_add_priority_to_tasks_table.php` | `up()` / `down()` | 2 | カラム追加 | DB 永続化 |
| 2 | `config/task.php` | 配列末尾 | 2 | `priority_values` | バリデーション・UI 共有 |
| 3 | `app/Models/Task.php` | PHPDoc / Fillable | 2 | `priority` 追加 | mass assignment |
| 4 | `app/Http/Resources/TaskResource.php` | `toArray()` | 2 | JSON に `priority` | API 契約 |
| 5 | `app/Http/Requests/StoreTaskRequest.php` | `rules()` | 2 | バリデーション | 作成時検証 |
| 6 | `app/Http/Requests/UpdateTaskRequest.php` | `rules()` | 2 | バリデーション | 更新時検証 |
| 7 | `app/Services/TaskService.php` | `normalizeTaskPayload()` | 2 | 許可リスト | CRUD 正規化 |
| 8 | `app/Services/TaskService.php` | `normalizeListFilters()` | 2 | フィルタ/ソート | 一覧正規化 |
| 9 | `app/Http/Requests/IndexTaskRequest.php` | `prepareForValidation` / `rules()` | 2 | `priority` / `priority_sort` | 一覧入力検証 |
| 10 | `app/Repositories/TaskRepository.php` | `getFiltered()` | 2 | WHERE + ORDER BY | クエリ実行 |
| 11 | `app/Repositories/Contracts/TaskRepositoryInterface.php` | PHPDoc | 2 | filters 型 | Interface 整合 |
| 12 | `frontend/src/types.ts` | `TaskPriority` 型 / `Task` / `TaskFormInput` / `TaskListQuery` | 2 | 型に priority 追加（起点） | TS 型定義（H3 の起点） |
| 13 | `frontend/src/components/StatusLabel.tsx` | `PRIORITY_OPTIONS` / `priorityLabel` 追加 | 2 | 選択肢・ラベル定義 | UI 共有の許可値 |
| 14 | `frontend/src/components/TaskForm.tsx` | `EMPTY_FORM` / `toFormInput` / select 追加 | 2 | 作成・編集フォーム | フォーム入力 |
| 15 | `frontend/src/components/TaskFilterBar.tsx` | priority フィルタ + priority_sort select | 2 | フィルタ・並び替え UI | 一覧クエリ送出 |
| 16 | `frontend/src/components/TaskTable.tsx` | thead / tbody に優先度列 | 2 | 一覧表示 | 列追加 |
| 17 | `frontend/src/api/tasks.ts` | `listTasks` params / `TaskPayload` | 2 | 送受信に priority | API 契約（フロント） |
| 18 | `frontend/src/pages/TasksPage.tsx` | `handleSubmitForm` payload | 2 | payload に priority | 作成/更新の送信 |
| 19 | `tests/Feature/TaskApiTest.php` | 各メソッド | 4 | CRUD 期待値 | API 契約 |
| 20 | `tests/Feature/TaskListFilterTest.php` | seed + 新規テスト（API） | 4 | フィルタ/ソート | 一覧クエリ（S2 は API のみ） |
| 21 | `postman/Task-API.postman_collection.json` | POST / PUT tests | 4 | アサーション | Newman |

---

## 4. 実施手順

### Phase 0: ブランチ作成

**この Phase の目的:** ベースラインから実験ブランチを切り、diff 計測の起点を固定する。

**Step 0-1.** 実験ブランチを作成する。

```bash
git checkout -b exp/api-spec-change-priority experiment-baseline-v1
```

---

### Phase 1: baseline メトリクス

**この Phase の目的:** 変更前の状態を記録する。

**Step 1-1.** baseline フェーズのメトリクスを取得する。

```bash
composer experiment:metrics -- --phase baseline --diff-ref experiment-baseline-v1
```

---

### Phase 2: 変更適用（テスト・Postman 未着手）

**この Phase の目的:** 本番コードに完全版 priority を適用し、after_update を計測できる状態にする。

**Step 2-1.** マイグレーションファイルを生成する。

```bash
docker compose exec app php artisan make:migration add_priority_to_tasks_table
```

**Step 2-2.** マイグレーション編集

- **ファイル:** `database/migrations/YYYY_MM_DD_HHMMSS_add_priority_to_tasks_table.php`
- **場所:** `up()` / `down()`
- **解説:** 未指定時 DB デフォルト `medium` を設定する。
- **変更前:**

```php
public function up(): void
{
    //
}

public function down(): void
{
    //
}
```

- **変更後:**

```php
public function up(): void
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->string('priority')->default('medium');
    });
}

public function down(): void
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropColumn('priority');
    });
}
```

**Step 2-3.** マイグレーションを実行する。

```bash
docker compose exec app php artisan migrate
```

**Step 2-4.** `config/task.php`

- **解説:** FormRequest / Blade が同じ許可値を参照する。
- **変更前:**

```php
return [
  'default_user_email' => env('DEFAULT_TASK_USER_EMAIL', 'test@example.com'),
  'status_values' => ['todo', 'in_progress', 'done'],
];
```

- **変更後:**

```php
return [
  'default_user_email' => env('DEFAULT_TASK_USER_EMAIL', 'test@example.com'),
  'status_values' => ['todo', 'in_progress', 'done'],
  'priority_values' => ['low', 'medium', 'high'],
];
```

**Step 2-5.** `app/Models/Task.php`

- **解説:** `fill()` で `priority` が保存されるようにする。
- **変更前:**

```php
/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property Carbon|null $due_date
 */
#[Fillable(['user_id', 'title', 'description', 'status', 'due_date'])]
```

- **変更後:**

```php
/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $priority
 * @property Carbon|null $due_date
 */
#[Fillable(['user_id', 'title', 'description', 'status', 'priority', 'due_date'])]
```

**Step 2-6.** `app/Http/Resources/TaskResource.php` — `toArray()` 行 21–27

- **解説:** API レスポンスに `priority` を含める。
- **変更前:**

```php
return [
  'id' => $this->id,
  'title' => $this->title,
  'status' => $this->status,
  'due_date' => $this->due_date?->format('Y-m-d'),
  'description' => $this->description,
];
```

- **変更後:**

```php
return [
  'id' => $this->id,
  'title' => $this->title,
  'status' => $this->status,
  'priority' => $this->priority,
  'due_date' => $this->due_date?->format('Y-m-d'),
  'description' => $this->description,
];
```

**Step 2-7.** `app/Http/Requests/StoreTaskRequest.php` — `rules()` 行 26–31

- **解説:** 省略時は DB デフォルト、送信時のみ検証。
- **変更後（rules 全体）:**

```php
return [
  'title' => ['required', 'string', 'max:255'],
  'description' => ['nullable', 'string'],
  'status' => ['required', 'string', Rule::in(config('task.status_values'))],
  'priority' => ['nullable', 'string', Rule::in(config('task.priority_values'))],
  'due_date' => ['nullable', 'date'],
];
```

**Step 2-8.** `app/Http/Requests/UpdateTaskRequest.php` — `rules()` 行 26–31

- **変更後（rules 全体）:**

```php
return [
  'title' => ['sometimes', 'required', 'string', 'max:255'],
  'description' => ['sometimes', 'nullable', 'string'],
  'status' => ['sometimes', 'required', 'string', Rule::in(config('task.status_values'))],
  'priority' => ['sometimes', 'nullable', 'string', Rule::in(config('task.priority_values'))],
  'due_date' => ['sometimes', 'nullable', 'date'],
];
```

**Step 2-9.** `app/Services/TaskService.php` — `normalizeTaskPayload()` 行 107–110

- **解説:** CRUD 用正規化で `priority` を通す。
- **変更前:**

```php
$allowed = ['title', 'description', 'status', 'due_date'];
$data = array_intersect_key($data, array_flip($allowed));
```

- **変更後:**

```php
$allowed = ['title', 'description', 'status', 'priority', 'due_date'];
$data = array_intersect_key($data, array_flip($allowed));
```

**Step 2-10.** `app/Services/TaskService.php` — `normalizeListFilters()` 行 72–100

- **解説:** 一覧クエリ用に `priority` フィルタと `priority_sort` を正規化する。
- **変更前（PHPDoc + メソッド）:**

```php
  /**
   * @param  array<string, mixed>  $query
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

    if (isset($query['due_date_sort']) && $query['due_date_sort'] === 'desc') {
      $filters['due_date_sort'] = 'desc';
    } elseif (isset($query['due_date_sort']) && $query['due_date_sort'] === 'asc') {
      $filters['due_date_sort'] = 'asc';
    }

    return $filters;
  }
```

- **変更後:**

```php
  /**
   * @param  array<string, mixed>  $query
   * @return array{title?: string, status?: string, priority?: string, priority_sort?: string, due_date_sort?: string}
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

    if (isset($query['priority']) && is_string($query['priority'])) {
      $priority = trim($query['priority']);
      if ($priority !== '') {
        $filters['priority'] = $priority;
      }
    }

    if (isset($query['priority_sort']) && $query['priority_sort'] === 'desc') {
      $filters['priority_sort'] = 'desc';
    } elseif (isset($query['priority_sort']) && $query['priority_sort'] === 'asc') {
      $filters['priority_sort'] = 'asc';
    }

    if (isset($query['due_date_sort']) && $query['due_date_sort'] === 'desc') {
      $filters['due_date_sort'] = 'desc';
    } elseif (isset($query['due_date_sort']) && $query['due_date_sort'] === 'asc') {
      $filters['due_date_sort'] = 'asc';
    }

    return $filters;
  }
```

**Step 2-11.** `app/Http/Requests/IndexTaskRequest.php`

- **解説:** Web/API 共通の一覧入力検証。空文字は null に正規化。
- **変更前 `prepareForValidation`:**

```php
    foreach (['title', 'status', 'due_date_sort'] as $key) {
```

- **変更後 `prepareForValidation`:**

```php
    foreach (['title', 'status', 'priority', 'priority_sort', 'due_date_sort'] as $key) {
```

- **変更前 `rules()`:**

```php
    return [
      'title' => ['nullable', 'string', 'max:255'],
      'status' => ['nullable', 'string', Rule::in(config('task.status_values'))],
      'due_date_sort' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
    ];
```

- **変更後 `rules()`:**

```php
    return [
      'title' => ['nullable', 'string', 'max:255'],
      'status' => ['nullable', 'string', Rule::in(config('task.status_values'))],
      'priority' => ['nullable', 'string', Rule::in(config('task.priority_values'))],
      'priority_sort' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
      'due_date_sort' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
    ];
```

**Step 2-12.** `app/Repositories/TaskRepository.php` — `getFiltered()` 行 11–30

- **解説:** 辞書順ではなく `CASE` で low → medium → high をソート。優先度の後に既存の期限ソートを適用。
- **変更前:**

```php
  public function getFiltered(int $userId, array $filters = []): Collection
  {
    $query = Task::query()->where('user_id', $userId);

    $title = $filters['title'] ?? null;
    if (is_string($title) && $title !== '') {
      $query->where('title', 'like', '%'.$this->escapeLike($title).'%');
    }

    $status = $filters['status'] ?? null;
    if (is_string($status) && $status !== '') {
      $query->where('status', $status);
    }

    $dueSort = $filters['due_date_sort'] ?? 'asc';
    $direction = $dueSort === 'desc' ? 'desc' : 'asc';
    $query->orderByRaw('due_date IS NULL DESC')->orderBy('due_date', $direction)->orderBy('id');

    /** @var Collection<int, Task> */
    return $query->get();
  }
```

- **変更後:**

```php
  public function getFiltered(int $userId, array $filters = []): Collection
  {
    $query = Task::query()->where('user_id', $userId);

    $title = $filters['title'] ?? null;
    if (is_string($title) && $title !== '') {
      $query->where('title', 'like', '%'.$this->escapeLike($title).'%');
    }

    $status = $filters['status'] ?? null;
    if (is_string($status) && $status !== '') {
      $query->where('status', $status);
    }

    $priority = $filters['priority'] ?? null;
    if (is_string($priority) && $priority !== '') {
      $query->where('priority', $priority);
    }

    $prioritySort = $filters['priority_sort'] ?? 'asc';
    $priorityDirection = $prioritySort === 'desc' ? 'desc' : 'asc';
    $query->orderByRaw(
      "CASE priority WHEN 'low' THEN 1 WHEN 'medium' THEN 2 WHEN 'high' THEN 3 ELSE 4 END {$priorityDirection}"
    );

    $dueSort = $filters['due_date_sort'] ?? 'asc';
    $direction = $dueSort === 'desc' ? 'desc' : 'asc';
    $query->orderByRaw('due_date IS NULL DESC')->orderBy('due_date', $direction)->orderBy('id');

    /** @var Collection<int, Task> */
    return $query->get();
  }
```

**Step 2-13.** `app/Repositories/Contracts/TaskRepositoryInterface.php` — PHPDoc 行 10–14

- **変更前:**

```php
  /**
   * @param  array{title?: string, status?: string, due_date_sort?: string}  $filters
   * @return Collection<int, Task>
   */
```

- **変更後:**

```php
  /**
   * @param  array{title?: string, status?: string, priority?: string, priority_sort?: string, due_date_sort?: string}  $filters
   * @return Collection<int, Task>
   */
```

**Step 2-14.** `frontend/src/types.ts` — priority の型を追加する（**フロント修正の起点**）。

- **解説:** `TaskPriority` を定義し、`Task` / `TaskFormInput` / `TaskListQuery` に priority を通す。ここを直すと TypeScript が未対応の各コンポーネントで型エラーを出し、修正箇所を機械的に洗い出せる（H3 の観察材料）。
- **変更前:**

```ts
export type TaskStatus = 'todo' | 'in_progress' | 'done';

export interface Task {
  id: number;
  title: string;
  description: string | null;
  status: TaskStatus;
  due_date: string | null;
}

export interface TaskFormInput {
  title: string;
  description: string;
  status: TaskStatus;
  due_date: string;
}

export interface TaskListQuery {
  title?: string;
  status?: TaskStatus | '';
  due_date_sort?: 'asc' | 'desc' | '';
}
```

- **変更後:**

```ts
export type TaskStatus = 'todo' | 'in_progress' | 'done';
export type TaskPriority = 'low' | 'medium' | 'high';

export interface Task {
  id: number;
  title: string;
  description: string | null;
  status: TaskStatus;
  priority: TaskPriority;
  due_date: string | null;
}

export interface TaskFormInput {
  title: string;
  description: string;
  status: TaskStatus;
  priority: TaskPriority;
  due_date: string;
}

export interface TaskListQuery {
  title?: string;
  status?: TaskStatus | '';
  priority?: TaskPriority | '';
  priority_sort?: 'asc' | 'desc' | '';
  due_date_sort?: 'asc' | 'desc' | '';
}
```

**Step 2-15.** `frontend/src/components/StatusLabel.tsx` — priority の選択肢・ラベルを追加する。

- **解説:** `STATUS_OPTIONS` と対になる `PRIORITY_OPTIONS` / `priorityLabel` を定義し、フォーム・フィルタ・一覧表示で共有する。
- **import 変更後:**

```ts
import type { TaskStatus, TaskPriority } from '../types';
```

- **ファイル末尾に追加:**

```ts
export const PRIORITY_OPTIONS: { value: TaskPriority; label: string }[] = [
  { value: 'low', label: '低' },
  { value: 'medium', label: '中' },
  { value: 'high', label: '高' },
];

export function priorityLabel(priority: TaskPriority): string {
  return PRIORITY_OPTIONS.find((option) => option.value === priority)?.label ?? priority;
}
```

**Step 2-16.** `frontend/src/components/TaskForm.tsx` — 作成・編集フォームに priority を追加する。

- **解説:** 既定値 `medium`、編集時は task の値を反映し、status select と同形の priority select を追加する。
- **import 変更後:**

```ts
import { STATUS_OPTIONS, PRIORITY_OPTIONS } from './StatusLabel';
```

- **`EMPTY_FORM` 変更後:**

```ts
const EMPTY_FORM: TaskFormInput = {
  title: '',
  description: '',
  status: 'todo',
  priority: 'medium',
  due_date: '',
};
```

- **`toFormInput` の return 変更後:**

```ts
  return {
    title: task.title,
    description: task.description ?? '',
    status: task.status,
    priority: task.priority,
    due_date: task.due_date ?? '',
  };
```

- **status の `app-form-field`（74–89 行目）の直後に priority フィールドを挿入:**

```tsx
      <div className="app-form-field">
        <label htmlFor="priority">優先度</label>
        <select
          id="priority"
          className="app-input"
          value={form.priority}
          onChange={(event) => setForm({ ...form, priority: event.target.value as TaskFormInput['priority'] })}
        >
          {PRIORITY_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
        {errors.priority && <p className="app-error">{errors.priority.join(' ')}</p>}
      </div>
```

**Step 2-17.** `frontend/src/components/TaskFilterBar.tsx` — priority フィルタと priority_sort を追加する。

- **解説:** status フィルタと同形の priority フィルタ、期限日ソートと同形の priority ソートを追加し、`onApply` に含める。
- **import 変更後:**

```ts
import type { TaskListQuery, TaskStatus, TaskPriority } from '../types';
import { STATUS_OPTIONS, PRIORITY_OPTIONS } from './StatusLabel';
```

- **型エイリアスと state 追加後（`type StatusFilter` の直後、および `useState` 群）:**

```ts
type StatusFilter = TaskStatus | '';
type PriorityFilter = TaskPriority | '';
type SortFilter = 'asc' | 'desc' | '';
```

```ts
  const [status, setStatus] = useState<StatusFilter>(initialQuery.status ?? '');
  const [priority, setPriority] = useState<PriorityFilter>(initialQuery.priority ?? '');
  const [prioritySort, setPrioritySort] = useState<SortFilter>(initialQuery.priority_sort ?? '');
  const [dueDateSort, setDueDateSort] = useState<SortFilter>(initialQuery.due_date_sort ?? '');
```

- **`handleSubmit` の `onApply` 変更後:**

```ts
    onApply({ title, status, priority, priority_sort: prioritySort, due_date_sort: dueDateSort });
```

- **status フィルタ `app-form-field`（38–53 行目）の直後に priority フィルタと priority ソートを挿入:**

```tsx
      <div className="app-form-field">
        <label htmlFor="filter-priority">優先度</label>
        <select
          id="filter-priority"
          className="app-input"
          value={priority}
          onChange={(event) => setPriority(event.target.value as PriorityFilter)}
        >
          <option value="">すべて</option>
          {PRIORITY_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </div>

      <div className="app-form-field">
        <label htmlFor="filter-priority-sort">優先度ソート</label>
        <select
          id="filter-priority-sort"
          className="app-input"
          value={prioritySort}
          onChange={(event) => setPrioritySort(event.target.value as SortFilter)}
        >
          <option value="">指定なし</option>
          <option value="asc">昇順</option>
          <option value="desc">降順</option>
        </select>
      </div>
```

**Step 2-18.** `frontend/src/components/TaskTable.tsx` — 一覧に優先度列を追加する。

- **解説:** ステータス列の直後に優先度列（`priorityLabel` 表示）を追加する。
- **import 変更後:**

```ts
import { statusLabel, priorityLabel } from './StatusLabel';
```

- **thead 変更後（ステータスの直後に優先度）:**

```tsx
          <th>タイトル</th>
          <th>ステータス</th>
          <th>優先度</th>
          <th>期限日</th>
          <th>説明</th>
          <th aria-label="操作" />
```

- **tbody 行 変更後（ステータスセルの直後に優先度セル）:**

```tsx
            <td>{task.title}</td>
            <td>{statusLabel(task.status)}</td>
            <td>{priorityLabel(task.priority)}</td>
            <td>{task.due_date ?? '-'}</td>
            <td>{task.description ?? '-'}</td>
```

**Step 2-19.** `frontend/src/api/tasks.ts` — 送受信に priority を通す。

- **解説:** 一覧クエリに `priority` / `priority_sort` を積み、`TaskPayload` に priority を追加する。
- **`listTasks` の params 追加後:**

```ts
  if (query.title) params.title = query.title;
  if (query.status) params.status = query.status;
  if (query.priority) params.priority = query.priority;
  if (query.priority_sort) params.priority_sort = query.priority_sort;
  if (query.due_date_sort) params.due_date_sort = query.due_date_sort;
```

- **`TaskPayload` 変更後:**

```ts
export interface TaskPayload {
  title: string;
  description: string | null;
  status: Task['status'];
  priority: Task['priority'];
  due_date: string | null;
}
```

**Step 2-20.** `frontend/src/pages/TasksPage.tsx` — 作成/更新 payload に priority を含める。

- **解説:** `handleSubmitForm` の payload 構築に priority を追加する。
- **変更後:**

```ts
    const payload = {
      title: input.title,
      description: input.description === '' ? null : input.description,
      status: input.status,
      priority: input.priority,
      due_date: input.due_date === '' ? null : input.due_date,
    };
```

**Step 2-21.** フロントエンド資産をビルドする。

```bash
composer npm:docker-build
```

---

### Phase 3: after_update メトリクス

**この Phase の目的:** テスト未修正の中間状態を計測する。

**Step 3-1.**

```bash
git add -A
git commit -m "feat: add priority attribute with list filter and sort (tests not updated)"
```

**Step 3-2.**

```bash
composer experiment:metrics -- --phase after_update --diff-ref experiment-baseline-v1
```

> **補足:** 非破壊的 CRUD 変更のみなら fail 0 の可能性あり。`TaskListFilterTest` の seed に `priority` が無いと DB エラーになる場合は fail > 0。いずれも記録する。
> 

---

### Phase 4: テスト・Postman 修正 → CI 緑

**この Phase の目的:** 完全版仕様を CI で固定し after_fix を計測可能にする。

**Step 4-1.** `tests/Feature/TaskApiTest.php`

- **解説:** CRUD の priority 契約を固定。
- **store テスト変更後:**

```php
    $response->assertCreated();
    $response->assertJsonPath('data.title', 'New Task');
    $response->assertJsonPath('data.priority', 'medium');
    $this->assertDatabaseHas('tasks', ['title' => 'New Task', 'priority' => 'medium']);
```

- **update テスト変更後:**

```php
    $response = $this->actingAs($this->user)->putJson("/api/tasks/{$task->id}", [
      'title' => 'Updated',
      'status' => 'in_progress',
      'priority' => 'high',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.title', 'Updated');
    $response->assertJsonPath('data.priority', 'high');
```

- **末尾に追加:**

```php
  /** POST priority 不正 → 422 */
  public function test_store_with_invalid_priority_returns_422(): void
  {
    $response = $this->actingAs($this->user)->postJson('/api/tasks', [
      'title' => 't',
      'status' => 'todo',
      'priority' => 'urgent',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('priority');
  }
```

**Step 4-2.** `tests/Feature/TaskListFilterTest.php`

- **解説:** 既存 seed は全件 `priority => 'medium'` にし既存 due_date テストを維持。priority 専用 seed でフィルタ/ソートを検証。S2 は一覧フィルタが API のみのため、追加テストは API（`getJson`）のみとする（Web ルート `/tasks` は存在しない）。
- **`seedTasks()` 各 create に追加:**

```php
      'priority' => 'medium',
```

- **ファイル末尾（`seedTasks()` の後）に private メソッド追加:**

```php
  private function seedTasksWithDistinctPriorities(): void
  {
    Task::query()->create([
      'user_id' => $this->user->id,
      'title' => 'Foo task',
      'description' => null,
      'status' => 'todo',
      'priority' => 'low',
      'due_date' => null,
    ]);

    Task::query()->create([
      'user_id' => $this->user->id,
      'title' => 'Bar task',
      'description' => null,
      'status' => 'done',
      'priority' => 'high',
      'due_date' => null,
    ]);

    Task::query()->create([
      'user_id' => $this->user->id,
      'title' => 'Baz task',
      'description' => null,
      'status' => 'in_progress',
      'priority' => 'medium',
      'due_date' => null,
    ]);
  }
```

- **新規テスト 3 本追加（API、`test_api_index_sorts_due_date_desc` の後）:**

```php
  public function test_api_index_filters_by_priority(): void
  {
    $this->seedTasksWithDistinctPriorities();

    $response = $this->actingAs($this->user)->getJson('/api/tasks?priority=high');

    $response->assertOk();
    $titles = collect($response->json('data'))->pluck('title')->all();
    $this->assertSame(['Bar task'], $titles);
  }

  public function test_api_index_sorts_priority_asc(): void
  {
    $this->seedTasksWithDistinctPriorities();

    $response = $this->actingAs($this->user)->getJson('/api/tasks?priority_sort=asc&due_date_sort=asc');

    $response->assertOk();
    $titles = collect($response->json('data'))->pluck('title')->all();
    $this->assertSame(['Foo task', 'Baz task', 'Bar task'], $titles);
  }

  public function test_api_index_sorts_priority_desc(): void
  {
    $this->seedTasksWithDistinctPriorities();

    $response = $this->actingAs($this->user)->getJson('/api/tasks?priority_sort=desc&due_date_sort=asc');

    $response->assertOk();
    $titles = collect($response->json('data'))->pluck('title')->all();
    $this->assertSame(['Bar task', 'Baz task', 'Foo task'], $titles);
  }
```

**Step 4-3.** `postman/Task-API.postman_collection.json` 

| **変更内容** | **リクエスト名** | **行** |
| --- | --- | --- |
| POST test スクリプト | `POST /api/tasks (valid)` | **205–210**（207–208 間に追加） |
| PUT body | `PUT /api/tasks/{{taskId}}` | **255** |
| PUT test スクリプト | `PUT /api/tasks/{{taskId}}` | **265**（続けて追加） |
- **POST test スクリプト変更後:**

```json
"pm.test('status 201', () => pm.response.to.have.status(201));",
"const json = pm.response.json();",
"pm.test('priority defaults to medium', () => {",
"  pm.expect(json.data.priority).to.eql('medium');",
"});",
"const id = json.data && json.data.id;",
"if (id) {",
"  pm.collectionVariables.set('taskId', String(id));",
"}"
```

- **PUT body 変更後:**

```json
"raw": "{\n  \"title\": \"Updated from Postman\",\n  \"status\": \"in_progress\",\n  \"priority\": \"high\"\n }"
```

- **PUT test スクリプト変更後:**

```json
"pm.test('status 200', () => pm.response.to.have.status(200));",
"pm.test('response has priority', () => {",
"  pm.expect(pm.response.json().data.priority).to.eql('high');",
"});"
```

**Step 4-4.** CI 相当の品質チェックを実行し、すべて緑にする。

```bash
./scripts/check-quality.sh
```

---

### Phase 5: after_fix メトリクス・記録

**Step 5-1.** 変更をコミットする。

```bash
git add -A
git commit -m "test: update tests and Postman for priority (full list support)"
```

**Step 5-2.** after_fix フェーズのメトリクスを取得する。

```bash
composer experiment:metrics -- --phase after_fix --diff-ref experiment-baseline-v1
```

**Step 5-3.** 実験記録を書き込む。

```bash
composer experiment:record -- --scenario api-spec-change-priority --write
```

**Step 5-4.** 結果を公開ディレクトリにコピーする。

```bash
./scripts/publish-experiment-results.sh --scenario api-spec-change-priority
```

**Step 5-5.** 結果をコミット・プッシュする。

```bash
git add experiment/results/api-spec-change-priority/
git commit -m "$(cat <<'EOF'
docs: publish experiment results for api-spec-change-priority

Record baseline, after_update, and after_fix metrics for the full
priority attribute scenario on the improved architecture.
EOF
)"
git push -u origin exp/api-spec-change-priority
```

**Step 5-6.** PRを作ってCIを確認する。

```bash
gh pr create --base main --head exp/api-spec-change-priority \
  --title "exp: api-spec-change-priority" \
  --body "実験用。マージはしない。"
```

**Step 5-7.** 結果を公開ディレクトリにコピーする。

```bash
./scripts/publish-experiment-results.sh --scenario api-spec-change-priority
```

**Step 5-8.** 結果を手動で変更し、コミット・プッシュする。

```bash
git add experiment/results/api-spec-change-priority/RECORD.md
git commit -m "$(cat <<'EOF'
docs: fill manual experiment record for api-spec-change-priority
Add CI, work time, commits, and notes to the manual recording table.
EOF
)"
git push origin exp/api-spec-change-priority
```

---

## 5. 完了条件

- [ ]  GitHub Actions 4 ジョブすべて成功
- [ ]  `experiment/metrics/runs/<run_id>/` に 3 フェーズ JSON がある
- [ ]  API / DB / React フォーム（`TaskForm`）で `priority` が動作する
- [ ]  一覧（`TaskTable`）に優先度列が表示される
- [ ]  `?priority=high` で API 一覧がフィルタされ、`TaskFilterBar` が反映する
- [ ]  `?priority_sort=asc|desc` で API 一覧が並び替えられ、`TaskFilterBar` が反映する
- [ ]  `npm run build`（`composer npm:docker-build`）が型エラーなく通る
- [ ]  `experiment/results/` に結果がコピーされている

---

## 6. 触らないファイルとその理由

| ファイル | 理由 |
| --- | --- |
| `app/Http/Controllers/API/TaskController.php` | index は `IndexTaskRequest` → `TaskService` 経由。一覧クエリロジック不要。S2 は API 一本化のため Web Controller は存在しない |
| `normalizeTaskPayload`（Controller 内） | improved では Service 層が担当（S0 Blade も同様。属性追加でも Controller は不変） |

**S0（Blade）との期待差:** バックエンド修正は 3 スタック共通（Repository + Service + IndexTaskRequest ほか）。フロント修正は S0 が Blade 2 ファイル、S2 が React 7 ファイル（`types.ts` 起点 + 6 コンポーネント/モジュール）に現れ、`git_frontend` / `git_backend` で比較する。TypeScript の型追加が波及検出の起点になる（H3）。

## 関連

- [EXPERIMENT.md](../../EXPERIMENT.md) — 実験設計・主評価指標の定義
- [EXPERIMENT.md — メトリクス記録テンプレート](../EXPERIMENT.md#メトリクス記録テンプレート) — スプレッドシート列定義
- [api-spec-change-status-int.md](./api-spec-change-status-int.md) — 既存属性の型変更シナリオ
- [db-schema-change.md](./db-schema-change.md) — クエリ変更シナリオ
