#!/bin/bash
#
# Provision a cPanel deploy target so it can actually boot Laravel.
#
# .cpanel.yml only copies app/ bootstrap/ config/ database/ public/build/
# resources/ routes/ into $DEPLOYPATH. Everything else an app needs to run --
# artisan, vendor/, .env, storage/ -- is environment-specific and is NOT
# deployed. It has to be put in place once, by hand. This script is that step.
#
# Safe to re-run: it never overwrites .env or storage/, so it doubles as the
# recovery path if the deploy target is ever wiped.
#
# Usage, from the cPanel repository clone:
#   cd ~/repositories/extra-miles
#   bash scripts/provision-deploy-target.sh ~/extra-miles
#
# Seeding .env the first time (copies, then you edit it):
#   SEED_ENV=~/extra-miles-stage/.env bash scripts/provision-deploy-target.sh ~/extra-miles

set -euo pipefail

PHP_BIN=${PHP_BIN:-/opt/cpanel/ea-php82/root/usr/bin/php}
COMPOSER_BIN=${COMPOSER_BIN:-/usr/local/bin/composer}
SEED_ENV=${SEED_ENV:-}

SRC=$(pwd)
DEPLOYPATH=${1:-}

if [ -z "$DEPLOYPATH" ]; then
    echo "usage: bash scripts/provision-deploy-target.sh <DEPLOYPATH>" >&2
    echo "  e.g. bash scripts/provision-deploy-target.sh ~/extra-miles" >&2
    exit 64
fi

# Refuse to run from anywhere but a real checkout -- otherwise we would happily
# provision a target out of an empty directory.
if [ ! -f "$SRC/artisan" ] || [ ! -f "$SRC/composer.json" ]; then
    echo "error: run this from the repository clone (no artisan/composer.json in $SRC)" >&2
    exit 1
fi

for bin in "$PHP_BIN" "$COMPOSER_BIN"; do
    [ -x "$bin" ] || { echo "error: not executable: $bin" >&2; exit 1; }
done

echo "==> provisioning $DEPLOYPATH from $SRC"
mkdir -p "$DEPLOYPATH"

# Versioned files the deploy skips. Always refreshed: they track the commit.
echo "--> artisan, composer.json, composer.lock"
cp "$SRC/artisan" "$SRC/composer.json" "$SRC/composer.lock" "$DEPLOYPATH/"

# storage/ holds live logs, sessions and cache. Seed the skeleton only when
# absent -- overwriting it on an existing install would discard real state.
if [ -d "$DEPLOYPATH/storage" ]; then
    echo "--> storage/ exists, left untouched"
else
    echo "--> storage/ seeding from clone"
    cp -r "$SRC/storage" "$DEPLOYPATH/"
fi

mkdir -p "$DEPLOYPATH/bootstrap/cache"

# .env carries credentials, so it is never generated or overwritten here.
if [ -f "$DEPLOYPATH/.env" ]; then
    echo "--> .env exists, left untouched"
elif [ -n "$SEED_ENV" ]; then
    [ -f "$SEED_ENV" ] || { echo "error: SEED_ENV not found: $SEED_ENV" >&2; exit 1; }
    echo "--> .env seeded from $SEED_ENV -- REVIEW IT (APP_ENV, APP_DEBUG, APP_URL, DB_*)"
    cp "$SEED_ENV" "$DEPLOYPATH/.env"
else
    echo "error: $DEPLOYPATH/.env missing. Re-run with SEED_ENV=/path/to/.env to seed it." >&2
    exit 1
fi

echo "--> composer install"
cd "$DEPLOYPATH"
"$PHP_BIN" "$COMPOSER_BIN" install --no-dev --optimize-autoloader

echo "--> permissions"
chmod -R 775 "$DEPLOYPATH/storage" "$DEPLOYPATH/bootstrap/cache"

echo "--> verifying"
"$PHP_BIN" artisan --version

echo "==> done. $DEPLOYPATH can now boot."
