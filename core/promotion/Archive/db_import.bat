@echo off
:: Remove Existing databases
C:\xampp\mysql\bin\mysql.exe -u root -proot -e "DROP DATABASE IF EXISTS bpcinternal;"

:: Create new databases
C:\xampp\mysql\bin\mysql.exe -u root -proot -e "CREATE DATABASE `bpcinternal` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;"

Pause

:: Import sql files
"C:\Program Files\7-Zip\7z.exe" x -so 13_06_2015.7z | C:\xampp\mysql\bin\mysql.exe -u root -proot bpcinternal
C:\xampp\mysql\bin\mysql.exe -u root -proot bpcinternal < ..\kit.sql
C:\xampp\mysql\bin\mysql.exe -u root -proot bpcinternal < ..\wamp.sql
C:\xampp\mysql\bin\mysql.exe -u root -proot bpcinternal < ..\debugMode.sql
Pause