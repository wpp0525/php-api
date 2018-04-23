#!/bin/bash

es_host="10.200.2.82:9200"
ca_host="10.200.2.82"
ca_pwd="qingfeng00"
#es_host="172.20.4.178:9200"
#ca_host="172.20.4.73"
#ca_pwd="654321"

if [[ "$1" == "" ]]
then
	lastday=`date -d last-day +%Y-%m-%d`
	date_times=($lastday)
else
	if [[ $1 =~ "," ]]
	then
		OLD_IFS="$IFS"
	    IFS=","
	    date_times=($1)
	    IFS="$OLD_IFS"
	elif [[ $1 =~ "~" ]]
	then
		OLD_IFS="$IFS"
	    IFS="~"
	    date_times=($1)
	    IFS="$OLD_IFS"
	    start_date=`date -d "${date_times[0]}" +%s`
	    end_date=`date -d "${date_times[1]}" +%s`
	    for ((i=0,d=${start_date}; d<=${end_date}; i=i+1,d=d+86400))
		do
        	date_times[i]=`date -d @${d} +%Y-%m-%d`
		done
	else
		date_times=($1)
	fi
fi

export RSYNC_PASSWORD=$ca_pwd
for date_time in ${date_times[*]}
do
	coupon_filename='coupon.'"$date_time"'.txt'
	rsync -av root@"$ca_host"::sem/"$coupon_filename" /opt/report
	if [ ! -f "/opt/report/""$coupon_filename" ]; then
		echo "/opt/report/""$coupon_filename"" not exist"
		continue
	fi
	if [ ! -n "$have_coupon" ]; then
	query1="load data local inpath '/opt/report/"$coupon_filename"' overwrite into table ca_promotion_coupon;"
	else
	query1="$query1""load data local inpath '/opt/report/"$coupon_filename"' into table ca_promotion_coupon;"
	fi
	have_coupon=true
done
if [ ! -n "$have_coupon" ]; then
	echo "no available coupon files and exit"
	exit 2
fi
echo "query1: $query1"
query2="insert into table es_promotion_coupon select concat( chargeDepart, '-', orderChannelId, '-', unitOfTime, '-', dateTime) as id, bu, chargeDepartId, chargeDepart, orderChannelId, orderChannel, promotionChargeAmount, promotionOrderAmount, couponChargeAmount, couponOrderAmount, concat(dateTime, ' 00:00:00'), unitOfTime from ca_promotion_coupon;"
echo "query2: $query2"

/usr/local/hive/bin/hive -e "$query1$query2"

