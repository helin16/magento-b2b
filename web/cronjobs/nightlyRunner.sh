#!/bin/bash

# backup database
#/bin/bash /var/www/magentob2b/web/cronjobs/backup_db.bash
# run all xero exporter
/usr/bin/php /var/www/magentob2b/web/cronjobs/report/ExportRunner.php >> /tmp/export_`date +"%d_%b_%y"`.log
# product ageing report
/usr/bin/php /var/www/magentob2b/web/cronjobs/ProductAgeingReport.php >> /tmp/ageingReport_`date +"%d_%b_%y"`.log
# price match
/usr/bin/php /var/www/magentob2b/web/cronjobs/pricematch/pricematchRunner.php >> /tmp/pricematchRunner_`date +"%d_%b_%y"`.log
# push to ec2 dummy .5
# /bin/bash /var/www/magentob2b/web/cronjobs/push_db_to_ec2.bash >> /tmp/push_to_ec2_dot_5_`date +"%d_%b_%y"`.log
# push to .7
#/bin/bash /var/www/magentob2b/web/cronjobs/push_db_to_dot_7.bash