# Kredensial Deployment — SPK Desa KI Bali

Domain: spkdesa-kibali.my.id  
Dokploy Panel: dokploy.spkdesa-kibali.my.id  
Server: 43.157.197.43  
SSH Alias: `spk-desa` (user: ubuntu)

## MySQL Production

| Parameter | Value |
|---|---|
| Host (internal) | mysql (Docker network) |
| Host (external) | 127.0.0.1:3306 |
| Database | spk_desa |
| Username | spk |
| Password | FHi7ufAp1pbEibbJqPHvcrlz6GK80jw |
| Root Username | root@localhost |
| Root Password | fxFNvOm6siMEpwftuRNwuQpI6cSRH |
| Charset | utf8mb4_unicode_ci |
| Engine | MySQL 8.4 (caching_sha2_password) |

## Dokploy Panel

| Parameter | Value |
|---|---|
| URL | https://dokploy.spkdesa-kibali.my.id |
| Email | admin@example.com |
| Password | Uz6aGQXAkWFUTvI3 |

## Laravel Production

| Parameter | Value |
|---|---|
| APP_URL | https://spkdesa-kibali.my.id |
| APP_ENV | production |
| APP_DEBUG | false |
| SESSION_DRIVER | redis |
| SESSION_DOMAIN | spkdesa-kibali.my.id |
| SESSION_SECURE_COOKIE | true |
| CACHE_STORE | redis |
| QUEUE_CONNECTION | database |

## Dokploy Compose Stack

- Compose ID: `i7fO2UD4uumalIAeMmKm0`
- Traefik Labels: explicit via `docker-compose.yml` (service `app`)
- Docker Network: `dokploy-network`
- Static Route File: removed (was `/etc/dokploy/traefik/dynamic/spkdesa-route.yml`)

## Catatan

- Password ini di-generate secara acak pada 2026-06-03.
- Simpan file ini di `.gitignore` jika repository ini publik (saat ini hanya internal).
- Untuk ganti password: update di MySQL + update `DB_PASSWORD` di Dokploy `.env` + recreate container.
- Root MySQL hanya bisa login via socket lokal (plugin `caching_sha2_password` + auth_string kosong), tidak bisa via TCP dengan password root (perlu skip-grant-tables untuk reset).

---

Terakhir diperbarui: 2026-06-03
