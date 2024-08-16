#!/bin/sh

curl --silent https://infinitenet.net/autombot/script/update_schedules.php >/dev/null 2>&1 &&
curl --silent https://infinitenet.net/autombot/script/historico.php >/dev/null 2>&1
curl --silent https://infinitenet.net/autombot/script/update_disponibilidade.php >/dev/null 2>&1 &&
curl --silent https://infinitenet.net/autombot/script/buscar_json.php >/dev/null 2>&1 &&
curl --silent https://infinitenet.net/autombot/script/historico.php >/dev/null 2>&1
