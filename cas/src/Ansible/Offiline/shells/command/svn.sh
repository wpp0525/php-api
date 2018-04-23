#! /bin/bash
ansible $1 -m shell -a "chdir=$3 sudo svn $2 $4"
