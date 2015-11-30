#!/bin/bash

# Product Attribute Set Pull
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductAttributeSetPull.php
# Product Attribute Pull
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductAttributePull.php
# Product Category Pull
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductCategoryPull.php
# Product Manufacturer Pull
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductManufacturerPull.php
# Product Download
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductDownloadNAll.php
# Product Process Download
/usr/bin/php /var/www/magentob2b/web/cronjobs/sync/ProductProcessDownloadedAll.php