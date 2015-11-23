#!/bin/bash

## generate a B2B DataFeed csv ########################################
if ps ax | grep -v grep | grep "DataFeedImporter.php" > /dev/null; then
	echo -n "DataFeedImporter is Already Running....... :: "
	date
	echo -n ""
else
	DIR=/tmp/datafeed/
	API=http://192.168.1.7/api/
	if ls ${DIR}/*.json &>/dev/null
	then
	    echo -n "Start to import json files"
		/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/DataFeedImporter.php $API $DIR
	    echo -n "DONE"
	else
		echo -n "NOT json files found under ${DIR}"
	fi
fi