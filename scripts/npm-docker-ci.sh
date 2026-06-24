#!/usr/bin/env bash
# Docker の node サービスで npm ci（必要なら build）を実行する。
# node_modules の削除は npm ci に任せる。Docker Desktop (Mac) の grpcfuse では
# コンテナ内 rm -rf node_modules が失敗し、ホスト削除直後の npm ci は ENOENT になることがある。
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

docker compose --profile node run --rm node npm ci

if [[ "${1:-}" == "--build" ]]; then
  docker compose --profile node run --rm node npm run build
fi
