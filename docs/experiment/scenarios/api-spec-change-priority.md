# シナリオ: API 仕様変更 — 属性追加（`api-spec-change-priority`）

## 目的

REST API に新属性 `priority` を追加し、**非破壊的な仕様変更がアーキテクチャ各層にどう波及するか**を比較する。

## 想定される破壊箇所

| 構成 | 主な修正箇所 |
|------|--------------|
| 改良構成 | `TaskResource`, `StoreTaskRequest` / `UpdateTaskRequest`, `TaskService`（正規化）, テスト |
| 従来構成 | 上記に加え **Web/API の Controller 内 `normalizeTaskPayload`** も同時修正 |

**触れない（改良構成）:** Controller、Repository、Interface

## 事前条件

- `experiment-baseline-v1` タグまたは同等の CI 緑状態（**main ベースラインは 4 属性のみ**）
- メトリクス用に **`baseline` を先に取得**し、`experiment/metrics/runs/<run_id>/` が作成されていること

## 変更内容（両リポジトリで同一適用）

1. **レスポンスに `priority` フィールドを追加**（`low` / `medium` / `high`、デフォルト `medium`）
2. **マイグレーション:** `tasks` テーブルに `priority` カラム（string, default `medium`）
3. **`TaskResource`:** `priority` を JSON に含める
4. **FormRequest:** `priority` の `Rule::in([...])` を追加
5. **`TaskService::normalizeTaskPayload`:** `priority` を許可リストに追加
6. **Web フォーム:** `tasks/_form.blade.php` に select を追加（任意だが機能 parity のため推奨）
7. **テスト:** `TaskApiTest`, `TaskWebTest`, Postman コレクションの期待値を更新

## 実施手順

```bash
git checkout -b exp/api-spec-change-priority experiment-baseline-v1

composer experiment:metrics -- --phase baseline --diff-ref experiment-baseline-v1

docker compose exec app php artisan make:migration add_priority_to_tasks_table
docker compose exec app php artisan migrate

# 上記ファイルを順に編集（テスト・Postman はまだ触らない）

docker compose --profile node run --rm node sh -c "npm ci && npm run build"

composer experiment:metrics -- --phase after_update --diff-ref experiment-baseline-v1

./scripts/check-quality.sh

composer experiment:metrics -- --phase after_fix --diff-ref experiment-baseline-v1

composer experiment:record -- --scenario api-spec-change-priority --write
```

## 記録するメトリクス

- PHPUnit / Newman 通過率（`after_update` vs `after_fix`）
- PHPStan エラー件数
- 修正工数（`git.files_changed` / `lines_*`、`--diff-ref experiment-baseline-v1`）
- 主な修正ファイル一覧（メモ欄）

## 完了条件

- [ ] GitHub Actions 4 ジョブすべて成功
- [ ] `experiment/metrics/runs/<run_id>/` に 3 フェーズ JSON がある
- [ ] （任意）`composer experiment:record -- --scenario api-spec-change-priority --write`
- [ ] [docs/experiment/results/](../results/) に結果をコピー（`scripts/publish-experiment-results.sh`）
- [ ] 従来構成（`legacy-architecture` ブランチ）で同一手順を実施

## 関連

- [ARCHITECTURE_ANALYSIS.md](../../ARCHITECTURE_ANALYSIS.md) — レイヤ別分析（セクション 1）
- [api-spec-change-status-int.md](./api-spec-change-status-int.md) — 既存属性の型変更シナリオ
- [db-schema-change.md](./db-schema-change.md) — クエリ変更シナリオ
