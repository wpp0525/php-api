#! /bin/bash
ansible $1 -m shell -a "chdir=$3 sudo /usr/bin/php server.php $2 $4"
