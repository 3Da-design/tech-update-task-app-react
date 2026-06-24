#!/usr/bin/env bash
# 実行前: PHPStan + ESLint / 実行後: PHPUnit + Newman（Docker 前提）
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "== PHPStan (実行前・型・構造) =="
docker compose exec -T app composer phpstan

echo ""
echo "== Frontend dependencies (Docker / npm ci) =="
"$ROOT/scripts/npm-docker-ci.sh"

echo ""
echo "== ESLint (実行前・構文・規約) =="
docker compose --profile node run --rm node npm run lint

echo ""
echo "== Frontend build (PHPUnit / Newman 用) =="
docker compose --profile node run --rm node npm run build

echo ""
echo "== PHPUnit (実行後・ロジック) =="
docker compose exec -T app composer test

echo ""
echo "== Newman (実行後・API・セッション) =="
if ! curl -sf "http://localhost:8000/up" > /dev/null 2>&1; then
  echo "http://localhost:8000 に接続できないため docker compose up -d を実行します"
  docker compose up -d
  for _ in $(seq 1 30); do
    if curl -sf "http://localhost:8000/up" > /dev/null 2>&1; then
      break
    fi
    sleep 1
  done
  if ! curl -sf "http://localhost:8000/up" > /dev/null 2>&1; then
    echo "ERROR: http://localhost:8000/up に接続できません。docker compose ps で状態を確認してください。"
    exit 1
  fi
fi

echo "DB migrate --seed（test@example.com / password）"
docker compose exec -T app php artisan migrate --force --seed

docker compose --profile node run --rm node npm run test:api:docker

echo ""
echo "All checks passed (PHPStan, ESLint, PHPUnit, Newman)."
