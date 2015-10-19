#!/bin/bash

## run all the message sending  ########################################
if ps ax | grep -v grep | grep "MessageSender.php" > /dev/null; then
echo -n "MessageSender is Already Running....... :: "
date
echo -n " "
else
/usr/bin/php /var/www/magentob2b/web/cronjobs/MessageSender.php >> /tmp/message_`date +"%d_%b_%y"`.log


## run all the product import  ########################################
if ps ax | grep -v grep | grep "importOrders.php" > /dev/null; then
echo -n "importOrders is Already Running....... :: "
date
echo -n " "
else
/usr/bin/php /var/www/magentob2b/web/cronjobs/importOrders.php >> /tmp/orderImport.log