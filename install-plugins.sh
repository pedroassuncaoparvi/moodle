#!/bin/bash

set -e

echo "=========================================="
echo "  Instalando plugins do Moodle EAD Parvi"
echo "=========================================="

sudo chown -R $USER:$USER public/theme public/mod public/admin/tool public/local 2>/dev/null || true

echo "→ Instalando tema Moove..."
git clone https://github.com/willianmano/moodle-theme_moove.git public/theme/moove
cd public/theme/moove && git checkout MOODLE_501_STABLE && cd ../../..

echo "→ Instalando HVP..."
git clone https://github.com/h5p/h5p-moodle-plugin.git public/mod/hvp
cd public/mod/hvp && git submodule update --init && cd ../../..

echo "→ Instalando Simple Certificate..."
git clone https://github.com/bozoh/moodle-mod_simplecertificate.git public/mod/simplecertificate

echo "→ Instalando ObjectFS..."
git clone https://github.com/catalyst/moodle-tool_objectfs.git public/admin/tool/objectfs

echo "→ Instalando AWS SDK..."
git clone https://github.com/catalyst/moodle-local_aws.git public/local/aws

echo "✅ Todos os plugins instalados!"
