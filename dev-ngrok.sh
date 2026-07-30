#!/usr/bin/env bash
#
# Start the Vite dev server in "ngrok/tunnel" mode.
#
# Usage:
#   ./dev-ngrok.sh https://abc-123.ngrok-free.app
#
# This sets VITE_DEV_SERVER_URL so Vite writes the public tunnel URL into
# public/hot. Laravel then serves asset URLs from the tunnel origin, avoiding
# CORS and mixed-content errors when previewing the app over HTTPS via ngrok.
#
# Run your Laravel server and ngrok tunnel separately:
#   php artisan serve
#   ngrok http 8000
#
# Note: Vite itself still binds to 0.0.0.0:5173 on your machine; the browser
# reaches it through the tunnel via the origin you pass here.

set -euo pipefail

URL="${1:-${VITE_DEV_SERVER_URL:-}}"
if [[ -z "$URL" ]]; then
    echo "Usage: ./dev-ngrok.sh <https://your-tunnel.ngrok-free.app>" >&2
    exit 1
fi

echo "Starting Vite in tunnel mode with origin: $URL"
export VITE_DEV_SERVER_URL="$URL"
exec npm run dev
