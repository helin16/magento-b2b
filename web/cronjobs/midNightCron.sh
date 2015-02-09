#!/bin/bash

# run all xero exporter
/usr/bin/php /var/www/magentob2b/web/cronjobs/report/ExportRunner.php >> /tmp/export_`date +"%d_%b_%y"`.log