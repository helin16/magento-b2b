#!/bin/bash

# run all the message sending 
/usr/bin/php /var/www/magentob2b/web/cronjobs/MessageSender.php >> /tmp/message_`date +"%d_%b_%y"`.log
# run all the product import
/usr/bin/php /var/www/magentob2b/web/cronjobs/importOrders.php >> /tmp/orderImport.log