#!/bin/bash

# Product Attribute Set Pull
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductAttributeSetPull.php >> /tmp/ProductAttributeSetPull_`date +"%d_%b_%y"`.log
# Product Attribute Pull
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductAttributePull.php >> /tmp/ProductAttributePull_`date +"%d_%b_%y"`.log
# Product Category Pull
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductCategoryPull.php >> /tmp/ProductCategoryPull_`date +"%d_%b_%y"`.log
# Product Manufacturer Pull
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductManufacturerPull.php >> /tmp/ProductManufacturerPull_`date +"%d_%b_%y"`.log
# Product Download
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductDownloadNAll.php >> /tmp/ProductDownloadAll_`date +"%d_%b_%y"`.log
# Product Process Download
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductProcessDownloadedAll.php >> /tmp/ProductProcessDownloadedAll.php_`date +"%d_%b_%y"`.log
