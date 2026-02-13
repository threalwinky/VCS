#!/bin/bash

ETC_DIR="/etc"
BASE_DIR="/tmp"
OLD_DIR="$BASE_DIR/old"
NEW_DIR="$BASE_DIR/new"
LOG_FILE="/var/log/checketc.log"
TMP_MAIL="/tmp/checketc.log"
MAIL_TO="root@localhost"
FOUND=0

log() {
    echo -e "$1" | sudo tee -a "$LOG_FILE" "$TMP_MAIL" > /dev/null
    echo -e "$1"
}

init() {
    [[ -n "$NEW_DIR" && -n "$OLD_DIR" ]] || exit 1
    sudo rm -rf "$NEW_DIR" "$OLD_DIR"
    sudo cp -r -L "$ETC_DIR" "$OLD_DIR"
    sudo cp -r -L "$ETC_DIR" "$NEW_DIR"
}

start() {
    [[ -d "$OLD_DIR" ]] || { init; exit 0; }
    sudo rm -rf "$NEW_DIR"
    sudo cp -r -L "$ETC_DIR" "$NEW_DIR"
}

check_modify() {
    local diff_out="$1"
    sudo truncate -s 0 "$TMP_MAIL"

    log "[Log checketc - $(date '+%H:%M:%S %d/%m/%Y')]"

    log "=== Danh sach file tao moi ==="
    created=$(echo "$diff_out" | grep "^Only in $NEW_DIR" | sed "s|^Only in $NEW_DIR/\?\(.*\): \(.*\)$|\1/\2|" | sed 's|^/||')

    if [[ -z "$created" ]]; then
        log "(Khong co)"
    else
        FOUND=1
        for f in $created; do
            file_path="$ETC_DIR/$f"
            log "$file_path"
            if sudo file "$file_path" | grep -q text; then
                sudo head -n 10 "$file_path" | sudo tee -a "$LOG_FILE" "$TMP_MAIL" > /dev/null
            fi
            log ""
        done
    fi

    log "=== Danh sach file sua doi ==="
    modified=$(echo "$diff_out" | grep -E "^(Files|diff -r)" | awk '{print $3}' | sed "s|^$OLD_DIR/||")

    if [[ -z "$modified" ]]; then
        log "(Khong co)"
    else
        FOUND=1
        for f in $modified; do
            log "$ETC_DIR/$f"
            log "--- Chi tiet thay doi ---"
            sudo diff "$OLD_DIR/$f" "$NEW_DIR/$f" | sudo tee -a "$LOG_FILE" "$TMP_MAIL" > /dev/null
            sudo diff "$OLD_DIR/$f" "$NEW_DIR/$f"
            log "------------------------"
        done
    fi

    log "=== Danh sach file bi xoa ==="
    deleted=$(echo "$diff_out" | grep "^Only in $OLD_DIR" | sed "s|^Only in $OLD_DIR/\?\(.*\): \(.*\)$|\1/\2|" | sed 's|^/||')

    if [[ -z "$deleted" ]]; then
        log "(Khong co)"
    else
        FOUND=1
        for f in $deleted; do
            file_path="$ETC_DIR/$f"
            log "$file_path"
            if sudo file "$file_path" | grep -q text; then
                sudo head -n 10 "$file_path" | sudo tee -a "$LOG_FILE" "$TMP_MAIL" > /dev/null
            fi
            log ""
        done
    fi

}

start

DIFF_RESULT=$(sudo diff -r "$OLD_DIR" "$NEW_DIR")

if [[ -n "$DIFF_RESULT" ]]; then
    check_modify "$DIFF_RESULT"
fi

if [[ "$FOUND" -eq 1 ]]; then
    sudo rm -rf "$OLD_DIR"
    sudo mv "$NEW_DIR" "$OLD_DIR"
    mail -s "Canh bao thay doi thu muc /etc" "$MAIL_TO" < "$TMP_MAIL"
else
    sudo rm -rf "$NEW_DIR"
fi

