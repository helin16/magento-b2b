@echo off
:: Remove Existing databases
e:\wamp\bin\mysql\mysql5.5.24\bin\mysql.exe -u root -proot -e "DROP DATABASE IF EXISTS bpcinternal
Pause

:: Create new databases
e:\wamp\bin\mysql\mysql5.5.24\bin\mysql.exe -u root -proot -e "CREATE DATABASE `bpcinternal` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci"
Pause

:: Import sql file
e:\wamp\bin\mysql\mysql5.5.24\bin\mysql.exe -u root -proot bpcinternal < .\bpcinternal_25Aug14\bpcinternal_25Aug14.sql
Pause
