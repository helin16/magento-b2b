# Remove Existing databases
/Applications/MAMP/Library/bin/mysql -u root -proot --port=8889 -e "DROP DATABASE IF EXISTS bpcinternal";
read -p "Press enter to continue" nothing;

# Create new databases
/Applications/MAMP/Library/bin/mysql -u root -proot --port=8889 -e "CREATE DATABASE bpcinternal DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci";
read -p "Press enter to continue" nothing;

# Import sql file
/Applications/MAMP/Library/bin/mysql -u root -proot --port=8889 bpcinternal < bpcinternal.sql;
read -p "Press enter to continue" nothing;
/Applications/MAMP/Library/bin/mysql -u root -proot --port=8889 bpcinternal < ..\accounting.sql;
read -p "Press enter to continue" nothing;