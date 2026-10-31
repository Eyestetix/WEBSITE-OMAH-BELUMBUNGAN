#!/bin/bash
set -e

# Konfigurasi Apache (Listen 0.0.0.0:3000, VirtualHost *:3000, runtime di /tmp, dan .htaccess)
# telah dibangkitkan secara penuh pada saat Docker image build time.
# Tidak ada berkas yang ditulis atau diubah saat runtime (mendukung read-only root filesystem Deplexo).

# Eksekusi proses Apache di foreground (PID 1) dengan preservasi signal handling
if [ "$#" -gt 0 ]; then
    exec "$@"
else
    exec apache2-foreground
fi
