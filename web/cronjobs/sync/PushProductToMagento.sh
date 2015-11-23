#!/bin/bash

SERVER=backup.budgetpc.com.au
SERVER_PATH=/var/www/html/var/import/
CSV_FILE_PATH=/tmp/

## generate a MAGENTO product csv ########################################
if ps ax | grep -v grep | grep "ProductToMagento.php" > /dev/null; then
	echo -n "ProductToMagento is Already Running....... :: "
	date
	echo -n ""
else
	echo -n '== Generating the csv'
	/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductToMagento.php $CSV_FILE_PATH
	FILE=${CSV_FILE_PATH}productUpdate.csv
	if [ -e "$FILE" ]
	then
		echo -n '== coping '.$FILE.'TO:'.$SERVER:$SERVER_PATH
		scp $FILE ec2-user@$SERVER:$SERVER_PATH
		rm -f $FILE
	fi
fi