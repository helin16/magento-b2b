#!/bin/bash
BASEDIR=$(dirname $0)

# Product Attribute Set Pull
/usr/bin/php $BASEDIR/ProductAttributeSetPull.php
# Product Attribute Pull
/usr/bin/php $BASEDIR/ProductAttributePull.php
# Product Category Pull
/usr/bin/php $BASEDIR/ProductCategoryPull.php
# Product Manufacturer Pull
/usr/bin/php $BASEDIR/ProductManufacturerPull.php
# Product Download
/usr/bin/php $BASEDIR/ProductDownloadAll.php
# Product Process Download
/usr/bin/php $BASEDIR/ProductProcessDownloadedAll.php