#!/bin/sh
# =============================================================
#  Run the whole backend test suite.
#
#  Prerequisites (local simulation, no Docker):
#    1. a MySQL/MariaDB with db/init.sql loaded
#    2. the app served by `php -S 0.0.0.0:8080 index.php` (from app/)
#
#  Usage:  ./tests/run-all.sh [base-url]
# =============================================================
set -e
cd "$(dirname "$0")/.."

BASE="${1:-http://127.0.0.1:8080}"
RC=0

echo "############################################################"
echo "# 1/4  PHP syntax check (every file)"
echo "############################################################"
find app -name '*.php' -print0 | xargs -0 -n1 php -l > /dev/null
echo "  all PHP files parse"

echo
echo "############################################################"
echo "# 1.5  Reset test data (best effort)"
echo "############################################################"
if [ "${RESET_DB:-1}" = "1" ]; then
    php tests/reset.php --yes || echo "  (skipped - database not reachable)"
fi

echo
echo "############################################################"
echo "# 2/4  Unit tests"
echo "############################################################"
php tests/test_units.php || RC=1

echo
echo "############################################################"
echo "# 3/4  HtmlSanitizer suite"
echo "############################################################"
php tests/test_sanitizer.php || RC=1

echo
echo "############################################################"
echo "# 4/4  End-to-end HTTP suite  (${BASE})"
echo "############################################################"
if curl -fsS -o /dev/null "${BASE}/healthz"; then
    python3 tests/test_e2e.py "${BASE}" || RC=1
else
    echo "  SKIPPED - no server answering on ${BASE}"
    echo "  start one with:  (cd app && php -S 0.0.0.0:8080 index.php) &"
    RC=1
fi

echo
if [ "$RC" -eq 0 ]; then
    echo ">>> ALL SUITES GREEN"
else
    echo ">>> FAILURES PRESENT"
fi
exit $RC
