#!/bin/sh
# spk-desa helper — satu command untuk semua kebutuhan dev local.
#
# Target: WSL2 (Ubuntu) + podman + podman-compose. POSIX sh.
#
#   ./spk.sh check   # cek WSL / podman / podman-compose terpasang
#   ./spk.sh run     # up stack + composer/npm install + migrate+seed + vite dev (foreground)
#   ./spk.sh down    # stop stack (volume DB dipertahankan)
#   ./spk.sh bash    # shell interaktif di container laravel.test
#   ./spk.sh pull    # git pull; perubahan lokal -> branch baru -> push -> PR ke main
#
# Opsi global: --no-wsl  (lewati cek WSL; untuk berjalan di host non-WSL seperti macOS)
set -u

APP_SERVICE="laravel.test"
APP_PORT="8080"
VITE_PORT="5173"
REPO_URL="https://github.com/Central-Saga/spk-desa"
BASE_BRANCH="main"

c_info() { printf '\033[1;34m==>\033[0m %s\n' "$*"; }
c_ok()   { printf '\033[1;32m  ✓\033[0m %s\n' "$*"; }
c_err()  { printf '\033[1;31m  ✗\033[0m %s\n' "$*" >&2; }

die() { c_err "$*"; exit 1; }

need_git_root() {
    [ -f compose.yaml ] || die "compose.yaml tidak ditemukan — jalankan dari repo root spk-desa."
}

have() { command -v "$1" >/dev/null 2>&1; }

# ---------- check ------------------------------------------------------------

check() {
    no_wsl=0
    for arg in "$@"; do
        [ "$arg" = "--no-wsl" ] && no_wsl=1
    done

    missing=""

    if [ "$no_wsl" -eq 1 ]; then
        c_ok "WSL check dilewati (--no-wsl)"
    elif [ -n "${WSL_DISTRO_NAME:-}" ] || grep -qi microsoft /proc/version 2>/dev/null; then
        c_ok "WSL terdeteksi"
    else
        c_err "Tidak berjalan di WSL. Install: wsl --install -d Ubuntu (dari PowerShell admin)."
        missing="$missing wsl"
    fi

    if have podman; then
        c_ok "podman: $(podman --version 2>/dev/null || echo 'terpasang')"
    else
        c_err "podman tidak ada. Install: sudo apt-get update && sudo apt-get install -y podman"
        missing="$missing podman"
    fi

    if have podman-compose; then
        c_ok "podman-compose: $(podman-compose --version 2>/dev/null | head -1 || echo 'terpasang')"
    else
        c_err "podman-compose tidak ada. Install: sudo apt-get install -y podman-compose (atau pipx install podman-compose)"
        missing="$missing podman-compose"
    fi

    if [ -n "$missing" ]; then
        c_err "Lengkapi dulu:$missing"
        exit 1
    fi
    c_ok "Semua siap."
}

# ---------- run --------------------------------------------------------------

# Tambahkan kunci compose yang hilang ke .env (APP_PORT, WWWUSER, VITE_PORT, FORWARD_*).
ensure_env_compose_keys() {
    # id -u/-g: uid WSL user; root di container pakai WWWGROUP agar bind-mount writable.
    keys="APP_PORT=$APP_PORT
WWWGROUP=
WWWUSER=$(id -u 2>/dev/null || echo 1000)
VITE_PORT=$VITE_PORT
FORWARD_DB_PORT=3306
FORWARD_REDIS_PORT=6379
FORWARD_MAILPIT_PORT=1025
FORWARD_MAILPIT_DASHBOARD_PORT=8025"

    # WWWGROUP: bila .env sudah punya nilai non-kosong, pertahankan; bila kosong = WWWUSER.
    if grep -q '^WWWGROUP=..*' .env 2>/dev/null; then
        keys=$(printf '%s\n' "$keys" | grep -v '^WWWGROUP=')
    else
        keys=$(printf '%s\n' "$keys" | sed "s/^WWWGROUP=$/WWWGROUP=$(id -g 2>/dev/null || echo 1000)/")
    fi

    while IFS= read -r line; do
        key=${line%%=*}
        val=${line#*=}
        if grep -q "^${key}=" .env; then
            # Kunci ada: bila kosong -> isi; bila sudah ada nilai -> biarkan.
            if ! grep -q "^${key}=..*" .env; then
                sed -i.bak "s|^${key}=|${key}=${val}|" .env && rm -f .env.bak
            fi
        else
            printf '%s\n' "$line" >> .env
        fi
    done <<EOF
$keys
EOF
}

compose_cmd() {
    if have podman-compose; then
        podman-compose "$@"
    elif podman compose version >/dev/null 2>&1; then
        podman compose "$@"
    else
        die "podman-compose / podman compose tidak tersedia."
    fi
}

wait_mysql() {
    c_info "Menunggu mysql healthy..."
    i=0
    while [ "$i" -lt 60 ]; do
        if compose_cmd exec -T mysql sh -c 'mysqladmin ping -uroot -p"$MYSQL_ROOT_PASSWORD" --silent' >/dev/null 2>&1; then
            c_ok "mysql siap."
            return 0
        fi
        i=$((i + 1))
        sleep 2
        done
    die "mysql tidak kunjung siap (120s). Cek: podman ps; podman logs \$(podman ps -qf name=mysql)"
}

run() {
    no_wsl=0
    for arg in "$@"; do
        [ "$arg" = "--no-wsl" ] && no_wsl=1
    done

    need_git_root
    check $([ "$no_wsl" -eq 1 ] && echo --no-wsl)
    cd "$(git rev-parse --show-toplevel)" || die "gagal masuk repo root"

    # 1. .env: copy dari contoh bila belum ada, lalu pastikan kunci compose ada.
    if [ ! -f .env ]; then
        [ -f .env.example ] || die ".env dan .env.example tidak ada."
        cp .env.example .env
        c_ok ".env dibuat dari .env.example"
    fi
    ensure_env_compose_keys

    # 2. Up stack (build image sail-8.5/app bila belum ada).
    c_info "podman-compose up -d (build pertama bisa 5-10 menit)..."
    compose_cmd up -d --build || die "podman-compose up gagal."

    wait_mysql

    # 3. Sekali saja: APP_KEY, dependensi, migrate+seed (semua seeder idempotent).
    if ! grep -q '^APP_KEY=base64' .env; then
        c_info "APP_KEY kosong -> generate..."
        compose_cmd exec -T "$APP_SERVICE" php artisan key:generate --force \
            || die "key:generate gagal."
    fi

    if [ ! -d vendor ]; then
        c_info "vendor tiada (repo gitignored) -> composer install (beberapa menit)..."
        compose_cmd exec -T "$APP_SERVICE" composer install --no-interaction --prefer-dist \
            || die "composer install gagal."
    fi

    if [ ! -d node_modules ]; then
        c_info "node_modules tiada -> npm install (beberapa menit)..."
        compose_cmd exec -T "$APP_SERVICE" npm install \
            || die "npm install gagal."
    fi

    if [ ! -f storage/DB_ALREADY_SEEDED ]; then
        c_info "migrate --seed (idempotent)..."
        compose_cmd exec -T "$APP_SERVICE" php artisan migrate --seed --force \
            || die "migrate --seed gagal."
        touch storage/DB_ALREADY_SEEDED
    else
        c_ok "migrate+seed pernah jalan, lewati (hapus storage/DB_ALREADY_SEEDED untuk paksa ulang)."
    fi

    # 4. Vite dev server foreground (Ctrl-C stop Vite; stack container tetap -d).
    c_info "Vite dev: http://localhost:$APP_PORT (HMR $VITE_PORT). Ctrl-C untuk stop Vite."
    compose_cmd exec -T "$APP_SERVICE" sh -c "VITE_PORT=$VITE_PORT LARAVEL_VITE_DEV=1 npm run dev"
}

# ---------- down ---------------------------------------------------------------

down() {
    need_git_root
    cd "$(git rev-parse --show-toplevel)" || die "gagal masuk repo root"
    c_info "podman-compose down (volume DB dipertahankan)..."
    compose_cmd down
    c_ok "Stack mati. Reset total DB: podman volume rm sail-mysql sail-redis"
}

# ---------- bash ---------------------------------------------------------------

bash_cmd() {
    need_git_root
    cd "$(git rev-parse --show-toplevel)" || die "gagal masuk repo root"
    compose_cmd exec "$APP_SERVICE" bash
}

# ---------- pull ---------------------------------------------------------------

pull() {
    need_git_root
    have git || die "git tidak ada."

    if [ -n "$(git status --porcelain)" ]; then
        c_info "Working tree dirty -> auto-commit ke branch baru + PR."

        have gh && gh auth status >/dev/null 2>&1 \
            && gh_auth=1 || gh_auth=0

        git fetch origin "$BASE_BRANCH" || die "git fetch gagal."

        ts=$(date +%Y%m%d-%H%M%S)
        branch="local-change-$ts"

        git stash -u || die "git stash gagal."
        # Branch baru dari origin/<BASE_BRANCH>, taruh perubahan di sana.
        git checkout -b "$branch" "origin/$BASE_BRANCH" || die "buat branch gagal."
        git stash pop || die "stash pop gagal — perubahan masih aman di stash, cek manual: git stash list"
        git add -A
        git commit -m "chore(local): simpan perubahan lokal sebelum pull ($ts)" \
            || die "commit gagal."
        git push -u origin "$branch" || die "push gagal."

        # PR via gh, fallback URL compare.
        if [ "$gh_auth" -eq 1 ]; then
            gh pr create --base "$BASE_BRANCH" --head "$branch" \
                --title "Local changes: $ts" \
                --body "Auto-commit perubahan lokal oleh \`spk.sh pull\` sebelum menarik update $BASE_BRANCH." \
                || die "gh pr create gagal — buat PR manual di URL fallback di bawah."
            c_ok "PR terbuka: $REPO_URL/pulls"
        else
            c_err "gh tidak ada / belum login. Buat PR manual:"
            printf '      %s/compare/%s...%s\n' "$REPO_URL" "$BASE_BRANCH" "$branch"
        fi

        # Kembali ke main yang sudah fast-forward dengan branch remote terbaru.
        git checkout "$BASE_BRANCH" || die "checkout $BASE_BRANCH gagal."
        git pull --ff-only origin "$BASE_BRANCH" || die "pull $BASE_BRANCH gagal."
        c_ok "Selesai: perubahan di branch $branch (PR), lokal di $BASE_BRANCH terbarui."
    else
        c_info "Working tree bersih -> git pull --ff-only."
        git pull --ff-only origin "$BASE_BRANCH" || die "git pull gagal."
        c_ok "Lokal terbarui."
    fi
}

# ---------- main ----------------------------------------------------------------

usage() {
    cat <<EOF
spk.sh — helper spk-desa

  ./spk.sh check        cek WSL / podman / podman-compose
  ./spk.sh run          up stack + install + migrate+seed + vite dev (foreground)
  ./spk.sh down         stop stack
  ./spk.sh bash         shell di container laravel.test
  ./spk.sh pull         git pull; perubahan lokal -> branch baru -> PR ke main

Opsi: --no-wsl (lewati cek WSL, untuk host non-WSL)
EOF
}

case "${1:-}" in
    check) shift; check "$@" ;;
    run)   shift; run "$@" ;;
    down)  down ;;
    bash)  bash_cmd ;;
    pull)  pull ;;
    -h|--help|help) usage ;;
    *) usage >&2; exit 1 ;;
esac