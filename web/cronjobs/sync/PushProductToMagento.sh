#!/bin/bash

SERVER=backup.budgetpc.com.au
SERVER_PATH=/var/www/html/var/import/
CSV_FILE_PATH=/tmp/

## generate a MAGENTO product csv ########################################
if ps ax | grep -v grep | grep "ProductToMagento.php" > /dev/null; then
	echo -n "ProductToMagento is Already Running....... :: "
	date
	echo
else
	echo -n '== Generating the csv ... ::'
	date
	/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductToMagento.php $CSV_FILE_PATH
	FILE=${CSV_FILE_PATH}productUpdate.csv
	if [ -e "$FILE" ]
	then
		echo -n "== coping ${FILE} TO ${SERVER}:${SERVER_PATH} :: "
		date
		scp $FILE ec2-user@$SERVER:$SERVER_PATH
		echo -n "== copied successfully :: "
		date
		echo -n "== removing ${FILE}"
		date
		rm -f $FILE
		echo -n "== removed successfully: ${FILE} :: "
		date
	else
		echo -n "NO SUCH A FILE: ${FILE} :: "
		date
	fi
fi