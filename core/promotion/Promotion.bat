:: Remove Existing databases
C:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot -e "DROP DATABASE IF EXISTS bpcinternal"
Pause

:: Create new databases
C:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot -e "CREATE DATABASE `bpcinternal` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci"
Pause

:: Import sql file
C:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot bpcinternal < .\backup_13_aug_2014.sql
Pause

:: Product Manager sql
C:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot bpcinternal < .\ProductManager\preProductManager.sql
Pause
