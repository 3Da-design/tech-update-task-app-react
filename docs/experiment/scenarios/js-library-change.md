# シナリオ: JavaScript ライブラリ変更

## 目的

フロントエンド資産（Alpine.js / Tailwind / Vite）のバージョン更新時の影響を比較する。本アプリの JS は薄いが、**ビルドパイプラインと Blade `@vite`** は CI 全ジョブに影響する。

## 想定される破壊箇所

| 領域 | 例 |
|------|-----|
| `package.json` | alpinejs, tailwindcss, vite, laravel-vite-plugin |
| `vite.config.js` / `tailwind.config.js` | 設定 API 変更 |
| `resources/js/app.js` | Alpine の import パス |
| CI | `frontend` ジョブ、`php-tests` の Vite build |
| Blade テスト | `manifest.json` 不足による 500 |

## 事前条件

- ベースライン CI 緑
- 各ライブラリの Migration Guide を確認

## 変更内容の具体例

**例（いずれか 1 つ、またはセット）:**

1. **Alpine.js** メジャー更新（`package.json` の `alpinejs`）
2. **Vite** メジャー更新（`vite`, `laravel-vite-plugin`）
3. **Tailwind CSS** メジャー更新（`tailwindcss`, `@tailwindcss/vite`）

両リポジトリで同じパッケージ・同じバージョンに揃える。

## 実施手順

```bash
git checkout -b exp/js-library-change experiment-baseline-v1

# 1. package.json を更新
docker compose --profile node run --rm node sh -c "npm install alpinejs@latest --save-dev"

# 2. 設定・import を修正
#    resources/js/app.js, vite.config.js, tailwind.config.js

docker compose --profile node run --rm node sh -c "npm ci && npm run lint && npm run build"

composer experiment:metrics -- --phase after_update

# 3. ESLint 指摘・ビルドエラーを修正
./scripts/check-quality.sh

composer experiment:metrics -- --phase after_fix
```

## 記録するメトリクス

- ESLint / Vite build の成否
- PHPUnit（Blade 描画テスト）の成否
- Newman の成否（ログイン画面経由）
- 修正工数

## 完了条件

- [ ] `npm run build` 成功
- [ ] CI の `frontend` / `php-tests` / `api-tests` 成功
- [ ] メトリクス記録完了
