#!/bin/bash

if ps ax | grep -v grep | grep "pricematchRunner.php" > /dev/null; then
  echo -n "pricematchRunner is Already Running....... :: "
  date
  echo -n " "
else
  /usr/bin/php /var/www/magentob2b/web/cronjobs/pricematch/pricematchRunner.php >> /tmp/log/pricematchRunner_`date +"%d_%b_%y"`.log
fi
