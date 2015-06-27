mysql -u root -proot -e "DROP DATABASE IF EXISTS bpcinternal;"
mysql -u root -proot -e "CREATE DATABASE bpcinternal DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;"

read -n1 -r -p "Press space to continue..." key

7z x -so 27_06_2015.7z | mysql -u root -proot bpcinternal
mysql -u root -proot bpcinternal < /home/frank/git/magento-b2b/core/promotion/wamp.sql
mysql -u root -proot bpcinternal < /home/frank/git/magento-b2b/core/promotion/debugMode.sql
read -n1 -r -p "Press space to continue..." key
