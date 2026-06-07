# 実験記録（自動生成）

| 項目 | 値 |
|------|----|
| **run_id** | `run-20260521T061416Z` |
| **シナリオ** | `api-spec-change-priority` |
| **リポジトリ** | `legacy` |

手動項目（CI・作業時間・コミット数など）は [手動記入](#manual) の表に追記してください。 スプレッドシートへそのまま貼る場合は [TSV（全列）](#tsv) を使えます。

## 自動収集サマリー

| フェーズ | 記録時刻 | PHPUnit | Newman | PHPStan | ESLint |
|:---------|:---------|:--------|:-------|:--------|:-------|
| ベースライン | `20260521T061416Z` | 38/38 (100.0%) | 13/13 (100.0%) | 0 件 | OK |
| 更新直後 | `20260521T061445Z` | 36/38 (94.7%) | 10/13 (76.9%) | 0 件 | OK |
| 修正後 | `20260521T061458Z` | 38/38 (100.0%) | 13/13 (100.0%) | 0 件 | OK |

<a id="manual"></a>

## 手動記入（実験者が追記）

| フェーズ | CI (失敗/総数) | 作業時間 (分) | 変更ファイル | 追加行 | 削除行 | コミット数 | 手動バグ | メモ |
|:---------|:---------------|:--------------|:-------------|:-------|:-------|:-----------|:---------|:-----|
| ベースライン | | | | | | | | |
| 更新直後 | | | | | | | | |
| 修正後 | | | | | | | | |

## フェーズ別詳細

### ベースライン (`baseline`)

- **JSON:** [`baseline.json`](experiment/metrics/runs/run-20260521T061416Z/baseline.json)
- **git diff_shortstat:** `（なし）`

### 更新直後 (`after_update`)

- **JSON:** [`after_update.json`](experiment/metrics/runs/run-20260521T061416Z/after_update.json)
- **git diff_shortstat:** `（なし）`

### 修正後 (`after_fix`)

- **JSON:** [`after_fix.json`](experiment/metrics/runs/run-20260521T061416Z/after_fix.json)
- **git diff_shortstat:** `（なし）`

<a id="tsv"></a>

<details>
<summary>スプレッドシート用 TSV（全列）</summary>

```tsv
repository	scenario	phase	recorded_at	phpunit_pass	phpunit_total	phpunit_pass_rate	newman_pass	newman_total	newman_pass_rate	phpstan_errors	eslint_ok	ci_jobs_failed	ci_jobs_total	work_minutes	files_changed	lines_added	lines_deleted	commits	manual_bugs	metrics_json	notes
legacy	api-spec-change-priority	baseline	20260521T061416Z	38	38	100.0	13	13	100.0	0	1							experiment/metrics/runs/run-20260521T061416Z/baseline.json	
legacy	api-spec-change-priority	after_update	20260521T061445Z	36	38	94.74	10	13	76.92	0	1							experiment/metrics/runs/run-20260521T061416Z/after_update.json	
legacy	api-spec-change-priority	after_fix	20260521T061458Z	38	38	100.0	13	13	100.0	0	1							experiment/metrics/runs/run-20260521T061416Z/after_fix.json	
```

</details>
