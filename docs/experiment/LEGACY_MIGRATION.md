# 従来構成リポジトリ作成チェックリスト

本ドキュメントは、**改良構成が完成したあと**に別リポジトリで従来構成（悪い例）を作るための手順です。本リポジトリでは実施しません。

## 前提

- 改良構成リポジトリに `experiment-baseline-v1` タグが付いている
- 機能・API 仕様・DB スキーマ・テスト期待値は **同一** に保つ
- `.github/workflows/ci.yml` は **コピーして同一** にする

## 1. リポジトリの複製

```bash
git clone <improved-repo-url> tech-update-task-app-legacy
cd tech-update-task-app-legacy
git remote rename origin improved-origin   # 任意
git remote add origin <new-legacy-repo-url>
```

## 2. タスク領域の「悪化」リファクタ

以下を **タスク機能のみ** 対象に実施する（認証・プロフィールは Breeze のまま）。

| 改良構成（削除・統合） | 従来構成（移行先） |
|------------------------|-------------------|
| `App\Services\TaskService` | `Web\TaskController` / `API\TaskController` にビジネスロジックを移動 |
| `TaskRepository` + `TaskRepositoryInterface` | Controller 内で `Task::query()` を直接使用 |
| `RepositoryServiceProvider` の bind | 削除 |
| Web / API で共有していた Service | 必要なら Web / API でロジックを重複実装 |

### チェックリスト

- [ ] `app/Services/TaskService.php` を削除
- [ ] `app/Repositories/` を削除
- [ ] `RepositoryServiceProvider` を `bootstrap/providers.php` から削除
- [ ] `Web\TaskController` に一覧フィルタ・正規化・認可ロジックを集約
- [ ] `API\TaskController` に同様のロジックを集約（または Web にのみ集約し API から呼ぶ — いずれも Service 層なし）
- [ ] FormRequest / TaskResource は維持してよい（仕様・テストを揃えるため）
- [ ] `tests/Feature/TaskApiTest.php` の期待値は **変更しない**
- [ ] `tests/Feature/TaskWebTest.php` の期待値は **変更しない**

## 3. 動作・CI の確認

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
./scripts/check-quality.sh
```

- [ ] PHPUnit 全件成功
- [ ] PHPStan 0 件
- [ ] Newman 全件成功
- [ ] GitHub Actions 4 ジョブ成功

## 4. 従来構成のベースライン tag

```bash
git tag -a experiment-baseline-v1 -m "Experiment baseline: legacy architecture"
```

## 5. 比較実験時の注意

- 各シナリオは [scenarios/](./scenarios/) の **同じ変更内容** を両リポジトリに適用する
- 修正は「CI が緑になる最小限」に留め、リファクタで差を広げない
- メトリクスは [metrics-record-template.md](./metrics-record-template.md) の列定義で両方記録する
