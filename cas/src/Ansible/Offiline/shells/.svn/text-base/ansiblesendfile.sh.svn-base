#! /bin/bash
if [[ -f /bin/$1 ]] || [[ -f /bin/$2 ]] || [[ -f /bin/$3 ]] || [[ -f /bin/$4 ]] || [[ -f /bin/$5 ]] || [[ -f /bin/$6 ]]
then
        echo "param error"
        exit;
fi
mod="";

if (( $4 ))
then
  mod=$mod" mode="$4
fi

if (( $5 ))
then
  mod=$mod" owner="$5
fi

if (( $6 ))
then
  mod=$mod" group="$6
fi

timenum=$(date +%Y%m%d%H%M%S)
backdir=$3".bak"
filename=$(basename $3)

path=$( cd "$( dirname "$0"  )" && pwd  );

ansible $1 -m shell -a "mkdir $backdir"
ansible $1 -m shell -a "cp -rf $3 $backdir/$filename.$timenum"
ansible $1 -m copy -a "src=$2 dest=$3 $mod"
