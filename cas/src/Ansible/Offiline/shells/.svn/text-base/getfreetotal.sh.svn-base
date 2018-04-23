#! /bin/bash
ansible $1 -m shell -a "free"|awk '{if($1 == "Mem:") {print $2}}'
