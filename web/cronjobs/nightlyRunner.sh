#!/bin/bash

## backup database ########################################
if ps ax | grep -v grep | grep "backup_db" > /dev/null; then
echo -n "backup_db is Already Running....... :: "
date
echo -n " "
else
/bin/bash /var/www/magentob2b/web/cronjobs/backup_db.bash >> /tmp/db_backup_`date +"%d_%b_%y"`.log

## run all xero exporter ########################################
if ps ax | grep -v grep | grep "ExportRunner.php" > /dev/null; then
echo -n "ExportRunner is Already Running....... :: "
date
echo -n " "
else
/usr/bin/php /var/www/magentob2b/web/cronjobs/report/ExportRunner.php >> /tmp/export_`date +"%d_%b_%y"`.log

## product ageing report ########################################
if ps ax | grep -v grep | grep "ProductAgeingReport.php" > /dev/null; then
echo -n "ProductAgeingReport is Already Running....... :: "
date
echo -n " "
else
/usr/bin/php /var/www/magentob2b/web/cronjobs/ProductAgeingReport.php >> /tmp/ageingReport_`date +"%d_%b_%y"`.log

## price match ########################################
if ps ax | grep -v grep | grep "pricematchRunner.php" > /dev/null; then
echo -n "pricematchRunner is Already Running....... :: "
date
echo -n " "
else
/usr/bin/php /var/www/magentob2b/web/cronjobs/pricematch/pricematchRunner.php >> /tmp/pricematchRunner_`date +"%d_%b_%y"`.log

# push to ec2 dummy .5
# /bin/bash /var/www/magentob2b/web/cronjobs/push_db_to_ec2.bash >> /tmp/push_to_ec2_dot_5_`date +"%d_%b_%y"`.log

# push to .7
#/bin/bash /var/www/magentob2b/web/cronjobs/push_db_to_dot_7.bash

## clean the assets ########################################
if ps ax | grep -v grep | grep "AssetCleaner.php" > /dev/null; then
echo -n "AssetCleaner is Already Running....... :: "
date
echo -n " "
else
/usr/bin/php /var/www/magentob2b/web/cronjobs/AssetCleaner.php >> /tmp/asset_cleaner_`date +"%d_%b_%y"`.log

## CronLog mailer ########################################
if ps ax | grep -v grep | grep "CronLogOutputNotificationSender.php" > /dev/null; then
echo -n "CronLogOutputNotificationSender is Already Running....... :: "
date
echo -n " "
else
/usr/bin/php /var/www/magentob2b/web/cronjobs/CronLogOutputNotification/CronLogOutputNotificationSender.php >> /tmp/CronLogOutputNotificationSender_`date +"%d_%b_%y"`.log