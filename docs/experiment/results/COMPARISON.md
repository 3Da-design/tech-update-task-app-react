# 改良構成 vs 従来構成 — 実験比較表

本ドキュメントは、**3 更新シナリオ** について改良構成（improved）と従来構成（legacy）で実施した 3 フェーズ計測の要約です。

> **比較の読み方:** `after_update` の PHPUnit / Newman **通過率は構成によって同一になることがある**。構成差の主指標は **`after_fix` の `git.files_changed` / `lines_added` / `lines_deleted`**（`composer experiment:metrics -- --diff-ref experiment-baseline-v1`）。詳細は [EXPERIMENT.md](../EXPERIMENT.md) と [ARCHITECTURE_ANALYSIS.md](../ARCHITECTURE_ANALYSIS.md) を参照。

> **main ブランチ:** タスクのベースライン仕様（4 属性）。シナリオ変更は **`exp/*` ブランチ** で実施し、起点は `experiment-baseline-v1` タグを使用すること。

---

## シナリオ 1: API 仕様変更 — 属性追加（`api-spec-change-priority`）

| 構成 | フェーズ | PHPUnit | Newman | PHPStan | Vite build |
|------|----------|---------|--------|---------|------------|
| improved | baseline | 38/38 (100%) | 13/13 (100%) | 0 | OK |
| improved | after_update | 36/38 (94.74%) | 10/13 (76.92%) | 0 | OK |
| improved | after_fix | 38/38 (100%) | 13/13 (100%) | 0 | OK |
| legacy | baseline | 38/38 (100%) | 13/13 (100%) | 0 | OK |
| legacy | after_update | 36/38 (94.74%) | 10/13 (76.92%) | 0 | OK |
| legacy | after_fix | 38/38 (100%) | 13/13 (100%) | 0 | OK |

**主な修正ファイル（改良）:** `TaskResource`, FormRequest×2, `TaskService`, migration, テスト, Postman（**Controller / Repository は未変更**）

**主な修正ファイル（従来）:** 上記に加え **`Web\TaskController` と `API\TaskController` の `normalizeTaskPayload` を両方更新**

| run_id | 構成 | 結果ディレクトリ |
|--------|------|------------------|
| `run-20260521T060318Z` | improved | [api-spec-change-priority/](./api-spec-change-priority/) |
| `run-20260521T061416Z` | legacy | [legacy/api-spec-change-priority/](./legacy/api-spec-change-priority/) |

**所見:** 更新直後の失敗数は同一。従来構成では修正対象が Controller 2 ファイルに分散し、改良構成では Service 周辺に集約される。

---

## シナリオ 2: API 仕様変更 — 既存属性の型変更（`api-spec-change-status-int`）

| 構成 | フェーズ | PHPUnit | Newman | 備考 |
|------|----------|---------|--------|------|
| improved | — | — | — | **未実施**（`exp/api-spec-change-status-int` 推奨） |
| legacy | — | — | — | 未実施 |

**期待される差:** priority 追加より修正ファイル数・変更レイヤ数が多い（Repository フィルタ、IndexTaskRequest、config、データ移行、Web View）。Controller 重複回避の効果は維持するが、修正総量により相対優位は縮小。

分析: [ARCHITECTURE_ANALYSIS.md](../ARCHITECTURE_ANALYSIS.md) セクション 2

---

## シナリオ 3: DB / クエリ変更（`db-schema-change`）

| 構成 | フェーズ | PHPUnit | Newman | 作業時間（ログ） |
|------|----------|---------|--------|------------------|
| improved | 3 フェーズ実施 | — | — | 15 分（`run-20260523T012455Z`） |
| legacy | 3 フェーズ実施 | — | — | 20 分（`run-20260523T012713Z`） |

**主な修正ファイル（改良）:** `TaskRepository::getFiltered` のみ + `TaskListFilterTest`

**主な修正ファイル（従来）:** Web / API Controller の `listTasks` クエリ **両方**

**期待される差:** 従来構成は `files_changed` が改良構成より **+1（Web Controller）** 程度多い。

> `results/` への JSON 公開は未掲載。発表前に `git.files_changed` を `--diff-ref experiment-baseline-v1` で計測すること。

分析: [ARCHITECTURE_ANALYSIS.md](../ARCHITECTURE_ANALYSIS.md) セクション 3

---

## 3 シナリオ比較一覧（改良構成）

| シナリオ | 主な修正ファイル | ファイル数 | 行数 | 変更レイヤ数 | 更新耐性 | Controller | Repository |
|----------|------------------|------------|------|--------------|----------|------------|------------|
| `api-spec-change-priority` | Resource, Request×2, Service, migration, Model, tests | 中（7〜10） | 中 | 4〜5 | **中** | **不変** | **不変** |
| `api-spec-change-status-int` | 上記 + IndexRequest, config, Repository, View | 中〜多（10〜14） | 中〜多 | 5〜6 | **低〜中** | **不変** | **フィルタのみ** |
| `db-schema-change` | **Repository**, TaskListFilterTest | **少（2）** | **少** | **2** | **高** | **不変** | **主修正** |

---

## ブランチ一覧

| ブランチ | 内容 |
|----------|------|
| `main` | 改良構成ベースライン + 分析ドキュメント |
| `experiment-baseline-v1` | メトリクス比較起点タグ |
| `exp/api-spec-change-priority` | シナリオ 1（3 フェーズ実施済み） |
| `exp/api-spec-change-status-int` | シナリオ 2（未実施） |
| `exp/db-schema-change` | シナリオ 3 |
| `legacy-architecture` | 従来構成 |
| `exp/legacy-api-spec-change-priority` | 従来構成でのシナリオ 1 |

---

## 評価指標の達成

| 指標 | 状態 |
|------|------|
| テスト通過率（3 フェーズ） | シナリオ 1 で `baseline` / `after_update` / `after_fix` JSON あり |
| 修正工数（主指標） | シナリオ 1: improved vs legacy で `files_changed` 比較 |
| 従来構成比較 | シナリオ 1 実施済み、シナリオ 3 は logs のみ |
| シナリオ 2 | 未実施 — 発表前に計測推奨 |
