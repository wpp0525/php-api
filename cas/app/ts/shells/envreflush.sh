#! /bin/bash
path=$( cd "$( dirname "$0"  )" && pwd  );
phppath="/usr/bin/php";
logfile=$path"/../logs/server_info/taskrenew.log";
if [[ ! -d $path"/../logs/server_info" ]]
then
  mkdir $path"/../logs/server_info"
fi
if [[ -f "/usr/local/php-5.5.31/bin/php" ]]
then
  phppath="/usr/local/php-5.5.31/bin/php";
fi
echo "" > $logfile;
nohup $phppath $path/../ts.php Envconfig nonious >> $logfile 2>&1 &
sleep 2;
nohup $phppath $path/../ts.php Envconfig renewinfo start >> $logfile 2>&1 &
nohup $phppath $path/../ts.php Envconfig renewinfo start >> $logfile 2>&1 &
nohup $phppath $path/../ts.php Envconfig renewinfo start >> $logfile 2>&1 &
nohup $phppath $path/../ts.php Envconfig renewinfo start >> $logfile 2>&1 &
nohup $phppath $path/../ts.php Envconfig renewinfo start >> $logfile 2>&1 &
nohup $phppath $path/../ts.php Envconfig renewinfo start >> $logfile 2>&1 &
