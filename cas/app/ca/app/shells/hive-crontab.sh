#!/bin/bash

#es_host="10.200.2.82:9200"
#mysql_host="192.168.0.139"
#mysql_user="root"
#mysql_pwd="123456"
#ca_host="10.200.2.82"
#ca_pwd="qingfeng00"
es_host="172.20.4.178:9200"
mysql_host="192.168.50.24"
mysql_user="lmm_sem_all"
mysql_pwd="sAfZBLxgD0JuWlhN"
ca_host="172.20.4.73"
ca_pwd="654321"

yesterday=`date -d last-day +%Y-%m-%d`' 00:00:00'
yesterday_timestamp=`date -d "$yesterday" +%s`'000'
today=`date +%Y-%m-%d`' 00:00:00'
today_timestamp=`date -d "$today" +%s`'000'
now_hour=`date +%H`
if [[ "$2" == "socom" ]]
then
	platform=2
	database="socom"
	prefix="socom."
	table="sem_account_report_socom"
	iscache="yes"
elif [[ "$2" == "smcn" ]]
then
	platform=3
	database="smcn"
	prefix="smcn."
	table="sem_account_report_smcn"
	iscache="no"
elif [[ "$2" == "sogou" ]]
then
	platform=4
	database="sogou"
	prefix="sogou."
	table="sem_account_report_sogou"
	iscache="no"
else
	platform=1
	database="default"
	prefix=""
	table="sem_account_report"
	iscache="yes"
fi
report_filename='report.'"$prefix"`date -d last-day +%Y-%m-%d`'.txt'
order_filename='order.'"$prefix"`date -d last-day +%Y-%m-%d`'.txt'

if [[ "$1" == "realtime" ]]
then
	################  realtime report sync  ################
	query1="USE "$database";DROP TABLE IF EXISTS es_sem_realtime_report_new;"
	echo "query1: $query1"
	query2="CREATE EXTERNAL TABLE es_sem_realtime_report_new (keywordId BIGINT,userName STRING,campaignName STRING,adgroupName STRING,keyword STRING,losc STRING,impression INT,click INT,cost FLOAT,orderNum INT,amount FLOAT,ctr DOUBLE,cpc DOUBLE,rate DOUBLE,roi DOUBLE,dateTime TIMESTAMP,unitOfTime SMALLINT,device SMALLINT)
	STORED BY 'org.elasticsearch.hadoop.hive.EsStorageHandler'
	TBLPROPERTIES(
	'es.nodes' = '"$es_host"',
	'es.index.auto.create' = 'false',
	'es.resource' = 'lmm_sem/sem_report',
	'es.query' = '{\"query\":{\"filtered\":{\"query\":{\"bool\":{\"must\":{\"term\":{\"unitOfTime\":7}}}},\"filter\":{\"range\":{\"date\":{\"gte\":"$today_timestamp"}}}}}}',
	'es.mapping.names' = 'keywordId:keywordId, userName:userName, campaignName:campaignName, adgroupName:adgroupName, keyword:keyword, losc:losc, impression:impression, click:click, cost:cost, orderNum:orderNum, amount:amount, ctr:ctr, cpc:cpc, rate:rate, roi:roi, dateTime:date, unitOfTime:unitOfTime, device:device');"
	echo "query2: $query2"
	query3="insert overwrite table sem_realtime_report select * from es_sem_realtime_report_new where dateTime >= timestamp '"$today"';"
	echo "query3: $query3"
	query4="insert overwrite table sem_account_realtime_report 
	select userName,sum(case when impression is null then 0 else impression end) as impression,sum(case when click is null then 0 else click end) as click,sum(case when cost is null then 0 else cost end) as cost,
	sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum,sum(case when losc is null or amount is null then 0 else amount end) as amount,dateTime,min(unitOfTime) as unitOfTime,device from 
	(select userName,dateTime,device,losc,sum(impression) as impression,sum(click) as click,sum(cost) as cost,min(orderNum) as orderNum,min(amount) as amount,min(unitOfTime) as unitOfTime from sem_realtime_report 
	where dateTime >= timestamp '"$today"' group by userName,dateTime,device,losc) as result group by userName,dateTime,device;"
	echo "query4: $query4"
	query5="CREATE TEMPORARY FUNCTION dboutput AS 'org.apache.hadoop.hive.contrib.genericudf.example.GenericUDFDBOutput';"
	echo "query5: $query5"
	query6="select dboutput('jdbc:mysql://"$mysql_host"/lmm_sem','"$mysql_user"','"$mysql_pwd"','INSERT INTO "$table"(userName, impression, click, cost, orderNum, amount, dateTime, unitOfTime, device) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE impression = ?,click = ?,cost = ?,orderNum = ?,amount = ?', userName, impression, click, cost, orderNum, amount, dateTime, unitOfTime, device, impression, click, cost, orderNum, amount) from sem_account_realtime_report;"
	echo "query6: $query6"
else
	################  daily report sync  ################
	export RSYNC_PASSWORD=$ca_pwd
	rsync -av root@"$ca_host"::sem/"$report_filename" /opt/report
	rsync -av root@"$ca_host"::sem/"$order_filename" /opt/report
	if [ ! -f "/opt/report/""$report_filename" ]; then
		echo "/opt/report/""$report_filename"" not exist"
		exit 2
	fi
	query1="USE "$database";load data local inpath '/opt/report/"$report_filename"' overwrite into table ca_sem_report;"
	echo "query1: $query1"
	if [ ! -f "/opt/report/""$order_filename" ]; then
		echo "/opt/report/""$report_filename"" not exist and empty table ca_sem_report_order"
		query2="DROP TABLE IF EXISTS ca_sem_report_order;CREATE TABLE ca_sem_report_order (losc STRING, amount FLOAT, orderNum INT, dateTime STRING, device SMALLINT, unitOfTime SMALLINT, userName STRING, campaignName STRING, adgroupName STRING, keywordId BIGINT, keyword STRING) ROW FORMAT DELIMITED FIELDS TERMINATED BY '\t' STORED AS TEXTFILE;"
		echo "query2: $query2"
	else
		query2="load data local inpath '/opt/report/"$order_filename"' overwrite into table ca_sem_report_order;"
		echo "query2: $query2"
	fi
	query3="insert into table sem_report 
	select case when r.keywordId is null then o.keywordId else r.keywordId end,case when r.userName is null then o.userName else r.userName end,case when r.campaignName is null then o.campaignName else r.campaignName end,
	case when r.adgroupName is null then o.adgroupName else r.adgroupName end,case when r.keyword is null then o.keyword else r.keyword end,case when r.losc is null then o.losc else r.losc end,
	case when r.impression is null then 0 else r.impression end,case when r.click is null then 0 else r.click end,case when r.cost is null then 0 else r.cost end,
	case when o.orderNum is null then 0 else o.orderNum end,case when o.amount is null then 0 else o.amount end,case when r.ctr is null then 0 else r.ctr end,case when r.cpc is null then 0 else r.cpc end,
	case when r.click = 0 or r.click is null or o.orderNum is null then 0 else o.orderNum/r.click end,case when r.cost = 0 or r.cost is null or o.amount is null then 0 else o.amount/r.cost end,
	concat(case when r.dateTime is null then o.dateTime else r.dateTime end,' 00:00:00'),
	case when r.unitOfTime is null then o.unitOfTime else r.unitOfTime end,case when r.device is null then o.device else r.device end 
	from ca_sem_report r full outer join ca_sem_report_order o on o.keywordId = r.keywordId and o.device = r.device;"
	echo "query3: $query3"
	query4="insert overwrite table sem_account_report 
	select userName,sum(case when impression is null then 0 else impression end) as impression,sum(case when click is null then 0 else click end) as click,sum(case when cost is null then 0 else cost end) as cost,
	sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum,sum(case when losc is null or amount is null then 0 else amount end) as amount,dateTime,min(unitOfTime) as unitOfTime,device from 
	(select userName,dateTime,device,losc,sum(impression) as impression,sum(click) as click,sum(cost) as cost,min(orderNum) as orderNum,min(amount) as amount,min(unitOfTime) as unitOfTime from sem_report 
	where dateTime >= timestamp '"$yesterday"' and dateTime < timestamp '"$today"' group by userName,dateTime,device,losc) as result group by userName,dateTime,device;"
	echo "query4: $query4"
	query5="CREATE TEMPORARY FUNCTION dboutput AS 'org.apache.hadoop.hive.contrib.genericudf.example.GenericUDFDBOutput';"
	echo "query5: $query5"
	query6="select dboutput('jdbc:mysql://"$mysql_host"/lmm_sem','"$mysql_user"','"$mysql_pwd"','INSERT INTO "$table"(userName, impression, click, cost, orderNum, amount, dateTime, unitOfTime, device) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE impression = ?,click = ?,cost = ?,orderNum = ?,amount = ?', userName, impression, click, cost, orderNum, amount, dateTime, unitOfTime, device, impression, click, cost, orderNum, amount) from sem_account_report;"
	echo "query6: $query6"
fi

/usr/local/hive/bin/hive -e "$query1$query2$query3$query4$query5$query6"

today=`date +%Y-%m-%d`
tomorrow=`date -d next-day +%Y-%m-%d`
yesterday=`date -d last-day +%Y-%m-%d`
oneweekago=`date -d last-week +%Y-%m-%d`
thirtydaysago=`date -d '30 days ago' +%Y-%m-%d`
ninetydaysago=`date -d '90 days ago' +%Y-%m-%d`
daytimes=($yesterday $oneweekago $thirtydaysago)
mids=$(echo $(curl "http://ca.lvmama.com/sem/listMonitor?current_page=1&page_size=4&condition=\{\"platform\":\"="$platform"\"\}&order=id desc")| grep -Po '"id":".*?"' | grep -Po '\d+')
nums=(1 2 3 4 5 6)
if [[ "$1" == "realtime" ]]
then
	echo "realtime operation finished"
else
	if [[ "$iscache" == "no" ]]; then
		echo "daily operation finished"
		exit 2
	fi
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
	echo "daily operation finished"
fi
