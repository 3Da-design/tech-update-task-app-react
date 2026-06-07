# シナリオ: API 仕様変更 — 既存属性の型変更（`api-spec-change-status-int`）

## 目的

既存フィールド `status` を string（`todo` / `in_progress` / `done`）から integer（`0` / `1` / `2`）へ変更し、**破壊的な API 仕様変更の波及範囲**を、属性追加（`api-spec-change-priority`）と比較する。

## 想定される破壊箇所

| 構成 | 主な修正箇所 |
|------|--------------|
| 改良構成 | `TaskResource`, FormRequest×3, `TaskService`, `config/task.php`, migration（データ移行）, `TaskRepository::getFiltered`（status フィルタ）, テスト, Web View |
| 従来構成 | 上記に加え **Web/API Controller 内の normalize / バリデーション** も同時修正 |

**触れない（改良構成）:** Controller、Interface のメソッドシグネチャ

## 事前条件

- `experiment-baseline-v1` タグまたは同等の CI 緑状態
- `baseline` メトリクス取得済み

## 変更内容（両リポジトリで同一適用）

### 1. マッピング定義

`config/task.php` を int ベースに変更（例）:

```php
'status_values' => [0, 1, 2],
'status_labels' => [
    0 => 'todo',
    1 => 'in_progress',
    2 => 'done',
],
```

### 2. 永続化

- マイグレーション: `tasks.status` を string → integer
- **既存行のデータ移行**（`todo`→0, `in_progress`→1, `done`→2）
- `Task` Model: `'status' => 'integer'` cast、PHPDoc 更新

### 3. HTTP 境界

- **`TaskResource`:** `status` を整数で返却
- **`StoreTaskRequest` / `UpdateTaskRequest`:** `integer` + `Rule::in([0, 1, 2])`
- **`IndexTaskRequest`:** 一覧フィルタ `?status=` も int 化

### 4. ユースケース・永続化

- **`TaskService::normalizeTaskPayload` / `normalizeListFilters`:** int 型の許可・正規化
- **`TaskRepository::getFiltered`:** `where('status', $status)` が int 比較になるよう追随

### 5. Web

- `tasks/_form.blade.php`, `tasks/index.blade.php` — option value を int、表示はラベル

### 6. テスト・Postman

- 既存の **全 status アサーション**を int 期待値に書き換え

## 実施手順

```bash
git checkout -b exp/api-spec-change-status-int experiment-baseline-v1

composer experiment:metrics -- --phase baseline --diff-ref experiment-baseline-v1

# 1. マイグレーション作成（データ移行含む）・適用
docker compose exec app php artisan make:migration change_status_to_integer_on_tasks_table
docker compose exec app php artisan migrate

# 2. config / Resource / Request×3 / Service / Model / Repository を編集
#    （テスト・Postman・View はまだ触らない）

docker compose --profile node run --rm node sh -c "npm ci && npm run build"

composer experiment:metrics -- --phase after_update --diff-ref experiment-baseline-v1

# 3. テスト・Postman・View を修正して CI 緑に
./scripts/check-quality.sh

composer experiment:metrics -- --phase after_fix --diff-ref experiment-baseline-v1

composer experiment:record -- --scenario api-spec-change-status-int --write
```

## 記録するメトリクス

- PHPUnit / Newman 通過率（`after_update` vs `after_fix`）
- **`after_fix` の `git.files_changed`**（priority シナリオとの比較）
- 作業時間（分）

## 完了条件

- [ ] GitHub Actions 4 ジョブすべて成功
- [ ] 3 フェーズ JSON がある
- [ ] 従来構成で同一手順を実施
- [ ] [COMPARISON.md](../results/COMPARISON.md) に improved vs legacy を追記

## 関連

- [ARCHITECTURE_ANALYSIS.md](../../ARCHITECTURE_ANALYSIS.md) — レイヤ別分析（セクション 2）
- [api-spec-change-priority.md](./api-spec-change-priority.md) — 属性追加シナリオ（対比用）
