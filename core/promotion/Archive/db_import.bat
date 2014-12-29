@echo off
:: Remove Existing databases
c:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot -e "DROP DATABASE IF EXISTS bpcinternal"
Pause

:: Create new databases
c:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot -e "CREATE DATABASE `bpcinternal` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci"
Pause

:: Import sql file
c:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot bpcinternal < .\bpcinternal.sql
Pause
c:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot bpcinternal < ..\accounting.sql
Pause