#!/bin/bash
set -e

# Ambil port dari environment variable PORT yang disediakan platform (Deplexo).
# Jika tidak diset, gunakan default port non-privileged 3000 (standar web runtime Deplexo).
TARGET_PORT="${PORT:-3000}"

# Pastikan konfigurasi ports.conf mendengarkan pada interface 0.0.0.0 dan port non-privileged
echo "Listen 0.0.0.0:${TARGET_PORT}" > /etc/apache2/ports.conf

# Perbarui VirtualHost 000-default.conf agar konsisten dengan port target
if [ -f /etc/apache2/sites-available/000-default.conf ]; then
    sed -i -E "s/<VirtualHost [^>]+>/<VirtualHost *:${TARGET_PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

# Pastikan direktori runtime, lock, dan log Apache dapat ditulis oleh proses container
mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2
chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2 2>/dev/null || true
chmod -R 777 /var/run/apache2 /var/lock/apache2 /var/log/apache2 2>/dev/null || true

# Eksekusi Apache di foreground
if [ "$#" -gt 0 ]; then
    exec "$@"
else
    exec apache2-foreground
fi
