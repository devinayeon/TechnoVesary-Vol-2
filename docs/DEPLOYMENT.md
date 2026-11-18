# Panduan Deployment ReValue

Dokumen ini menjelaskan alur CI/CD berbasis Docker untuk aplikasi ReValue
(`https://revalue.kodetalma.my.id`).

## Ringkasan arsitektur

```
GitHub Actions
  ├── CI (ci.yml)      : Pint, PHPStan, Pest, vue-tsc, vp check, vite build
  └── Deploy (deploy.yml)
        ├── build & push image -> ghcr.io/tunggulalmajid/technovesary-vol-2
        └── SSH ke server -> deploy/deploy.sh

Server (VPS)
  nginx host :80/:443 (Certbot)
        └── 127.0.0.1:8005
              └── docker compose "revalue"
                    ├── app        (nginx + php-fpm, port 8005)
                    ├── queue      (php artisan queue:work)
                    ├── scheduler  (php artisan schedule:work)
                    └── db         (mysql:8.0)
  volume: revalue_storage, revalue_dbdata
```

## 1. Persiapan repository (GitHub)

### 1.1 Workflow permissions

`Settings -> Actions -> General -> Workflow permissions` pilih
**Read and write permissions** (dibutuhkan untuk push image ke GHCR).

### 1.2 Environment

`Settings -> Environments -> New environment` dengan nama `production`.
Disarankan menambahkan **Required reviewers** agar deploy dapat dikontrol.

### 1.3 Secrets

`Settings -> Secrets and variables -> Actions -> New repository secret`:

| Nama                 | Keterangan                                                    |
| -------------------- | ------------------------------------------------------------- |
| `SSH_HOST`           | IP atau domain server                                         |
| `SSH_USER`           | user deploy (mis. `deploy`)                                   |
| `SSH_PRIVATE_KEY`    | private key SSH (format OpenSSH, isi lengkap termasuk header) |
| `SSH_PORT`           | opsional, default `22`                                        |
| `PRODUCTION_ENV`     | isi teks `.env` produksi yang langsung ditempel (plaintext)   |
| `PRODUCTION_ENV_B64` | alternatif: isi `.env` produksi dalam format base64           |

Opsional (dipakai sebagai build arg saat membangun asset):

| Nama                   | Jenis    | Keterangan                                   |
| ---------------------- | -------- | -------------------------------------------- |
| `VITE_BASEMAP_API_KEY` | Variable | API key Carto basemap yang di-bake ke bundle |

> `VITE_*` ditanam saat build, bukan saat runtime. Jika tidak diisi, frontend
> memakai fallback key yang sudah ada di `resources/js/lib/map/basemap.ts`.

Membuat `PRODUCTION_ENV_B64`:

```bash
# Linux / macOS
base64 -w0 .env.production
```

```powershell
# Windows PowerShell
[Convert]::ToBase64String([IO.File]::ReadAllBytes(".env.production"))
```

> `.env` produksi ditulis ulang dari secret ini pada setiap deploy. Jangan
> melakukan perubahan manual `.env` di server karena akan tertimpa.

### 1.3.1 Membuat SSH key untuk deploy (dibuat di server Ubuntu)

Dianjurkan memakai key **khusus deploy**, terpisah dari key pribadi Anda.
Jalankan di server:

```bash
# 1) Buat user deploy (lewati jika sudah ada)
sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG docker deploy
sudo mkdir -p /home/deploy/.ssh && sudo chmod 700 /home/deploy/.ssh

# 2) Generate key pair TANPA passphrase (dibutuhkan CI non-interaktif)
sudo -u deploy ssh-keygen -t ed25519 -C "github-actions@revalue" \
  -f /home/deploy/.ssh/github_deploy -N ""

# 3) Izinkan key tersebut login
sudo -u deploy sh -c 'cat /home/deploy/.ssh/github_deploy.pub >> /home/deploy/.ssh/authorized_keys'
sudo chmod 600 /home/deploy/.ssh/authorized_keys
sudo chown -R deploy:deploy /home/deploy/.ssh

# 4) Tampilkan PRIVATE key -> salin ke GitHub Secret SSH_PRIVATE_KEY
sudo cat /home/deploy/.ssh/github_deploy
```

Yang disalin ke secret adalah **seluruh isi private key**, termasuk baris:

```
-----BEGIN OPENSSH PRIVATE KEY-----
...
-----END OPENSSH PRIVATE KEY-----
```

`SSH_USER` diisi `deploy`. Setelah secret disimpan, private key di server
sebaiknya dihapus dari disk (secret GitHub sudah menyimpannya):

```bash
sudo shred -u /home/deploy/.ssh/github_deploy
```

Uji login dari komputer Anda sebelum push:

```bash
ssh -i /path/ke/github_deploy deploy@revalue.kodetalma.my.id
```

> Jika Anda memakai user lain (mis. `ubuntu`), ganti `deploy` pada langkah di
> atas dan pada `SSH_USER`. User tersebut tetap harus ada di grup `docker`
> dan menjadi pemilik `/opt/revalue`.

### 1.4 Branch protection

`Settings -> Branches -> Add branch protection rule` untuk `main`:
aktifkan **Require status checks to pass** dan pilih
`Backend (lint, static analysis, test)` serta `Frontend (types, lint, build)`.

### 1.5 Visibilitas package GHCR

Setelah push pertama, buka `Packages -> technovesary-vol-2 ->
Package settings -> Change visibility -> Public`. Dengan begitu server dapat
menarik image tanpa kredensial tambahan.

## 2. Persiapan server

Asumsi: Ubuntu 22.04/24.04, akses `sudo`, domain sudah diarahkan ke IP server.

### 2.1 Docker

```bash
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker "$USER"
newgrp docker
docker compose version
```

### 2.2 Direktori aplikasi

```bash
sudo mkdir -p /opt/revalue
sudo chown "$USER":"$USER" /opt/revalue
git clone https://github.com/tunggulalmajid/TechnoVesary-Vol-2.git /opt/revalue
chmod +x /opt/revalue/deploy/deploy.sh
```

### 2.3 Nginx & Certbot

Langkah 1 — konfigurasi HTTP sementara
(`/etc/nginx/sites-available/revalue.kodetalma.my.id`):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name revalue.kodetalma.my.id www.revalue.kodetalma.my.id;

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        return 301 https://$host$request_uri;
    }
}
```

```bash
sudo apt install -y nginx certbot python3-certbot-nginx
sudo mkdir -p /var/www/certbot
sudo ln -sf /etc/nginx/sites-available/revalue.kodetalma.my.id /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/revalue.conf
sudo nginx -t && sudo systemctl reload nginx
```

Langkah 2 — terbitkan sertifikat:

```bash
sudo certbot certonly --webroot -w /var/www/certbot \
  -d revalue.kodetalma.my.id -d www.revalue.kodetalma.my.id \
  --email admin@kodetalma.my.id --agree-tos --no-eff-email
```

Langkah 3 — konfigurasi final (ganti seluruh isi file):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name revalue.kodetalma.my.id www.revalue.kodetalma.my.id;

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        return 301 https://$host$request_uri;
    }
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    http2 on; # nginx < 1.25: gunakan "listen 443 ssl http2;"
    server_name revalue.kodetalma.my.id www.revalue.kodetalma.my.id;

    client_max_body_size 64M;

    ssl_certificate     /etc/letsencrypt/live/revalue.kodetalma.my.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/revalue.kodetalma.my.id/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    location / {
        proxy_pass http://127.0.0.1:8005;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_connect_timeout 60s;
        proxy_send_timeout 300s;
        proxy_read_timeout 300s;
    }
}
```

```bash
sudo nginx -t && sudo systemctl reload nginx
sudo certbot renew --dry-run
```

### 2.4 Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80,443/tcp
sudo ufw enable
```

Port `8005` sengaja **tidak** dibuka karena hanya di-bind ke `127.0.0.1`.

## 3. Deploy

### 3.1 Otomatis

Push ke `main` (atau merge PR) akan memicu `Deploy` workflow:

1. Build image `linux/amd64` dan push dengan tag `sha-<7 karakter>` + `latest`.
2. SSH ke server, tulis `.env` dari secret, jalankan `deploy/deploy.sh`.

`deploy.sh` melakukan:

1. `git fetch` + `git reset --hard origin/main`
2. `docker compose pull`
3. `docker compose up -d db` + menunggu MySQL sehat
4. `php artisan migrate --force`
5. `db:seed` **hanya jika tabel `users` kosong**
6. `docker compose up -d --remove-orphans`
7. Health check `GET /up`

### 3.2 Manual / rollback

`Actions -> Deploy -> Run workflow`:

- `image_tag` kosong -> build dari commit terbaru lalu deploy.
- `image_tag` diisi (mis. `sha-1a2b3c4`) -> deploy image lama tanpa build.

### 3.3 Deploy manual di server

```bash
cd /opt/revalue
./deploy/deploy.sh "$(git rev-parse HEAD)"
```

## 4. Verifikasi

```bash
cd /opt/revalue
docker compose -f compose.prod.yml ps
docker compose -f compose.prod.yml logs -f app
curl -I https://revalue.kodetalma.my.id/up
```

Login dengan akun hasil seeder (`admin@banksampah.test` / `password`) lalu
**segera ganti kata sandi**.

## 5. Backup & restore

Backup database:

```bash
cd /opt/revalue
docker compose -f compose.prod.yml exec -T db \
  sh -c 'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' \
  > backup-$(date +%F).sql
```

Backup file upload (volume `revalue_storage`):

```bash
docker run --rm -v revalue_storage:/data -v "$PWD":/backup alpine \
  tar czf /backup/storage-$(date +%F).tar.gz -C /data .
```

Restore:

```bash
docker compose -f compose.prod.yml exec -T db \
  sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' \
  < backup-2026-01-01.sql

docker run --rm -v revalue_storage:/data -v "$PWD":/backup alpine \
  sh -c 'tar xzf /backup/storage-2026-01-01.tar.gz -C /data'
```

## 6. Troubleshooting

| Gejala                                   | Penyebab umum                       | Solusi                                                    |
| ---------------------------------------- | ----------------------------------- | --------------------------------------------------------- |
| `Class "App\Services\..." not found`     | folder/namespace beda huruf         | pastikan direktori `app/Services` (huruf besar)           |
| Halaman 500, log `could not find driver` | ekstensi `pdo_mysql` tidak ada      | image harus dibangun ulang dari `Dockerfile`              |
| Asset 404 setelah deploy                 | `public/build` tidak ikut ter-build | cek log build; jalankan `npm run build` di job            |
| Redirect/asset memakai `http://`         | proxy header tidak diteruskan       | `trustProxies` + header `X-Forwarded-Proto` di nginx host |
| `SQLSTATE[HY000] [2002]`                 | `DB_HOST` bukan `db`                | sesuaikan `.env` (`DB_HOST=db`)                           |
| Login selalu gagal setelah deploy        | `APP_KEY` berubah                   | gunakan `APP_KEY` tetap di secret                         |
| `db:seed` menimpa data                   | seeder dijalankan manual di server  | biarkan `deploy.sh` yang menentukan                       |

## 7. Catatan keamanan

- `.env` disimpan sebagai GitHub Secret dan ditulis dengan permission `600`.
- Seeder produksi membuat akun default; ganti kata sandi setelah deploy pertama.
- Jangan menjalankan `php artisan migrate:fresh` di server produksi.
- Rotasi `APP_KEY` akan membatalkan seluruh session dan data terenkripsi.

## 8. Setup cepat dari nol (urutan yang disarankan)

Diasumsikan Docker sudah terpasang dan domain sudah mengarah ke IP server.

```bash
# 0) Verifikasi Docker
docker --version
docker compose version

# 1) Nginx + Certbot (HTTP dulu)
sudo apt install -y nginx certbot
sudo mkdir -p /var/www/certbot
sudo nano /etc/nginx/sites-available/revalue.kodetalma.my.id   # isi blok HTTP (§2.3 langkah 1)
sudo ln -sf /etc/nginx/sites-available/revalue.kodetalma.my.id /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# 2) Terbitkan sertifikat
sudo certbot certonly --webroot -w /var/www/certbot \
  -d revalue.kodetalma.my.id -d www.revalue.kodetalma.my.id \
  --email admin@kodetalma.my.id --agree-tos --no-eff-email

# 3) Ganti config nginx dengan versi HTTPS final (§2.3 langkah 3)
sudo nginx -t && sudo systemctl reload nginx
sudo certbot renew --dry-run

# 4) Clone repo
sudo mkdir -p /opt/revalue
sudo chown "$USER":"$USER" /opt/revalue
git clone https://github.com/tunggulalmajid/TechnoVesary-Vol-2.git /opt/revalue
chmod +x /opt/revalue/deploy/deploy.sh

# 5) Siapkan .env (boleh manual; nanti ditimpa workflow dari secret)
cd /opt/revalue
cp .env.production.example .env
nano .env            # isi APP_KEY, DB_PASSWORD, DB_ROOT_PASSWORD, MAIL_*
chmod 600 .env

# 6) Deploy pertama
./deploy/deploy.sh "$(git rev-parse HEAD)"
```

> `deploy.sh` menarik image dari GHCR. Pastikan image sudah pernah di-push
> oleh workflow `Deploy` (push ke `main`), atau bangun manual:
> `docker build -t ghcr.io/tunggulalmajid/technovesary-vol-2:latest .`

## 9. Operasional harian (cheat sheet)

Semua perintah dijalankan di `/opt/revalue`.

```bash
alias dc='docker compose -f compose.prod.yml'
```

| Kebutuhan             | Perintah                                       |
| --------------------- | ---------------------------------------------- |
| Status service        | `dc ps`                                        |
| Log aplikasi (live)   | `dc logs -f app`                               |
| Log queue             | `dc logs -f queue`                             |
| Log scheduler         | `dc logs -f scheduler`                         |
| Log database          | `dc logs -f db`                                |
| Restart semua         | `dc restart`                                   |
| Restart app saja      | `dc restart app`                               |
| Hentikan semua        | `dc down` (volume tetap aman)                  |
| Masuk shell container | `dc exec app sh`                               |
| Artisan               | `dc exec app php artisan <cmd>`                |
| Tinker                | `dc exec app php artisan tinker`               |
| Migrasi manual        | `dc exec app php artisan migrate --force`      |
| Bersihkan cache       | `dc exec app php artisan optimize:clear`       |
| Rebuild cache         | `dc exec app php artisan optimize`             |
| Health check          | `curl -fsS https://revalue.kodetalma.my.id/up` |
| Update aplikasi       | `./deploy/deploy.sh "$(git rev-parse HEAD)"`   |
| Rollback image        | `IMAGE_TAG=sha-<7char> dc up -d`               |
| Pemakaian disk        | `docker system df`                             |
| Bersihkan image lama  | `docker image prune -f`                        |
| Pemakaian volume      | `docker volume ls`                             |

### 9.1 Mengubah environment

`.env` di server adalah hasil salinan dari GitHub Secret `PRODUCTION_ENV_B64`
dan akan **ditimpa setiap deploy**. Jadi:

1. Ubah `.env` lokal (dari `.env.production.example`).
2. Encode base64 dan perbarui secret `PRODUCTION_ENV_B64`.
3. Jalankan ulang workflow `Deploy` (atau `docker compose up -d --force-recreate`).
4. `dc exec app php artisan optimize` (otomatis dijalankan entrypoint saat recreate).

### 9.2 Menjalankan perintah artisan saat maintenance

```bash
dc exec app php artisan down --secret="token-sementara"
# akses https://revalue.kodetalma.my.id/token-sementara
dc exec app php artisan up
```

### 9.3 Melihat log jika container gagal start

```bash
dc ps -a
dc logs --tail=200 app
docker inspect --format='{{.State.ExitCode}}' revalue-app-1
```

### 9.4 Update sertifikat

Certbot memasang timer otomatis. Verifikasi:

```bash
sudo systemctl list-timers | grep certbot
sudo certbot renew --dry-run
```
