#!/usr/bin/env bash
set -e
cd "$(dirname "$0")/lab"
if ! command -v node >/dev/null; then
  echo "Node.js نصب نیست."
  exit 1
fi
[ -d node_modules ] || npm install
[ -f dist/index.html ] || npm run build
echo "http://localhost:8080/simulations.html"
exec node server/index.js
