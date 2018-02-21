#!/bin/bash

cd `dirname -- "${BASH_SOURCE[0]}"`

DATA_DIR="$(dirname -- "$(pwd -P)")/data"

CONFIG_DIR="$DATA_DIR/transmission-config"
SETTINGS_FILE="$CONFIG_DIR/settings.json"
PID_FILE="$DATA_DIR/transmission-pid"

DOWNLOADS_DIR="$DATA_DIR/downloads"
DOWNLOADS_PARTIAL_DIR="$DATA_DIR/downloads-partial"

PID=
if [ -f "$PID_FILE" ]; then
    PID=`cat "$PID_FILE"`
    if [ ! -e "/proc/$PID" ] || [ `cat /proc/$PID/cmdline | tr "\000" "\n" | head -n 1` != "transmission-daemon" ]; then
        PID=
    fi
fi

if [ -n "$PID" ]; then
    echo "killing transmission-daemon"
    kill "$PID"
    while [ -e "/proc/$PID" ]; do sleep 0.5; done
else
    echo "starting transmission-daemon"
    mkdir -p "$CONFIG_DIR" "$DOWNLOADS_DIR" "$DOWNLOADS_PARTIAL_DIR"
    cat <<EOF > "$SETTINGS_FILE"
{
    "download-dir": "$DOWNLOADS_DIR",
    "incomplete-dir": "$DOWNLOADS_PARTIAL_DIR",
    "incomplete-dir-enabled" : true,

    "ratio-limit": 0,
    "ratio-limit-enabled": true,
    "speed-limit-up": 25,
    "speed-limit-up-enabled": true,

    "peer-port-random-on-start": true
}
EOF
    transmission-daemon -x "$PID_FILE" -g "$CONFIG_DIR" -p 9100
fi