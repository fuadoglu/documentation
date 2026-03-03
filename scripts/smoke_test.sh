#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-http://localhost}"

echo "Smoke test target: ${BASE_URL}"

check() {
  local path="$1"
  local expected_code="$2"

  local code
  code=$(curl -k -L -o /dev/null -s -w "%{http_code}" "${BASE_URL}${path}")

  if [[ "$code" != "$expected_code" ]]; then
    echo "FAIL: ${path} expected ${expected_code}, got ${code}"
    exit 1
  fi

  echo "OK: ${path} -> ${code}"
}

# guest pages
check "/login" "200"

# register and forgot password must be disabled
check "/register" "404"
check "/forgot-password" "404"

echo "Smoke tests passed."
