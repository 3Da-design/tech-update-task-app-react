# 実験記録（自動生成）

| 項目 | 値 |
|------|----|
| **run_id** | `run-20260729T020328Z` |
| **シナリオ** | `api-spec-change-status-int` |
| **リポジトリ** | `stack-s2` |

手動項目（CI・作業時間・コミット数など）は [手動記入](#manual) の表に追記してください。 スプレッドシートへそのまま貼る場合は [TSV（全列）](#tsv) を使えます。

**修正工数:** 主指標は `git_app`（`experiment/results/`・`experiment/metrics/` を除外したアプリ差分）。 `git` は実験メタデータ（結果 JSON 等）を含む参考値です。

## 自動収集サマリー

| フェーズ | 記録時刻 | PHPUnit | Newman | PHPStan | ESLint |
|:---------|:---------|:--------|:-------|:--------|:-------|
| ベースライン | `20260729T020328Z` | 21/21 (100.0%) | 13/13 (100.0%) | 0 件 | OK |
| 更新直後 | `20260729T020828Z` | 12/21 (57.1%) | 10/13 (76.9%) | 0 件 | OK |
| 修正後 | `20260729T021105Z` | 21/21 (100.0%) | 13/13 (100.0%) | 0 件 | OK |

<a id="manual"></a>

## 手動記入（実験者が追記）

| フェーズ | CI (失敗/総数) | 作業時間 (分) | アプリ変更ファイル | アプリ追加行 | アプリ削除行 | コミット数 | 手動バグ | メモ |
|:---------|:---------------|:--------------|:-------------------|:-------------|:-------------|:-----------|:---------|:-----|
| ベースライン | 0/4 | 6 | 0 | 0 | 0 | 1 | 0 | tag（`dc5cae1`）と差分ゼロの anchor コミット（`6632553`）。CI 4ジョブ緑。事前に dev DB を `migrate:fresh --seed` でリセット（前回実行 2026-07-25 のマイグレーションが `tasks.status` を smallint のまま残していたため）。 |
| 更新直後 | 2/4 | 5 | 14 | 72 | 20 | 1 | 0 | status を int 化（migration・config・Model・FormRequest×3・TaskService・TaskRepository・Interface・React 5ファイル）。テスト/Postman は意図的に未更新。PHP Tests（21→12/21）・API Tests(Newman)（13→10/13）が失敗、PHP Quality・Frontend は緑。 |
| 修正後 | 0/4 | 4 | 17 | 85 | 33 | 1 | 0 | テスト/Postman を int 対応に更新し復旧（PHPUnit 21/21・Newman 13/13・PHPStan 0件・ESLint OK・Pint PASS）。CI 4ジョブ緑。H3 の詳細は下記「型検査による早期検出」節。 |

> **作業時間の但し書き:** 上表の作業時間は Claude Code による自動実行の実測経過時間（CI 待ちを含む、コミット時刻から算出）であり、人間の修正工数ではない。スタック間比較に用いる場合は同一の実行主体で揃えること。主指標は `git_app` の変更ファイル数・行数。

<a id="h3"></a>

## 型検査による早期検出（仮説 H3 の観察）

`frontend/src/types.ts` の `TaskStatus` を `'todo' | 'in_progress' | 'done'` → `0 | 1 | 2` の**1行だけ**変更した時点で `tsc --noEmit` を実行し、他フロントファイルを未修正のまま検出内容を記録した。

**検出: 3 ファイル / 7 件**

| ファイル:行 | エラー | 内容 |
|---|---|---|
| `api/tasks.ts:7` | TS2322 | `params.status = query.status` — `number` を `Record<string, string>` に代入不可 |
| `components/StatusLabel.tsx:4` | TS2322 | `STATUS_OPTIONS` の `value: 'todo'` |
| `components/StatusLabel.tsx:5` | TS2322 | `STATUS_OPTIONS` の `value: 'in_progress'` |
| `components/StatusLabel.tsx:6` | TS2322 | `STATUS_OPTIONS` の `value: 'done'` |
| `components/StatusLabel.tsx:10` | TS2322 | `statusLabel()` の `?? status` が `string \| number` になり戻り値型 `string` に不適合 |
| `components/TaskForm.tsx:9` | TS2322 | `EMPTY_FORM.status: 'todo'` |
| `components/TaskForm.tsx:80` | TS2352 | select の `onChange` で `event.target.value as TaskStatus`（`string` → `0\|1\|2` は重なりなし） |

**未検出（H3 の限界）: 1 箇所**

- `components/TaskFilterBar.tsx:44` の `setStatus(event.target.value as StatusFilter)` は型エラーにならなかった。`StatusFilter = TaskStatus | ''` に `''` が含まれるため `string` と型が重なり、`as` キャストが不整合を吸収した。手順書に従って `Number()` 変換へ修正したが、**型検査では見つけられず手順書の指示が無ければ見落とす箇所**である。
- `components/TaskTable.tsx` は `statusLabel(task.status)` 経由のため自動追従（変更不要、検出も不要）。

**含意:** TypeScript は string→int の破壊的変更に対し、値リテラル・代入・戻り値型の不整合を機械的に洗い出せた（S1 の素の HTML+JS では実行時まで露見しない）。一方で `as` キャストが介在する境界（DOM イベント値）は型検査をすり抜けるため、フロント側の変更点をすべて型が保証するわけではない。

## フェーズ別詳細

### ベースライン (`baseline`)

- **JSON:** [`baseline.json`](experiment/metrics/runs/run-20260729T020328Z/baseline.json)
- **git diff_ref:** `experiment-baseline-v1`
- **git_app（アプリ修正工数・主指標）:** 0 files, +0 / -0 (`（なし）`)
- **git_frontend（フロント別・第2章）:** 0 files, +0 / -0 (`（なし）`)
- **git_backend（バックエンド別・第2章）:** 0 files, +0 / -0 (`（なし）`)
- **git（実験メタデータ込み）:** 0 files, +0 / -0 (`（なし）`)

### 更新直後 (`after_update`)

- **JSON:** [`after_update.json`](experiment/metrics/runs/run-20260729T020328Z/after_update.json)
- **git diff_ref:** `experiment-baseline-v1`
- **git_app（アプリ修正工数・主指標）:** 14 files, +72 / -20 (` 14 files changed, 72 insertions(+), 20 deletions(-)`)
- **git_frontend（フロント別・第2章）:** 5 files, +12 / -9 (` 5 files changed, 12 insertions(+), 9 deletions(-)`)
- **git_backend（バックエンド別・第2章）:** 9 files, +60 / -11 (` 9 files changed, 60 insertions(+), 11 deletions(-)`)
- **git（実験メタデータ込み）:** 14 files, +72 / -20 (` 14 files changed, 72 insertions(+), 20 deletions(-)`)

### 修正後 (`after_fix`)

- **JSON:** [`after_fix.json`](experiment/metrics/runs/run-20260729T020328Z/after_fix.json)
- **git diff_ref:** `experiment-baseline-v1`
- **git_app（アプリ修正工数・主指標）:** 17 files, +85 / -33 (` 17 files changed, 85 insertions(+), 33 deletions(-)`)
- **git_frontend（フロント別・第2章）:** 5 files, +12 / -9 (` 5 files changed, 12 insertions(+), 9 deletions(-)`)
- **git_backend（バックエンド別・第2章）:** 11 files, +71 / -22 (` 11 files changed, 71 insertions(+), 22 deletions(-)`)
- **git（実験メタデータ込み）:** 17 files, +85 / -33 (` 17 files changed, 85 insertions(+), 33 deletions(-)`)

<a id="tsv"></a>

<details>
<summary>スプレッドシート用 TSV（全列）</summary>

```tsv
repository	scenario	phase	recorded_at	phpunit_pass	phpunit_total	phpunit_pass_rate	newman_pass	newman_total	newman_pass_rate	phpstan_errors	eslint_ok	ci_jobs_failed	ci_jobs_total	work_minutes	app_files_changed	app_lines_added	app_lines_deleted	frontend_files_changed	frontend_lines_added	frontend_lines_deleted	backend_files_changed	backend_lines_added	backend_lines_deleted	meta_files_changed	meta_lines_added	meta_lines_deleted	commits	manual_bugs	metrics_json	notes
stack-s2	api-spec-change-status-int	baseline	20260729T020328Z	21	21	100.0	13	13	100.0	0	1				0	0	0	0	0	0	0	0	0	0	0	0			experiment/metrics/runs/run-20260729T020328Z/baseline.json	
stack-s2	api-spec-change-status-int	after_update	20260729T020828Z	12	21	57.14	10	13	76.92	0	1				14	72	20	5	12	9	9	60	11	14	72	20			experiment/metrics/runs/run-20260729T020328Z/after_update.json	 14 files changed, 72 insertions(+), 20 deletions(-)
stack-s2	api-spec-change-status-int	after_fix	20260729T021105Z	21	21	100.0	13	13	100.0	0	1				17	85	33	5	12	9	11	71	22	17	85	33			experiment/metrics/runs/run-20260729T020328Z/after_fix.json	 17 files changed, 85 insertions(+), 33 deletions(-)
```

</details>
