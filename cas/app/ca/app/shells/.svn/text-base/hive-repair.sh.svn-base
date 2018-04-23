#!/bin/bash

#mysql_host="192.168.0.139"
#mysql_user="root"
#mysql_pwd="123456"
#ca_host="10.200.2.82"
#ca_pwd="qingfeng00"
mysql_host="192.168.50.24"
mysql_user="lmm_sem_all"
mysql_pwd="sAfZBLxgD0JuWlhN"
ca_host="172.20.4.73"
ca_pwd="654321"

if [[ "$1" == "" ]]
then
	echo "please set the datetime"
	exit 2
else
	if [[ $1 =~ "," ]]
	then
		OLD_IFS="$IFS"
	    IFS=","
	    date_times=($1)
	    IFS="$OLD_IFS"
		this_day=`date +%Y-%m-%d`' 00:00:00'
		next_day=`date +%Y-%m-%d`' 00:00:00'
	    for date_time in ${date_times[*]}
	    do
	        if [ `date -d "$date_time" +%s` -lt `date -d "$this_day" +%s` ]; then
				this_day=$date_time' 00:00:00'
			fi
			if [ ! -n "$have_date" ]; then
				next_day=$this_day
	        	where="dateTime != timestamp '"$date_time" 00:00:00'"
	        else
	        	where="$where"" and dateTime != timestamp '"$date_time" 00:00:00'"
			fi
	        if [ `date -d "$date_time" +%s` -gt `date -d "$next_day" +%s` ]; then
				next_day=$date_time' 00:00:00'
			fi
			have_date=true
	    done
		next_day=`date -d "$next_day"" 1 days" +%Y-%m-%d`' 00:00:00'
	elif [[ $1 =~ "~" ]]
	then
		OLD_IFS="$IFS"
	    IFS="~"
	    date_times=($1)
	    IFS="$OLD_IFS"
	    start_date=`date -d "${date_times[0]}" +%s`
	    end_date=`date -d "${date_times[1]}" +%s`
		this_day=${date_times[0]}' 00:00:00'
		next_day=`date -d "${date_times[1]}"" 00:00:00 1 days" +%Y-%m-%d`' 00:00:00'
	    for ((i=0,d=${start_date}; d<=${end_date}; i=i+1,d=d+86400))
		do
        	date_times[i]=`date -d @${d} +%Y-%m-%d`
			if [ ! -n "$have_date" ]; then
	        	where="dateTime != timestamp '"${date_times[i]}" 00:00:00'"
	        else
	        	where="$where"" and dateTime != timestamp '"${date_times[i]}" 00:00:00'"
			fi
			have_date=true
		done
	else
		this_day=$1' 00:00:00'
		next_day=`date -d "$this_day"" 1 days" +%Y-%m-%d`' 00:00:00'
		date_times=($1)
	    where="dateTime != timestamp '"$1" 00:00:00'"
	fi
fi

if [[ "$2" == "socom" ]]
then
	platform=2
	database="socom"
	prefix="socom."
	table="sem_account_report_socom"
elif [[ "$2" == "smcn" ]]
then
	platform=3
	database="smcn"
	prefix="smcn."
	table="sem_account_report_smcn"
elif [[ "$2" == "sogou" ]]
then
	platform=4
	database="sogou"
	prefix="sogou."
	table="sem_account_report_sogou"
else
	platform=1
	database="default"
	prefix=""
	table="sem_account_report"
fi

################  repair report sync  ################
export RSYNC_PASSWORD=$ca_pwd
for date_time in ${date_times[*]}
do
	if [[ "$3" != "onlycache" ]]; then
		report_filename='report.'"$prefix""$date_time"'.txt'
		order_filename='order.'"$prefix""$date_time"'.txt'
		rsync -av root@"$ca_host"::sem/"$report_filename" /opt/report
		rsync -av root@"$ca_host"::sem/"$order_filename" /opt/report
	fi
	if [ ! -f "/opt/report/""$report_filename" ]; then
		echo "/opt/report/""$report_filename"" not exist"
		continue
	fi
	if [ ! -f "/opt/report/""$order_filename" ]; then
		echo "/opt/report/""$report_filename"" not exist and empty table ca_sem_report_order"
		if [ ! -n "$have_report" ]; then
		query1="USE "$database";load data local inpath '/opt/report/"$report_filename"' overwrite into table ca_sem_report;DROP TABLE IF EXISTS ca_sem_report_order;CREATE TABLE ca_sem_report_order (losc STRING, amount FLOAT, orderNum INT, dateTime STRING, device SMALLINT, unitOfTime SMALLINT, userName STRING, campaignName STRING, adgroupName STRING, keywordId BIGINT, keyword STRING) ROW FORMAT DELIMITED FIELDS TERMINATED BY '\t' STORED AS TEXTFILE;"
		else
		query1="$query1""load data local inpath '/opt/report/"$report_filename"' into table ca_sem_report;"
		fi
	else
		if [ ! -n "$have_report" ]; then
		query1="USE "$database";load data local inpath '/opt/report/"$report_filename"' overwrite into table ca_sem_report;load data local inpath '/opt/report/"$order_filename"' overwrite into table ca_sem_report_order;"
		else
		query1="$query1""load data local inpath '/opt/report/"$report_filename"' into table ca_sem_report;load data local inpath '/opt/report/"$order_filename"' into table ca_sem_report_order;"
		fi
	fi
	have_report=true
done
if [ ! -n "$have_report" ]; then
	echo "no available report files and exit"
	exit 2
fi
echo "query1: $query1"
query2="insert overwrite table sem_report_repair select * from sem_report where "$where";"
echo "query2: $query2"
query3="insert into table sem_report_repair 
select case when r.keywordId is null then o.keywordId else r.keywordId end,case when r.userName is null then o.userName else r.userName end,case when r.campaignName is null then o.campaignName else r.campaignName end,
case when r.adgroupName is null then o.adgroupName else r.adgroupName end,case when r.keyword is null then o.keyword else r.keyword end,case when r.losc is null then o.losc else r.losc end,
case when r.impression is null then 0 else r.impression end,case when r.click is null then 0 else r.click end,case when r.cost is null then 0 else r.cost end,
case when o.orderNum is null then 0 else o.orderNum end,case when o.amount is null then 0 else o.amount end,case when r.ctr is null then 0 else r.ctr end,case when r.cpc is null then 0 else r.cpc end,
case when r.click = 0 or r.click is null or o.orderNum is null then 0 else o.orderNum/r.click end,case when r.cost = 0 or r.cost is null or o.amount is null then 0 else o.amount/r.cost end,
concat(case when r.dateTime is null then o.dateTime else r.dateTime end,' 00:00:00'),
case when r.unitOfTime is null then o.unitOfTime else r.unitOfTime end,case when r.device is null then o.device else r.device end 
from ca_sem_report r full outer join (select losc,min(amount) as amount,min(orderNum) as orderNum,dateTime,device,unitOfTime,userName,campaignName,adgroupName,keywordId,keyword from ca_sem_report_order 
group by losc,dateTime,device,unitOfTime,userName,campaignName,adgroupName,keywordId,keyword) o on o.keywordId = r.keywordId and o.device = r.device and o.dateTime = r.dateTime;"
echo "query3: $query3"
query4="insert overwrite table sem_report select * from sem_report_repair;"
echo "query4: $query4"
query5="insert overwrite table sem_account_report 
select userName,sum(case when impression is null then 0 else impression end) as impression,sum(case when click is null then 0 else click end) as click,sum(case when cost is null then 0 else cost end) as cost,
sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum,sum(case when losc is null or amount is null then 0 else amount end) as amount,dateTime,min(unitOfTime) as unitOfTime,device from 
(select userName,dateTime,device,losc,sum(impression) as impression,sum(click) as click,sum(cost) as cost,min(orderNum) as orderNum,min(amount) as amount,min(unitOfTime) as unitOfTime from sem_report 
where dateTime >= timestamp '"$this_day"' and dateTime < timestamp '"$next_day"' group by userName,dateTime,device,losc) as result group by userName,dateTime,device;"
echo "query5: $query5"
query6="CREATE TEMPORARY FUNCTION dboutput AS 'org.apache.hadoop.hive.contrib.genericudf.example.GenericUDFDBOutput';"
echo "query6: $query6"
query7="select dboutput('jdbc:mysql://"$mysql_host"/lmm_sem','"$mysql_user"','"$mysql_pwd"','INSERT INTO "$table"(userName, impression, click, cost, orderNum, amount, dateTime, unitOfTime, device) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE impression = ?,click = ?,cost = ?,orderNum = ?,amount = ?', userName, impression, click, cost, orderNum, amount, dateTime, unitOfTime, device, impression, click, cost, orderNum, amount) from sem_account_report;"
echo "query7: $query7"

if [[ "$3" != "onlycache" ]]
then
	/usr/local/hive/bin/hive -e "$query1$query2$query3$query4$query5$query6$query7"
fi

if [[ "$3" == "cleancache" ]] || [[ "$3" == "onlycache" ]]
then
	today=`date +%Y-%m-%d`
	tomorrow=`date -d next-day +%Y-%m-%d`
	yesterday=`date -d last-day +%Y-%m-%d`
	oneweekago=`date -d last-week +%Y-%m-%d`
	thirtydaysago=`date -d '30 days ago' +%Y-%m-%d`
	ninetydaysago=`date -d '90 days ago' +%Y-%m-%d`
	daytimes=($yesterday $oneweekago $thirtydaysago)
	mids=$(echo $(curl "http://ca.lvmama.com/sem/listMonitor?current_page=1&page_size=4&condition=\{\"platform\":\"="$platform"\"\}&order=id desc")| grep -Po '"id":".*?"' | grep -Po '\d+')
	nums=(1 2 3 4 5 6)
	for daytime in ${daytimes[*]}; do
		################  daily top cache  ################
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=campaignName&order=rate&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=campaignName&order=roi&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=adgroupName&order=rate&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=adgroupName&order=roi&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=keywordId&order=rate&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=keywordId&order=roi&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=campaignName&order=rate&ordertype=asc&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=campaignName&order=roi&ordertype=asc&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=adgroupName&order=rate&ordertype=asc&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=adgroupName&order=roi&ordertype=asc&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=keywordId&order=rate&ordertype=asc&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listTop?platform="$platform"&group=keywordId&order=roi&ordertype=asc&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		################  daily monitor cache  ################
		for mid in ${mids[*]}; do
			curl -iw "\n" "http://ca.lvmama.com/sem/listMonitorReport?id="$mid"&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&nocache=1"
			for num in ${nums[*]}; do
				curl -iw "\n" "http://ca.lvmama.com/sem/listMonitorReport?id="$mid"&num="$num"&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&nocache=1"
			done
		done
		################  realtime analysis cache  ################
		for mid in ${mids[*]}; do
			for num in ${nums[*]}; do
				curl -iw "\n" "http://ca.lvmama.com/sem/listAnalysisReport?current_page=1&mid="$mid"&num="$num"&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&nocache=1"
			done
		done
		curl -iw "\n" "http://ca.lvmama.com/sem/listReport?current_page=1&platform="$platform"&group=keywordId&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&nocache=1"
		curl -iw "\n" "http://ca.lvmama.com/sem/listReport?current_page=1&page_size=20&platform="$platform"&group=userName&condition=\{\"startDate\":\""$daytime"\",\"endDate\":\""$today"\"\}&terms=\{\"unitOfTime\":\"5\",\"device\":\"0\"\}&have_report=1&nocache=1"
	done
fi
