BASEDIR=$(dirname $0)
FNAME=bpcinternal.sql
FPASSWORD=budget123pc
DBNAME=bpcinternal
DBHOST=localhost
DBUSERNAME=root
DBPASSWORD=root

echo Directory: $BASEDIR
echo FileName: $FNAME
echo DatabaseName: $DBNAME

echo create database $DBNAME if not exists
mysql -h $DBHOST -u $DBUSERNAME -p$DBPASSWORD -e "CREATE DATABASE IF NOT EXISTS $DBNAME DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;"

echo importing datases from $BASEDIR/$FNAME.7z, may take few minutes
mysql -h $DBHOST -u $DBUSERNAME -p$DBPASSWORD $DBNAME < $BASEDIR/$FNAME

echo import sql files
mysql -h $DBHOST -u $DBUSERNAME -p$DBPASSWORD $DBNAME < $BASEDIR/../newSupplierQtyColumn.sql
mysql -h $DBHOST -u $DBUSERNAME -p$DBPASSWORD $DBNAME < $BASEDIR/../newSyncSettings.sql
mysql -h $DBHOST -u $DBUSERNAME -p$DBPASSWORD $DBNAME < $BASEDIR/../newWeightColumn.sql

echo done
