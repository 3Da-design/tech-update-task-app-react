# 主シナリオ 3 件 — 実験結果統合（legacy / improved）

`exp/*` ブランチ上の各シナリオ `RECORD.md` を **マージせず** に集約した比較表です。

**収集日:** 2026-07-04  
**比較基準:** `experiment-baseline-v1` からの `git diff`（ブランチ先端）

リポジトリ: legacy = `tech-update-task-app-legacy`、improved = `tech-update-task-app`（本リポジトリ）

## 修正工数の見方

| 種別 | JSON キー | 除外パス | 用途 |
|------|-----------|----------|------|
| **アプリ修正工数（主指標）** | `git_app` | `experiment/results/`・`experiment/metrics/` | 論文・構成比較 |
| **メタデータ込み（参考）** | `git` | なし（結果 JSON・RECORD 等を含む） | 実験運用全体の diff |

## ソース（ブランチ別）

| シナリオ | legacy ブランチ | legacy RECORD | improved ブランチ | improved RECORD |
|----------|-----------------|---------------|-------------------|-----------------|
| status integer 化 | `exp/api-spec-change-status-int` | `experiment/results/legacy/api-spec-change-status-int/RECORD.md` | `exp/api-spec-change-status-int` | `experiment/results/api-spec-change-status-int/RECORD.md` |
| priority 追加 | `exp/api-spec-change-priority` | `experiment/results/legacy/api-spec-change-priority/RECORD.md` | `exp/api-spec-change-priority` | `experiment/results/api-spec-change-priority/RECORD.md` |
| DB / クエリ変更 | `exp/db-schema-change` | `experiment/results/legacy/db-schema-change/RECORD.md` | `exp/db-schema-change` | `experiment/results/db-schema-change/RECORD.md` |

---

## 主指標サマリー — アプリ修正工数（`after_fix` / `git_app` 相当）

| シナリオ | 構成 | 変更ファイル | 追加行 | 削除行 | 作業時間 (分) |
|----------|------|-------------|--------|--------|--------------|
| status-int | legacy | 14 | 103 | 50 | 50 |
| status-int | improved | 15 | 87 | 38 | 30 |
| priority | legacy | 15 | 263 | 19 | 50 |
| priority | improved | 17 | 268 | 10 | 30 |
| db-schema | legacy | 3 | 35 | 2 | 15 |
| db-schema | improved | 2 | 34 | 1 | 10 |

**db-schema 内訳（アプリのみ）:** legacy = Web/API Controller 2 + テスト 1、improved = TaskRepository 1 + テスト 1

---

## 参考 — メタデータ込み（`after_fix` / `git` 相当）

| シナリオ | 構成 | 変更ファイル | 追加行 | 削除行 |
|----------|------|-------------|--------|--------|
| status-int | legacy | 19 | 291 | 50 |
| status-int | improved | 20 | 275 | 38 |
| priority | legacy | 20 | 451 | 19 |
| priority | improved | 22 | 456 | 10 |
| db-schema | legacy | 8 | 223 | 2 |
| db-schema | improved | 7 | 222 | 1 |

---

## 更新直後のテスト失敗（`after_update`）

| シナリオ | 構成 | PHPUnit | Newman | 備考 |
|----------|------|---------|--------|------|
| status-int | legacy / improved | 30/47（17 失敗） | 10/13（3 失敗） | 破壊的 API 変更 |
| priority | legacy / improved | 47/47（0 失敗） | 13/13（0 失敗） | 非破壊的追加 |
| db-schema | legacy / improved | 47/47（0 失敗） | 13/13（0 失敗） | クエリ変更のみ |

---

## 読み取りメモ

1. **破壊的変更（status-int）:** 更新直後の失敗数は同一。アプリ修正工数では improved が行数・時間で有利。
2. **非破壊的追加（priority）:** 通過率だけでは構成差が出ない。アプリのファイル数・行数で比較する。
3. **クエリ変更（db-schema）:** アプリ修正工数で legacy 3 files vs improved 2 files — 設計仮説どおり。

---

## スプレッドシート用 TSV

`COMPARISON.tsv` を参照（`app_*` = 主指標、`meta_*` = メタデータ込み）。
