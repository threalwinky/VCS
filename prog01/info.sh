#!/bin/bash

echo "[Thong tin he thong]"

echo
echo "Ten may: $(hostname)"
echo "Ten ban phan phoi: $(lsb_release -d | awk '{print $2}')"

echo
echo "Phien ban he dieu hanh: $(lsb_release -d | awk '{print $3}')"

echo
cpu_1=$(lscpu | grep 'Model name\|CPU op-mode' | awk '{$1=$1; print}')
cpu_2=$(cat /proc/cpuinfo | grep "cpu MHz" | head -n 1 | awk '{$1=$1; print}')
echo "Thong tin CPU: \n$cpu_1\n$cpu_2 MHz"

echo
echo "Bo nho vat ly:"
free -m | awk '/Mem:/ {print "Tong:", $2, "MB"}'

echo 
echo "O dia con trong:"
df -m --total | awk '/total/ {print "Con trong:", $4, "MB"}'

echo
echo "Danh sach dia chi IP:"
ip -o -4 addr show | awk '{print $2, $4}'

echo
echo "Danh sach user:"
cut -d: -f1 /etc/passwd | sort

echo
echo "Tien trinh dang chay voi quyen root:"
ps -U root -u root -o pid,cmd --sort=cmd

echo
echo "Cac port dang mo:"
ss -tuln | awk 'NR>1 {print $5}' | awk -F: '{print $NF}' | sort -n | uniq

echo
echo "Thu muc cho phep other co quyen ghi:"
find / -type d -perm -0002 2>/dev/null

echo
echo "Danh sach cac goi phan mem da cai:"
dpkg-query -W -f='${Package}\t${Version}\n' | sort