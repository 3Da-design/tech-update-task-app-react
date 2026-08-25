#!/usr/bin/env bash
# コンテナ内の node_modules をホスト側へ複製する（エディタの型解決専用）。
#
# node サービスは node_modules を名前付きボリュームで上書きしているため、
# 依存パッケージはコンテナ内にしか存在せず、ホストの node_modules/ は空になる。
# その状態だとエディタの TypeScript 言語サーバーが @types/react を解決できず、
# 「JSX element implicitly has type 'any' because no interface
#  'JSX.IntrinsicElements' exists.」が全コンポーネントに出る。
#
# ビルド・CI・実験計測はコンテナ内のボリュームを使うため、この複製は影響しない。
# node_modules/ は .gitignore 済みで、ブランチ切り替えでも保持される。
#
# 再実行が必要になるのは次の場合:
#   - リポジトリを clone / git worktree add し直したとき
#   - docker compose down -v でボリュームを削除したとき
#   - package.json の依存を変更したとき
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PROJECT="${COMPOSE_PROJECT_NAME:-$(basename "$ROOT")}"
VOLUME="${PROJECT}_node_modules_react"

if ! docker volume inspect "$VOLUME" >/dev/null 2>&1; then
  echo "ボリューム ${VOLUME} が見つかりません。先に依存をインストールしてください:" >&2
  echo "  composer npm:docker-build" >&2
  exit 1
fi

mkdir -p node_modules
docker run --rm \
  -v "${VOLUME}:/from:ro" \
  -v "${ROOT}/node_modules:/to" \
  alpine sh -c 'cp -a /from/. /to/'

echo "synced: $(ls node_modules | wc -l | tr -d ' ') packages -> ${ROOT}/node_modules"
