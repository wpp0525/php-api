#! /bin/bash
val1=$( command -v $1  )
val2=$( command -v $2  )
val3=$( command -v $3  )
val4=$( command -v $4  )

if [[ $val1 || $val2 || $val3 || $val4 ]]
then
        echo "param error"
        exit;
fi

ansible $1 -m copy -a "src=$2 dest=$3 mode=$4"
