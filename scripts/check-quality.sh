#!/usr/bin/env bash
# 実行前: PHPStan + ESLint / 実行後: PHPUnit（Docker 前提）
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "== PHPStan (実行前・型・構造) =="
docker compose exec -T app composer phpstan

echo ""
echo "== ESLint (実行前・構文・規約) =="
docker compose --profile node run --rm node npm run lint

echo ""
echo "== PHPUnit (実行後・ロジック) =="
docker compose exec -T app composer test

echo ""
echo "All static and unit checks passed."
echo "Postman: Import postman/Task-API.postman_collection.json and postman/local.postman_environment.json"
echo "  Run Auth → Login, then Tasks API folder in Postman (app must be up on :8000)."
