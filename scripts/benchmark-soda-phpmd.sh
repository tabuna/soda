#!/usr/bin/env bash
# Benchmark Soda vs PHPMD on a given directory
# Usage: ./scripts/benchmark-soda-phpmd.sh <path> [path2 ...]
# Example: ./scripts/benchmark-soda-phpmd.sh src

set -e

PATHS=("${@:-src}")

echo "=== Benchmark: Soda vs PHPMD ==="
echo "Paths: ${PATHS[*]}"
echo ""

for path in "${PATHS[@]}"; do
  if [[ ! -d "$path" ]]; then
    echo "Skip (not a dir): $path"
    continue
  fi

  echo "--- $path ---"

  if command -v php &>/dev/null && { [[ -f soda ]] || [[ -f vendor/bin/soda ]]; }; then
    echo -n "Soda: "
    /usr/bin/time -p php soda quality "$path" 2>&1 | grep real || true
  else
    echo "Soda: not available"
  fi

  if command -v phpmd &>/dev/null; then
    echo -n "PHPMD: "
    /usr/bin/time -p phpmd "$path" text cleancode,codesize,design 2>&1 | grep real || true
  elif [[ -f vendor/bin/phpmd ]]; then
    echo -n "PHPMD: "
    /usr/bin/time -p php vendor/bin/phpmd "$path" text cleancode,codesize,design 2>&1 | grep real || true
  else
    echo "PHPMD: not available"
  fi

  echo ""
done
