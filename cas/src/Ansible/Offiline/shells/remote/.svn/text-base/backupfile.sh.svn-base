#! /bin/bash
if [[ -f /bin/$1 ]]
then
        echo "param error"
        exit;
fi

timenum=$(date +%Y%m%e%H%M%S)
backdir=$1".bak"
filename=$(basename $1)
if [[ ! -d $backdir ]]
then
        mkdir $backdir
fi
if [[ -f $1 ]]
then
  cp -rf $1 $backdir"/"$filename"."$timenum
fi
