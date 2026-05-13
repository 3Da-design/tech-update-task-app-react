#!/usr/bin/env python3
"""Convert leading 4-space-based indentation to 2-space (EditorConfig / PSR-style depth)."""

from __future__ import annotations

import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SKIP_DIRS = {"vendor", "node_modules", "storage", "cache", ".git"}
TARGET_DIRS = ["app", "bootstrap", "config", "database", "routes", "tests"]
ROOT_PHP = ["artisan", "public/index.php"]


def transform_line(line: str) -> str:
    if line[:1] in ("\n", "\r") or not line:
        return line
    n = 0
    for ch in line:
        if ch == " ":
            n += 1
        elif ch == "\t":
            return line
        else:
            break
    if n == 0:
        return line
    new_n = (n // 4) * 2 + (n % 4)
    return " " * new_n + line[n:]


def process_file(path: Path) -> bool:
    raw = path.read_text(encoding="utf-8")
    lines = raw.splitlines(keepends=True)
    out: list[str] = []
    changed = False
    for line in lines:
        if not line.endswith("\n") and line != lines[-1]:
            pass
        new_line = transform_line(line)
        if new_line != line:
            changed = True
        out.append(new_line)
    new_raw = "".join(out)
    if changed and new_raw != raw:
        path.write_text(new_raw, encoding="utf-8", newline="")
        return True
    return False


def iter_php_files() -> list[Path]:
    files: list[Path] = []
    for rel in ROOT_PHP:
        p = ROOT / rel
        if p.is_file():
            files.append(p)
    for d in TARGET_DIRS:
        base = ROOT / d
        if not base.is_dir():
            continue
        for path in base.rglob("*.php"):
            if any(part in SKIP_DIRS for part in path.parts):
                continue
            if path.name.endswith(".blade.php"):
                continue
            files.append(path)
    return sorted(set(files))


def main() -> int:
    changed_count = 0
    for path in iter_php_files():
        if process_file(path):
            changed_count += 1
            print(path.relative_to(ROOT))
    print(f"Updated {changed_count} file(s).", file=sys.stderr)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
