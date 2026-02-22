#!/usr/bin/env bash
# Delete a cinema room by id (fails with 422 if room has reservations)
# Usage: ./delete.sh <id> [JWT_TOKEN]   or set JWT_TOKEN (auto-generated via CLI if not set)
set -e
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
BASE_URL="${BASE_URL:-http://localhost:8000}"
ID="${1:?Usage: $0 <id> [JWT_TOKEN]}"
JWT_TOKEN="${2:-$JWT_TOKEN}"
if [ -z "$JWT_TOKEN" ]; then
  echo "JWT_TOKEN not set, generating via CLI..."
  JWT_TOKEN=$(cd "$PROJECT_ROOT" && docker compose exec -T app php bin/console app:jwt:generate 2>/dev/null | tail -1)
  if [ -z "$JWT_TOKEN" ]; then
    echo "Failed to get token. Is Docker running? Run from project root or set JWT_TOKEN."
    exit 1
  fi
fi
curl -s -w "\nHTTP %{http_code}\n" -X DELETE "${BASE_URL}/api/cinema-rooms/${ID}" \
  -H "Authorization: Bearer ${JWT_TOKEN}" \
  -H "Content-Type: application/json"
