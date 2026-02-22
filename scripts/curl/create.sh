#!/usr/bin/env bash
# Create a cinema room
# Usage: ./create.sh [JWT_TOKEN]   or set JWT_TOKEN (auto-generated via CLI if not set)
# Body: rows, columns, movie, movieDatetime (ISO 8601, required)
set -e
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
BASE_URL="${BASE_URL:-http://localhost:8000}"
JWT_TOKEN="${1:-$JWT_TOKEN}"
BODY="${BODY:-{\"rows\":5,\"columns\":10,\"movie\":\"Example Movie\",\"movieDatetime\":\"2025-12-01T20:00:00+00:00\"}}"
if [ -z "$JWT_TOKEN" ]; then
  echo "JWT_TOKEN not set, generating via CLI..."
  JWT_TOKEN=$(cd "$PROJECT_ROOT" && docker compose exec -T app php bin/console app:jwt:generate 2>/dev/null | tail -1)
  if [ -z "$JWT_TOKEN" ]; then
    echo "Failed to get token. Is Docker running? Run from project root or set JWT_TOKEN."
    exit 1
  fi
fi
curl -s -X POST "${BASE_URL}/api/cinema-rooms" \
  -H "Authorization: Bearer ${JWT_TOKEN}" \
  -H "Content-Type: application/json" \
  -d "$BODY" | (jq . 2>/dev/null || cat)
