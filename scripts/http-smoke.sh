#!/usr/bin/env bash
set -euo pipefail

if [ $# -lt 1 ] || [ -z "$1" ]; then
    echo "Usage: $0 <base-url>"
    echo "Example: $0 http://127.0.0.1:8011"
    exit 1
fi

BASE_URL="${1%/}"

# Ensure base URL is not empty and has http/https scheme
if [[ ! "$BASE_URL" =~ ^https?:// ]]; then
    echo "❌ Error: Base URL must start with http:// or https://"
    exit 1
fi

echo "=========================================="
echo "Starting HTTP Smoke Test against: $BASE_URL"
echo "=========================================="

CURL_TIMEOUT=10
CONNECT_TIMEOUT=5

check_endpoint() {
    local endpoint="$1"
    local expected_status="$2"
    local url="${BASE_URL}${endpoint}"
    
    echo -n "Checking GET ${endpoint} (expect ${expected_status})... "
    
    local http_code
    http_code=$(curl -s -o /dev/null -w "%{http_code}" \
        --connect-timeout "$CONNECT_TIMEOUT" \
        --max-time "$CURL_TIMEOUT" \
        "$url")
    
    if [ "$http_code" = "$expected_status" ]; then
        echo "OK (${http_code})"
    else
        echo "FAIL (expected ${expected_status}, got ${http_code})"
        exit 1
    fi
}

check_redirect_or_ok() {
    local endpoint="$1"
    local url="${BASE_URL}${endpoint}"
    
    echo -n "Checking GET ${endpoint} (expect 200 or 302)... "
    
    local http_code
    http_code=$(curl -s -o /dev/null -w "%{http_code}" \
        --connect-timeout "$CONNECT_TIMEOUT" \
        --max-time "$CURL_TIMEOUT" \
        "$url")
    
    if [ "$http_code" = "200" ] || [ "$http_code" = "302" ]; then
        echo "OK (${http_code})"
    else
        echo "FAIL (expected 200 or 302, got ${http_code})"
        exit 1
    fi
}

check_health() {
    local endpoint="/health"
    local url="${BASE_URL}${endpoint}"
    
    echo -n "Checking GET ${endpoint} (JSON status=ok, db=connected)... "
    
    local response_file
    response_file=$(mktemp)
    
    local http_code
    http_code=$(curl -s -w "%{http_code}" -o "$response_file" \
        --connect-timeout "$CONNECT_TIMEOUT" \
        --max-time "$CURL_TIMEOUT" \
        "$url")
    
    if [ "$http_code" != "200" ]; then
        echo "FAIL (HTTP ${http_code})"
        cat "$response_file"
        rm -f "$response_file"
        exit 1
    fi
    
    local response_body
    response_body=$(cat "$response_file")
    rm -f "$response_file"
    
    local valid=0
    if command -v jq >/dev/null 2>&1; then
        if echo "$response_body" | jq -e '.status == "ok" and .db == "connected"' >/dev/null 2>&1; then
            valid=1
        fi
    elif command -v php >/dev/null 2>&1; then
        if php -r '$d=json_decode(file_get_contents("php://stdin"), true); exit(($d["status"]??"") === "ok" && ($d["db"]??"") === "connected" ? 0 : 1);' <<< "$response_body" 2>/dev/null; then
            valid=1
        fi
    elif command -v python3 >/dev/null 2>&1; then
        if python3 -c 'import sys, json; d=json.load(sys.stdin); sys.exit(0 if d.get("status")=="ok" and d.get("db")=="connected" else 1)' <<< "$response_body" 2>/dev/null; then
            valid=1
        fi
    fi
    
    if [ "$valid" -eq 1 ]; then
        echo "OK (status=ok, db=connected)"
    else
        echo "FAIL (Invalid JSON health response: ${response_body})"
        exit 1
    fi
}

# Run smoke tests
check_endpoint "/up" "200"
check_health
check_endpoint "/" "200"
check_endpoint "/artikel" "200"
check_redirect_or_ok "/admin"

echo "=========================================="
echo "✅ All HTTP Smoke Tests Passed Successfully!"
echo "=========================================="
