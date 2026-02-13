#!/bin/bash

LOG_FILE="/var/log/auth.log"
STATE_FILE="/tmp/ssh_log_offset"
TMP_MAIL="/tmp/ssh_alert_mail"
MAIL_TO="root@localhost"
FOUND=0

if [[ ! -f "$STATE_FILE" ]]; then
    wc -l < "$LOG_FILE" > "$STATE_FILE"
    exit 0
fi

LAST_LINE=$(cat "$STATE_FILE")
CURRENT_LINE=$(wc -l < "$LOG_FILE")

if [[ "$CURRENT_LINE" -lt "$LAST_LINE" ]]; then
    LAST_LINE=0
fi

NEW_ENTRIES=$(tail -n +$((LAST_LINE + 1)) "$LOG_FILE" | grep "sshd" | grep "Accepted password")

if [[ -n "$NEW_ENTRIES" ]]; then
    FOUND=1
    > "$TMP_MAIL"

    while read -r line; do
        RAW_TIME=$(echo "$line" | awk '{print $1}')
        TIME=$(date -d "$RAW_TIME" "+%H:%M:%S")
        DATE=$(date -d "$RAW_TIME" "+%d/%m/%Y")
        USER=$(echo "$line" | grep -oP 'for \K[^ ]+')

        echo "User $USER dang nhap thanh cong vao thoi gian $TIME $DATE" >> "$TMP_MAIL"
    done <<< "$NEW_ENTRIES"
fi

if [[ "$FOUND" -eq 1 ]]; then
    mail -s "Canh bao SSH Login" "$MAIL_TO" < "$TMP_MAIL"
fi

echo "$CURRENT_LINE" > "$STATE_FILE"
rm -f "$TMP_MAIL"