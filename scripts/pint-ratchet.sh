#!/usr/bin/env bash
# =============================================================================
# Pint Quality Ratchet
# -----------------------------------------------------------------------------
# Purpose:
# Enforce Pint code formatting standards incrementally against all new or
# modified PHP files since the immutable quality baseline:
#   6f6ec58662f6e5b8db3fe6ecf9b6aa281da50f87
#
# Context:
# Repository-wide Pint currently has 49 pre-existing historical style issues.
# Rather than creating an invasive 49-file churn across the codebase, this
# ratchet ensures:
# 1. Unchanged legacy files retain their historical debt temporarily.
# 2. Every new or modified PHP file must strictly pass Pint.
# 3. Any touched legacy PHP file in a future task must be brought into full
#    compliance before CI passes.
# 4. A dedicated future formatting task can eliminate baseline debt and restore
#    repository-wide `pint --test`.
# =============================================================================
set -euo pipefail

PINT_RATCHET_BASE="6f6ec58662f6e5b8db3fe6ecf9b6aa281da50f87"
TARGET_REF="${1:-HEAD}"

# Verify Git environment
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Error: Not inside a Git repository."
    exit 1
fi

# Verify Pint binary exists
PINT_BIN="./vendor/bin/pint"
if [ ! -x "$PINT_BIN" ]; then
    echo "❌ Error: Pint binary not found or not executable at $PINT_BIN"
    exit 1
fi

# Check if baseline commit exists in repo
if ! git cat-file -e "${PINT_RATCHET_BASE}^{commit}" > /dev/null 2>&1; then
    echo "❌ Error: Pint ratchet baseline commit $PINT_RATCHET_BASE is missing from Git history."
    echo "   Ensure checkout includes full history (e.g. actions/checkout@v4 with fetch-depth: 0)."
    exit 1
fi

# Verify target ref is valid
if ! git rev-parse --verify "${TARGET_REF}" > /dev/null 2>&1; then
    echo "❌ Error: Target ref '$TARGET_REF' cannot be resolved."
    exit 1
fi

# Verify baseline is an ancestor of target ref (or target is baseline itself)
if [ "$TARGET_REF" != "$PINT_RATCHET_BASE" ]; then
    if ! git merge-base --is-ancestor "$PINT_RATCHET_BASE" "$TARGET_REF" 2>/dev/null; then
        echo "❌ Error: Baseline commit $PINT_RATCHET_BASE is not an ancestor of $TARGET_REF."
        exit 1
    fi
fi

# Collect added, copied, modified, or renamed PHP files between baseline and target
CHANGED_PHP_FILES=()

# Get committed differences between baseline and TARGET_REF
while IFS= read -r file; do
    if [ -n "$file" ] && [ -f "$file" ]; then
        CHANGED_PHP_FILES+=("$file")
    fi
done < <(git diff --name-only --diff-filter=ACMR "$PINT_RATCHET_BASE" "$TARGET_REF" -- '*.php')

# If target is HEAD, also detect working tree staged, unstaged, or untracked PHP files
if [ "$TARGET_REF" = "HEAD" ]; then
    while IFS= read -r file; do
        if [ -n "$file" ] && [ -f "$file" ]; then
            # Avoid duplicates
            found=0
            for existing in "${CHANGED_PHP_FILES[@]:-}"; do
                if [ "$existing" = "$file" ]; then
                    found=1
                    break
                fi
            done
            if [ "$found" -eq 0 ]; then
                CHANGED_PHP_FILES+=("$file")
            fi
        fi
    done < <(git status --porcelain | awk '{print $NF}' | grep '\.php$' || true)
fi

echo "============================================================"
echo "Pint Quality Ratchet"
echo "Baseline : $PINT_RATCHET_BASE"
echo "Target   : $TARGET_REF"
echo "============================================================"

if [ ${#CHANGED_PHP_FILES[@]} -eq 0 ]; then
    echo "✅ No PHP files added or modified since baseline $PINT_RATCHET_BASE."
    echo "   Pint ratchet skipped (0 files to check)."
    exit 0
fi

echo "Checking ${#CHANGED_PHP_FILES[@]} post-baseline PHP file(s):"
for f in "${CHANGED_PHP_FILES[@]}"; do
    echo "  - $f"
done
echo "------------------------------------------------------------"

# Run Pint in test mode against changed files
"$PINT_BIN" --test "${CHANGED_PHP_FILES[@]}"

echo "============================================================"
echo "✅ All post-baseline PHP files passed Pint inspection!"
echo "============================================================"
