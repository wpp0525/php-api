#! /bin/bash
ansible $1 -m shell -a "df"|awk '{if($6 == "/") {print $3} else if($5 == "/") {print $2}}'
