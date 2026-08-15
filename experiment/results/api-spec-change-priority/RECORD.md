# 実験記録（自動生成）

| 項目 | 値 |
|------|----|
| **run_id** | `run-20260805T235627Z` |
| **シナリオ** | `api-spec-change-priority` |
| **リポジトリ** | `stack-s2` |

手動項目（CI・作業時間・コミット数など）は [手動記入](#manual) の表に追記してください。 スプレッドシートへそのまま貼る場合は [TSV（全列）](#tsv) を使えます。

**修正工数:** 主指標は `git_app`（`experiment/results/`・`experiment/metrics/` を除外したアプリ差分）。 `git` は実験メタデータ（結果 JSON 等）を含む参考値です。

## 自動収集サマリー

| フェーズ | 記録時刻 | PHPUnit | Newman | PHPStan | ESLint |
|:---------|:---------|:--------|:-------|:--------|:-------|
| ベースライン | `20260805T235627Z` | 21/21 (100.0%) | 13/13 (100.0%) | 0 件 | OK |
| 更新直後 | `20260806T005527Z` | 21/21 (100.0%) | 13/13 (100.0%) | 0 件 | OK |
| 修正後 | `20260806T010556Z` | 25/25 (100.0%) | 15/15 (100.0%) | 0 件 | OK |

<a id="manual"></a>

## 手動記入（実験者が追記）

| フェーズ | CI (失敗/総数) | 作業時間 (分) | アプリ変更ファイル | アプリ追加行 | アプリ削除行 | コミット数 | 手動バグ | メモ |
|:---------|:---------------|:--------------|:-------------------|:-------------|:-------------|:-----------|:---------|:-----|
| ベースライン | 0/4 | 5 | 0 | 0 | 0 | 1 | 0 | baseline anchor コミットのみ（`8d6e4f0`）。4 属性のみの起点。 |
| 更新直後 | 1/4 | 54 | 17 | 138 | 11 | 1 | 1 | `f933a59`。CI は PHP Quality のみ失敗（PHPStan 4 件）。原因は `TaskRepository::getFiltered` の `$filters['prioirty']` タイプミス 1 件で、priority フィルタが無効化されていた。PHPUnit / Newman は priority のテスト未追加のため 100% のまま通過し、型検査だけが検出（H3 の観察材料）。 |
| 修正後 | 0/4 | 38 | 20 | 229 | 14 | 1 | 0 | `6740897`。テスト（PHPUnit 21→25）・Postman（13→15 assertions）を priority 仕様に追従させ、上記タイプミスも修正。CI 4 ジョブすべて成功（run `31061794008`）。別途 `4b9bece` で結果 JSON を公開（アプリ差分なし）。 |

**注記:** 自動収集サマリーの「更新直後 / PHPStan 0 件」は `scripts/collect-experiment-metrics.sh` が stderr の `[ERROR]` 行のみを数えており、PHPStan が stdout に出す検出結果を拾えていないための表示上の 0 です。実際は 4 件で、`after_update.json` の `phpstan.exit_code = 1` / `ok = false` が正しい信号です。

## フェーズ別詳細

### ベースライン (`baseline`)

- **JSON:** [`baseline.json`](experiment/metrics/runs/run-20260805T235627Z/baseline.json)
- **git diff_ref:** `experiment-baseline-v1`
- **git_app（アプリ修正工数・主指標）:** 0 files, +0 / -0 (`（なし）`)
- **git_frontend（フロント別・第2章）:** 0 files, +0 / -0 (`（なし）`)
- **git_backend（バックエンド別・第2章）:** 0 files, +0 / -0 (`（なし）`)
- **git（実験メタデータ込み）:** 0 files, +0 / -0 (`（なし）`)

### 更新直後 (`after_update`)

- **JSON:** [`after_update.json`](experiment/metrics/runs/run-20260805T235627Z/after_update.json)
- **git diff_ref:** `experiment-baseline-v1`
- **git_app（アプリ修正工数・主指標）:** 17 files, +138 / -11 (` 17 files changed, 138 insertions(+), 11 deletions(-)`)
- **git_frontend（フロント別・第2章）:** 7 files, +80 / -6 (` 7 files changed, 80 insertions(+), 6 deletions(-)`)
- **git_backend（バックエンド別・第2章）:** 10 files, +58 / -5 (` 10 files changed, 58 insertions(+), 5 deletions(-)`)
- **git（実験メタデータ込み）:** 20 files, +171 / -12 (` 20 files changed, 171 insertions(+), 12 deletions(-)`)

### 修正後 (`after_fix`)

- **JSON:** [`after_fix.json`](experiment/metrics/runs/run-20260805T235627Z/after_fix.json)
- **git diff_ref:** `experiment-baseline-v1`
- **git_app（アプリ修正工数・主指標）:** 20 files, +229 / -14 (` 20 files changed, 229 insertions(+), 14 deletions(-)`)
- **git_frontend（フロント別・第2章）:** 7 files, +80 / -6 (` 7 files changed, 80 insertions(+), 6 deletions(-)`)
- **git_backend（バックエンド別・第2章）:** 12 files, +141 / -6 (` 12 files changed, 141 insertions(+), 6 deletions(-)`)
- **git（実験メタデータ込み）:** 23 files, +262 / -15 (` 23 files changed, 262 insertions(+), 15 deletions(-)`)

<a id="tsv"></a>

<details>
<summary>スプレッドシート用 TSV（全列）</summary>

```tsv
repository	scenario	phase	recorded_at	phpunit_pass	phpunit_total	phpunit_pass_rate	newman_pass	newman_total	newman_pass_rate	phpstan_errors	eslint_ok	ci_jobs_failed	ci_jobs_total	work_minutes	app_files_changed	app_lines_added	app_lines_deleted	frontend_files_changed	frontend_lines_added	frontend_lines_deleted	backend_files_changed	backend_lines_added	backend_lines_deleted	meta_files_changed	meta_lines_added	meta_lines_deleted	commits	manual_bugs	metrics_json	notes
stack-s2	api-spec-change-priority	baseline	20260805T235627Z	21	21	100.0	13	13	100.0	0	1	0	4	5	0	0	0	0	0	0	0	0	0	0	0	0	1	0	experiment/metrics/runs/run-20260805T235627Z/baseline.json	
stack-s2	api-spec-change-priority	after_update	20260806T005527Z	21	21	100.0	13	13	100.0	0	1	1	4	54	20	171	12	7	80	6	10	58	5	20	171	12	1	1	experiment/metrics/runs/run-20260805T235627Z/after_update.json	 20 files changed, 171 insertions(+), 12 deletions(-)
stack-s2	api-spec-change-priority	after_fix	20260806T010556Z	25	25	100.0	15	15	100.0	0	1	0	4	38	23	262	15	7	80	6	12	141	6	23	262	15	1	0	experiment/metrics/runs/run-20260805T235627Z/after_fix.json	 23 files changed, 262 insertions(+), 15 deletions(-)
```

</details>

## 2026-08-12 追記 — `git_app` の除外パス是正にともなう再計測

**主指標 `git_app` を 23 → 20 files（+262/−15 → +229/−14）に改めた。**

当初の計測は Vite のビルド生成物3件（`public/assets/index-CJouV3Ii.js` +16 /
`public/assets/index-Cwra73WT.js` +16 / `public/index.html` +1−1、計 +33/−1）を含んでいた。
これは「手で書いた修正量」を測るという主指標の定義から外れ、ビルド工程を持たない S1・
生成物を未コミットの S0 と比較条件が揃わないため、`collect-experiment-metrics.sh` の除外パスに
`public/assets` と `public/index.html` を追加した（4リポとも同一定義に統一）。

- **裏取りの再計測:** `run-20260815T052039Z`（`--phase after_fix --diff-ref 8d6e4f0`。集約を汚さないよう `experiment/metrics/verifications/` に退避）。
  `git_app = 20 files / +229 / −14` を再現。品質ゲートは PHPStan 0・ESLint OK・Vite build OK・
  **PHPUnit 25/25・Newman 15/15** と元の run と完全一致（コード状態は同一で、変わったのは指標の定義のみ）
- **`after_update` の 20 → 17 files（+171/−12 → +138/−11）は再計測ではなく再計算。**
  当該フェーズのコミット（`f933a59`）は既に過去のため品質ゲートは再実行できず、
  `git diff` による決定的な再計算のみ行った。テスト結果は元の値のまま
- **`baseline` は 0 files で変化なし**

> ⚠ **`experiment-baseline-v1` タグが実験ブランチの祖先になっていない。**
> タグは `6779a89`（`main` 上・2026-08-06 10:23 JST）を指しているが、これは本 run の
> `after_fix` 記録（10:05 JST）より**後に作られたコミット**で、`exp/api-spec-change-priority` の履歴に含まれない。
> そのためタグを基準に測ると 25 files/−57 と誤った値が出る。**正しい基準は anchor コミット `8d6e4f0`。**
> 上記の再計測・再計算はすべて `8d6e4f0` を基準にしている。タグの付け替えは未実施（要判断）。
