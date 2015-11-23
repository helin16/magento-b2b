#!/bin/bash

SERVER=backup.budgetpc.com.au
SERVER_PATH=/var/www/html/var/import/
CSV_FILE_PATH=/tmp/productUpdate.csv

## generate a MAGENTO product csv ########################################
if ps ax | grep -v grep | grep "ProductToMagento.php" > /dev/null; then
	echo -n "ProductToMagento is Already Running....... :: "
	date
	echo
else
	echo -n '== Generating the csv ... ::'
	date
	/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductToMagento.php $CSV_FILE_PATH
	if [ -e "$CSV_FILE_PATH" ]
	then
		echo -n "== coping ${CSV_FILE_PATH} TO ${SERVER}:${SERVER_PATH} :: "
		date
		scp $CSV_FILE_PATH ec2-user@$SERVER:$SERVER_PATH
		echo -n "== copied successfully :: "
		date
		echo -n "== removing ${CSV_FILE_PATH} :: "
		date
		rm -f $CSV_FILE_PATH
		echo -n "== removed successfully: ${CSV_FILE_PATH} :: "
		date
	else
		echo -n "NO SUCH A FILE: ${CSV_FILE_PATH} :: "
		date
	fi
	echo
	echo
fi