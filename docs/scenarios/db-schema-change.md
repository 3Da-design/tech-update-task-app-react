# シナリオ: DB / クエリ変更（タイトル検索の大文字小文字）

## 目的

永続化層・検索クエリの変更が **Repository に集約されるか**（改良構成）と **Controller 2 箇所に分散するか**（従来構成）を比較する。

API 仕様変更（`priority` 追加）とは異なり、**主な修正箇所がクエリ層**になる点が特徴です。

## 想定される破壊箇所

| 構成 | 主な修正箇所 |
|------|--------------|
| 改良構成 | `TaskRepository::getFiltered` のみ |
| 従来構成 | `Web\TaskController` と `API\TaskController` 内の `listTasks` クエリ **両方** |

## 事前条件

- `experiment-baseline-v1` タグ（または同等の CI 緑状態の main）
- CI / ローカルテストが **PostgreSQL** で実行されていること（`LIKE` の大文字小文字挙動を開発環境と一致させる）
- `baseline` メトリクス取得済み

## 変更内容（両リポジトリで同一適用）

### 1. テストの追加（シナリオ開始時）

既存の `tests/Feature/TaskListFilterTest.php` に以下を **追加**（ベースラインの CI を一時的に赤くする想定）:

- `test_web_index_title_search_is_case_insensitive`
- `test_api_index_title_search_is_case_insensitive`

例: タイトル `Important task` に対し `?title=important` でヒットすること。

### 2. クエリの変更

タイトル部分一致を **大文字小文字を区別しない** 検索に変更する。

**改良構成（推奨実装）** — Repository のみ:

```php
$query->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($this->escapeLike($title)).'%']);
```

既存の `TaskRepository::escapeLike` を利用する。

**従来構成** — Web / API Controller の `listTasks` 内の `where('title', 'like', ...)` を同様に変更（2 ファイル）。

> PostgreSQL 専用の `ilike` でもよいが、本研究では **移植性のある LOWER 比較** を推奨（CI と Docker の一致）。

### 3. 適用順序

1. 上記テストを追加 → `after_update` メトリクス（失敗想定）
2. クエリのみ変更（Controller / Service は触らない）→ まだ失敗の可能性
3. テストが緑になるまでクエリを修正 → `after_fix` メトリクス

## 実施手順

```bash
git checkout -b exp/db-schema-change experiment-baseline-v1

composer experiment:metrics -- --phase baseline --diff-ref experiment-baseline-v1

# 1. TaskListFilterTest に case-insensitive テストを追加
composer experiment:metrics -- --phase after_update --diff-ref experiment-baseline-v1

# 2. Repository（改良）または Controller×2（従来）でクエリ修正
./scripts/check-quality.sh

# 3. after_fix 計測
composer experiment:metrics -- --phase after_fix --diff-ref experiment-baseline-v1

composer experiment:record -- --scenario db-schema-change --write
./scripts/publish-experiment-results.sh --scenario db-schema-change
```

## 記録するメトリクス（主指標）

| 優先 | 指標 | 取得元 |
|------|------|--------|
| 1 | 変更ファイル数 | `git.files_changed`（`--diff-ref experiment-baseline-v1`） |
| 2 | 追加 / 削除行数 | `git.lines_added` / `git.lines_deleted` |
| 3 | 更新直後のテスト失敗数 | `phpunit.fail` / `newman.fail`（`after_update`） |
| 4 | 作業時間（分） | 手動（テンプレート） |

**期待される差:** 従来構成は `files_changed` が改良構成より **+1（Web Controller）** 程度多い。

## 完了条件

- [ ] GitHub Actions 4 ジョブすべて成功（`after_fix`）
- [ ] `experiment/metrics/runs/<run_id>/` に 3 フェーズ JSON がある
- [ ] `experiment/results/` に結果をコピー（`scripts/publish-experiment-results.sh`）
- [ ] 改良 vs legacy で `git.files_changed` を各リポジトリの `experiment/results/` で比較
- [ ] 従来構成リポジトリ（`tech-update-task-app-legacy`）で同一手順を実施

## 関連

- [EXPERIMENT.md](../../EXPERIMENT.md) — 実験設計・主評価指標の定義
- [EXPERIMENT.md — メトリクス記録テンプレート](../EXPERIMENT.md#メトリクス記録テンプレート) — スプレッドシート列定義
- [api-spec-change-priority.md](./api-spec-change-priority.md) — 属性追加シナリオ
- [api-spec-change-status-int.md](./api-spec-change-status-int.md) — 既存属性の型変更シナリオ
