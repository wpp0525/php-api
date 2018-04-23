#! /bin/bash
ansible $1 -m shell -a "cat /proc/cpuinfo | grep 'processor' | wc -l"
