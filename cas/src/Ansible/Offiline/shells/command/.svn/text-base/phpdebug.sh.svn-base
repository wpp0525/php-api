#! /bin/bash
path=$( cd "$( dirname "$0"  )" && pwd  );

if [[ $2 == 'closedebug' ]]
then
  ansible $1 -m copy -a "src=$path/phpdebug/closedebug.sh dest=/tmp mode=777"
  ansible $1 -m shell -a "chdir=/tmp /tmp/closedebug.sh $3/index.php"
fi
if [[ $2 == 'logdebug' ]]
then
  ansible $1 -m copy -a "src=$path/phpdebug/logdebug.sh dest=/tmp mode=777"
  ansible $1 -m shell -a "chdir=/tmp /tmp/logdebug.sh $3/index.php"
fi
if [[ $2 == 'showdebug' ]]
then
  ansible $1 -m copy -a "src=$path/phpdebug/showdebug.sh dest=/tmp mode=777"
  ansible $1 -m shell -a "chdir=/tmp /tmp/showdebug.sh $3/index.php"
fi
exit;
