#!/usr/bin/env bash
#
# Deploy ReValue ke server produksi.
#
# Pemakaian:
#   ./deploy/deploy.sh <commit-sha> [image-tag]
#
# Catatan:
# - .env TIDAK di-commit dan TIDAK ditimpa oleh script ini (dikelola workflow).
# - Seeder hanya dijalankan saat tabel users masih kosong, agar perubahan
#   password/data yang dibuat lewat aplikasi tidak ter-reset tiap deploy.
set -euo pipefail

APP_DIR="${APP_DIR:-/opt/revalue}"
COMPOSE_FILE="${COMPOSE_FILE:-compose.prod.yml}"

COMMIT="${1:?commit sha wajib dikirim sebagai argumen pertama}"
TAG="${2:-}"

cd "$APP_DIR"

echo "==> Sinkronisasi kode"
git fetch --prune origin main
git reset --hard origin/main

export IMAGE_NAME="${IMAGE_NAME:-ghcr.io/tunggulalmajid/technovesary-vol-2}"
export IMAGE_TAG="${TAG:-sha-${COMMIT:0:7}}"

compose() {
    docker compose -f "$COMPOSE_FILE" "$@"
}

echo "==> Menarik image ${IMAGE_NAME}:${IMAGE_TAG}"
compose pull

echo "==> Menjalankan database"
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

echo "==> Menjalankan migrasi"
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
    echo "==> Database kosong, menjalankan seeder"
    compose run --rm app php artisan db:seed --force
else
    echo "==> Database sudah berisi ${USER_COUNT} pengguna, seeder dilewati"
fi

echo "==> Menjalankan seluruh service"
compose up -d --remove-orphans

echo "==> Health check"
compose exec -T app curl -fsS http://127.0.0.1:8005/up >/dev/null

docker image prune -f >/dev/null

echo "==> Deploy ${IMAGE_TAG} berhasil"
