#! /bin/bash
upinfo=$(ansible $1 -m shell -a "uptime")
upinfo=$(echo $upinfo | awk 'NR==1{print $0}')
upa=${upinfo#*load average: }
echo ${upa##*, }
