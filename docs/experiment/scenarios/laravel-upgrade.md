# シナリオ: Laravel バージョン更新

## 目的

フレームワークのメジャー（またはマイナー）更新時に、**アプリ全体**（Breeze 含む）への影響と修正工数を比較する。

## 想定される破壊箇所

| 領域 | 例 |
|------|-----|
| 設定 | `bootstrap/app.php`, `config/*` |
| 認証 | Breeze コントローラ・ミドルウェア |
| タスク | Service / Repository / Resource |
| テスト | PHPUnit のアサーション・属性 |
| フロント | Vite / Blade の `@vite` 挙動 |

## 事前条件

- ベースライン CI 緑
- [Laravel Upgrade Guide](https://laravel.com/docs/upgrade) の対象バージョン手順を確認済み

## 変更内容の具体例

**例:** Laravel 13.x → 次の安定メジャー（リリース時点の公式ガイドに従う）

```bash
# composer.json の laravel/framework を更新
composer update laravel/framework --with-all-dependencies
```

両リポジトリで **同じ目標バージョン** に揃える。

## 実施手順

```bash
git checkout -b exp/laravel-upgrade experiment-baseline-v1

# 1. composer.json / composer.lock を更新
docker compose exec app composer update laravel/framework --with-all-dependencies

# 2. 公式 Upgrade Guide に沿って破壊的変更を修正
#    - bootstrap/app.php
#    - config/
#    - app/ 配下

# 3. Breeze 関連の非推奨 API を修正

docker compose --profile node run --rm node sh -c "npm ci && npm run build"

composer experiment:metrics -- --phase after_update

# 4. PHPUnit / PHPStan / Pint / Newman を順に修正
docker compose exec app vendor/bin/pint
./scripts/check-quality.sh

composer experiment:metrics -- --phase after_fix
```

## 記録するメトリクス

- 全 CI ジョブの成否（ジョブ単位で失敗数を記録）
- PHPUnit / PHPStan / ESLint / Newman の通過率
- 修正工数
- タスク領域 vs 認証領域の修正ファイル数（メモで内訳）

## 完了条件

- [ ] `composer.json` の Laravel バージョンが両リポジトリで一致
- [ ] CI 4 ジョブ成功
- [ ] `after_fix` メトリクスとスプレッドシート記録完了
