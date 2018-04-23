#! /bin/bash
if [ ! -f $1 ]
then
        echo "index 文件不存在";
        exit;
fi
sed -i '/error_reporting/d' $1
sed -i '/display_errors/d' $1
sed -i '3i\error_reporting(E_ALL);' $1
