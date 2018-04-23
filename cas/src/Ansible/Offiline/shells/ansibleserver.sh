#! /bin/bash

if [[ -f /bin/$1 ]] || [[ -f /bin/$2 ]] || [[ -f /bin/$3 ]]
then
        echo "param error"
        exit;
fi

ansible $1 -m service -a "name=$2 state=$3"
