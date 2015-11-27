#!/bin/bash

SERVER=backup.budgetpc.com.au
SERVER_PATH=/var/www/html/var/import/
FILE_DIR=/tmp/
FILE_NAME=productUpdate.tar.gz


## generate a MAGENTO product file ########################################
if ps ax | grep -v grep | grep "ProductToMagento.php" > /dev/null; then
	echo -n "ProductToMagento is Already Running....... :: "
	date
	echo
else
	echo -n '== Generating the file ... ::'
	date
	/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductToMagento.php $FILE_DIR
	FILE_PATH=${FILE_DIR}/${FILE_NAME}
	if [ -e "$FILE_PATH" ]
	then
		echo -n "== coping ${FILE_PATH} TO ${SERVER}:${SERVER_PATH} :: "
		date
		scp $FILE_PATH ec2-user@$SERVER:$SERVER_PATH
		echo -n "== copied successfully :: "
		date
		echo -n "== removing ${FILE_PATH} :: "
		date
		rm -f $FILE_PATH
		echo -n "== removed successfully: ${FILE_PATH} :: "
		date
	else
		echo -n "NO SUCH A FILE: ${FILE_PATH} :: "
		date
	fi
	echo
	echo
fi