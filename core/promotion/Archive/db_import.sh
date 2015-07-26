FN=`date +"%d_%m_%Y.7z"`
DBN=bpcinternal
echo FileName: $FN
echo DatabaseName: $DBN

$ read -rsp $'Press any key to continue...\n' -n1 key

mysql -u root -proot -e "DROP DATABASE IF EXISTS $DBN;"
mysql -u root -proot -e "CREATE DATABASE $DBN DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;"

7z x -so -pbudget123pc $FN | mysql -u root -proot $DBN

echo "Done All"
