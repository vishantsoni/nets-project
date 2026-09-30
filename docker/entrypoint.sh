#!/bin/sh
set -e

cd /var/www/html

# Vercel and most PaaS platforms inject the listen port as $PORT.
PORT="${PORT:-8000}"
export PORT

# Render the nginx vhost with the actual port.
# Uses sed rather than envsubst: gettext is not present in the php alpine image,
# and the template only contains nginx-safe placeholders.
sed "s|\${PORT}|${PORT}|g" \
    /etc/nginx/templates/default.conf.template \
    > /etc/nginx/http.d/default.conf

# Serverless/managed filesystems are read-only outside of /tmp.
if [ -n "$VIEW_COMPILED_PATH" ]; then
    mkdir -p "$VIEW_COMPILED_PATH" 2>/dev/null || true
fi

mkdir -p \
    /tmp/views \
    /tmp/cache/data \
    /tmp/sessions \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    2>/dev/null || true

if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set. Set it in the environment." >&2
fi

# Background workers are opt-in; serverless platforms usually run them elsewhere.
if [ "$RUN_QUEUE" = "true" ] && command -v supervisorctl >/dev/null 2>&1; then
    supervisorctl -c /etc/supervisord.conf start queue || true
fi

if [ "$RUN_SCHEDULER" = "true" ] && command -v supervisorctl >/dev/null 2>&1; then
    supervisorctl -c /etc/supervisord.conf start scheduler || true
fi

exec "$@"