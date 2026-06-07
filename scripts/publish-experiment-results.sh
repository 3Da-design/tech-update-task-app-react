#!/usr/bin/env bash
# experiment/metrics/runs/<run_id>/ を docs/experiment/results/<scenario>/ にコピーする
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

SCENARIO=""
RUN_ID=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --scenario)
      SCENARIO="${2:?--scenario requires a value}"
      shift 2
      ;;
    --run)
      RUN_ID="${2:?--run requires a value}"
      shift 2
      ;;
    *)
      echo "Unknown argument: $1" >&2
      exit 1
      ;;
  esac
done

if [[ -z "$SCENARIO" ]]; then
  echo "error: --scenario is required (e.g. api-spec-change-priority)" >&2
  exit 1
fi

ACTIVE_RUN_FILE="$ROOT/experiment/metrics/.active-run"
if [[ -z "$RUN_ID" ]]; then
  if [[ ! -f "$ACTIVE_RUN_FILE" ]]; then
    echo "error: no --run and no experiment/metrics/.active-run" >&2
    exit 1
  fi
  RUN_ID="$(tr -d '\n' <"$ACTIVE_RUN_FILE")"
fi

SRC="$ROOT/experiment/metrics/runs/$RUN_ID"
DEST="$ROOT/docs/experiment/results/$SCENARIO"

if [[ ! -d "$SRC" ]]; then
  echo "error: run directory not found: $SRC" >&2
  exit 1
fi

mkdir -p "$DEST"
for f in baseline.json after_update.json after_fix.json RECORD.md; do
  if [[ -f "$SRC/$f" ]]; then
    cp "$SRC/$f" "$DEST/$f"
  fi
done

printf '%s\n' "$RUN_ID" >"$DEST/run_id.txt"
echo "Published $SRC -> $DEST"
