#!/bin/sh
set -eu
: "${DB_HOST:?}" "${DB_DATABASE:?}" "${DB_USERNAME:?}" "${PGPASSWORD:?}" "${BACKUP_DIR:?}"
case "$BACKUP_DIR" in /*) ;; *) echo "BACKUP_DIR must be absolute" >&2; exit 2;; esac
mkdir -p "$BACKUP_DIR"
stamp=$(date -u +%Y%m%dT%H%M%SZ)
target="$BACKUP_DIR/llegamos-$stamp.dump"
pg_dump --format=custom --no-owner --host="$DB_HOST" --port="${DB_PORT:-5432}" --username="$DB_USERNAME" --file="$target" "$DB_DATABASE"
sha256sum "$target" > "$target.sha256"
find "$BACKUP_DIR" -maxdepth 1 -type f -name 'llegamos-*.dump*' -mtime "+${BACKUP_RETENTION_DAYS:-30}" -delete
printf '%s\n' "$target"