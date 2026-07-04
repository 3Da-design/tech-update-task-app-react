# 主シナリオ 3 件 — 実験結果統合（legacy / improved）

`exp/*` ブランチ上の各シナリオ `RECORD.md` を **マージせず** に集約した比較表です。  
詳細・JSON へのリンクは各ブランチの `experiment/results/` を参照してください。

**収集日:** 2026-07-04  
**比較基準:** `experiment-baseline-v1` からの `git diff`（`experiment/results/` 配下のメタデータファイルを含む）

## ソース（ブランチ別）

| シナリオ | legacy ブランチ | legacy RECORD | improved ブランチ | improved RECORD |
|----------|-----------------|---------------|-------------------|-----------------|
| status integer 化 | `exp/api-spec-change-status-int` | `experiment/results/legacy/api-spec-change-status-int/RECORD.md` | `exp/api-spec-change-status-int` | `experiment/results/api-spec-change-status-int/RECORD.md` |
| priority 追加 | `exp/api-spec-change-priority` | `experiment/results/legacy/api-spec-change-priority/RECORD.md` | `exp/api-spec-change-priority` | `experiment/results/api-spec-change-priority/RECORD.md` |
| DB / クエリ変更 | `exp/db-schema-change` | `experiment/results/legacy/db-schema-change/RECORD.md` | `exp/db-schema-change` | `experiment/results/db-schema-change/RECORD.md` |

リポジトリ: legacy = `tech-update-task-app-legacy`、improved = `tech-update-task-app`（本リポジトリ）

---

## 主指標サマリー（`after_fix` フェーズ）

| シナリオ | 構成 | 変更ファイル | 追加行 | 削除行 | 作業時間 (分) | コミット数 | CI |
|----------|------|-------------|--------|--------|--------------|-----------|-----|
| status-int | legacy | 14 | 103 | 50 | 50 | 2 | 4/4 |
| status-int | improved | 15 | 87 | 38 | 30 | 2 | 4/4 |
| priority | legacy | 15 | 263 | 19 | 50 | 2 | 4/4 |
| priority | improved | 17 | 268 | 10 | 30 | 2 | 4/4 |
| db-schema | legacy | 3 | 35 | 2 | 15 | 2 | 4/4 |
| db-schema | improved | 2 | 34 | 1 | 10 | 3 | 4/4 |

**legacy − improved（ファイル数差）:** status-int −1、priority −2、db-schema +1

---

## 更新直後のテスト失敗（`after_update` フェーズ）

| シナリオ | 構成 | PHPUnit | Newman | 備考 |
|----------|------|---------|--------|------|
| status-int | legacy | 30/47（17 失敗） | 10/13（3 失敗） | 破壊的 API 変更。構成差は主に修正工数で現れる |
| status-int | improved | 30/47（17 失敗） | 10/13（3 失敗） | 同上（失敗数は同一） |
| priority | legacy | 47/47（0 失敗） | 13/13（0 失敗） | 非破壊的追加。更新直後も全通過 |
| priority | improved | 47/47（0 失敗） | 13/13（0 失敗） | 同上 |
| db-schema | legacy | 47/47（0 失敗） | 13/13（0 失敗） | クエリ変更のみ。既存テストは通過 |
| db-schema | improved | 47/47（0 失敗） | 13/13（0 失敗） | 同上 |

---

## フェーズ別詳細（手動記入 + 自動収集）

### api-spec-change-status-int

| フェーズ | 構成 | PHPUnit | Newman | 変更ファイル | 追加/削除行 | 作業時間 (分) | メモ |
|----------|------|---------|--------|-------------|------------|--------------|------|
| baseline | legacy | 47/47 | 13/13 | 0 | +0/−0 | 10 | 変更前 |
| after_update | legacy | 30/47 | 10/13 | 10 | +81/−28 | 45 | テスト・Postman 未修正 |
| after_fix | legacy | 47/47 | 13/13 | 14 | +103/−50 | 50 | テスト・Postman 修正完了 |
| baseline | improved | 47/47 | 13/13 | 0 | +0/−0 | 10 | baseline 計測 |
| after_update | improved | 30/47 | 10/13 | 11 | +65/−16 | 20 | PHPUnit 17失敗・Newman 3失敗 |
| after_fix | improved | 47/47 | 13/13 | 15 | +87/−38 | 30 | Controller 未変更 |

### api-spec-change-priority

| フェーズ | 構成 | PHPUnit | Newman | 変更ファイル | 追加/削除行 | 作業時間 (分) | メモ |
|----------|------|---------|--------|-------------|------------|--------------|------|
| baseline | legacy | 47/47 | 13/13 | 0 | +0/−0 | 10 | baseline 計測 |
| after_update | legacy | 47/47 | 13/13 | 11 | +157/−12 | 45 | PHPUnit/Newman 0失敗（非破壊的変更） |
| after_fix | legacy | 52/52 | 15/15 | 15 | +263/−19 | 50 | Web/API Controller 2 箇所変更済み |
| baseline | improved | 47/47 | 13/13 | 0 | +0/−0 | 10 | baseline 計測 |
| after_update | improved | 47/47 | 13/13 | 13 | +120/−7 | 20 | PHPUnit/Newman 0失敗（非破壊的変更） |
| after_fix | improved | 54/54 | 15/15 | 17 | +268/−10 | 30 | API Controller 未変更 |

### db-schema-change

| フェーズ | 構成 | PHPUnit | Newman | 変更ファイル | 追加/削除行 | 作業時間 (分) | メモ |
|----------|------|---------|--------|-------------|------------|--------------|------|
| baseline | legacy | 47/47 | 13/13 | 0 | +0/−0 | 5 | 変更前 |
| after_update | legacy | 47/47 | 13/13 | 2 | +2/−2 | 15 | ケース無視テスト未追加 |
| after_fix | legacy | 49/49 | 13/13 | 3 | +35/−2 | 15 | ケース無視テスト追加完了 |
| baseline | improved | 47/47 | 13/13 | 0 | +0/−0 | 5 | baseline 計測のみ |
| after_update | improved | 47/47 | 13/13 | 1 | +1/−1 | 5 | TaskRepository の LOWER 比較に変更 |
| after_fix | improved | 49/49 | 13/13 | 2 | +34/−1 | 10 | ケース無視テスト 2 件追加 |

---

## スプレッドシート用 TSV

`COMPARISON.tsv` を参照するか、以下をコピーしてください。

```tsv
repository	scenario	phase	recorded_at	phpunit_pass	phpunit_total	phpunit_pass_rate	newman_pass	newman_total	newman_pass_rate	phpstan_errors	eslint_ok	ci_jobs_failed	ci_jobs_total	work_minutes	files_changed	lines_added	lines_deleted	commits	manual_bugs	notes
legacy	api-spec-change-status-int	baseline	20260703T061225Z	47	47	100.0	13	13	100.0	0	1			10	0	0	0	0	0	変更前
legacy	api-spec-change-status-int	after_update	20260703T061344Z	30	47	63.83	10	13	76.92	0	1			45	10	81	28	1	0	テスト・Postman 未修正（PHPUnit 17失敗、Newman 3失敗）
legacy	api-spec-change-status-int	after_fix	20260703T061457Z	47	47	100.0	13	13	100.0	0	1	0	4	50	14	103	50	2	0	テスト・Postman 修正完了
improved	api-spec-change-status-int	baseline	20260703T062340Z	47	47	100.0	13	13	100.0	0	1	0	4	10	0	0	0	0	0	変更なし（baseline 計測）
improved	api-spec-change-status-int	after_update	20260703T062501Z	30	47	63.83	10	13	76.92	0	1	2	4	20	11	65	16	1	0	PHPUnit 17失敗・Newman 3失敗（テスト・Postman未修正）
improved	api-spec-change-status-int	after_fix	20260703T062604Z	47	47	100.0	13	13	100.0	0	1	0	4	30	15	87	38	2	0	主指標: 15 files, +87/-38。Controller 未変更
legacy	api-spec-change-priority	baseline	20260703T063745Z	47	47	100.0	13	13	100.0	0	1			10	0	0	0	0	0	変更前（baseline 計測）
legacy	api-spec-change-priority	after_update	20260703T064012Z	47	47	100.0	13	13	100.0	0	1			45	11	157	12	1	0	PHPUnit/Newman 0失敗（非破壊的変更・テスト・Postman 未修正でも緑）
legacy	api-spec-change-priority	after_fix	20260703T064309Z	52	52	100.0	15	15	100.0	0	1	0	4	50	15	263	19	2	0	priority 用テスト・Postman 追加完了（Web/API Controller 2 箇所変更済み）
improved	api-spec-change-priority	baseline	20260703T065152Z	47	47	100.0	13	13	100.0	0	1	0	4	10	0	0	0	0	0	変更なし（baseline 計測）
improved	api-spec-change-priority	after_update	20260703T065436Z	47	47	100.0	13	13	100.0	0	1	0	4	20	13	120	7	1	0	PHPUnit/Newman 0失敗（非破壊的変更・テスト未修正でも緑）
improved	api-spec-change-priority	after_fix	20260703T065612Z	54	54	100.0	15	15	100.0	0	1	0	4	30	17	268	10	2	0	主指標: 17 files, +268/-10。API Controller 未変更
legacy	db-schema-change	baseline	20260704T044055Z	47	47	100.0	13	13	100.0	0	1			5	0	0	0	0	0	変更前
legacy	db-schema-change	after_update	20260704T044112Z	47	47	100.0	13	13	100.0	0	1			15	2	2	2	1	0	ケース無視テスト未追加（PHPUnit/Newman 全通過）
legacy	db-schema-change	after_fix	20260704T044150Z	49	49	100.0	13	13	100.0	0	1	0	4	15	3	35	2	2	0	ケース無視テスト追加完了
improved	db-schema-change	baseline	20260704T044846Z	47	47	100.0	13	13	100.0	0	1			5	0	0	0	0	0	baseline 計測のみ
improved	db-schema-change	after_update	20260704T044903Z	47	47	100.0	13	13	100.0	0	1			5	1	1	1	1	0	TaskRepository の LOWER 比較に変更
improved	db-schema-change	after_fix	20260704T044951Z	49	49	100.0	13	13	100.0	0	1	0	4	10	2	34	1	3	0	ケース無視テスト 2 件追加、check-quality 緑
```

---

## 読み取りメモ（考察のたたき台）

1. **破壊的変更（status-int）:** 更新直後のテスト失敗数は両構成で同一。差は `after_fix` の行数（legacy +103 vs improved +87）と作業時間に現れる。
2. **非破壊的追加（priority）:** 更新直後は両構成ともテスト全通過。構成差の判定には通過率ではなく修正ファイル数・行数を使う。
3. **クエリ変更（db-schema）:** improved は Repository 1 箇所（after_update 1 file）、legacy は Controller 2 箇所（after_update 2 files）— 設計仮説どおりの差。
