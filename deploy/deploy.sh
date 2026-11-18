#!/usr/bin/env bash
#
# Deploy ReValue langsung di server produksi dengan Docker Compose.
#
set -euo pipefail

APP_DIR="${APP_DIR:-/opt/revalue}"
COMPOSE_FILE="${COMPOSE_FILE:-compose.prod.yml}"

cd "$APP_DIR"

echo "==> Sinkronisasi kode dari GitHub"
git fetch --prune origin main
git reset --hard origin/main

compose() {
    docker compose -f "$COMPOSE_FILE" "$@"
}

echo "==> Membangun image Docker dan menyalakan database"
compose build
compose up -d db

DB_ROOT_PASSWORD="$(grep -E '^DB_ROOT_PASSWORD=' .env | cut -d= -f2-)"

echo "==> Menunggu MySQL siap"
for _ in $(seq 1 60); do
    if compose exec -T db mysqladmin ping \
        -h 127.0.0.1 -uroot -p"${DB_ROOT_PASSWORD}" --silent >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

echo "==> Menjalankan migrasi database"
compose run --rm app php artisan migrate --force

echo "==> Memeriksa apakah database masih kosong"
USER_COUNT="$(
    compose run --rm -T app \
        php artisan tinker --execute='echo "USERS=".\App\Models\User::count();' \
        | tr -d '\r' \
        | grep -o 'USERS=[0-9]*' \
        | head -n 1 \
        | cut -d= -f2 || true
)"

if [ -z "${USER_COUNT}" ]; then
    echo "!! Gagal membaca jumlah pengguna, seeder dilewati agar data aman"
elif [ "${USER_COUNT}" = "0" ]; then
    echo "==> Database kosong, menjalankan seeder awal"
    compose run --rm app php artisan db:seed --force
else
    echo "==> Database sudah berisi ${USER_COUNT} pengguna, seeder dilewati"
fi

echo "==> Menjalankan seluruh service (app, queue, scheduler, db)"
compose up -d --remove-orphans

echo "==> Health check"
compose exec -T app curl -fsS http://127.0.0.1:8005/up >/dev/null

docker image prune -f >/dev/null 2>&1 || true

echo "==> Deploy berhasil!"
