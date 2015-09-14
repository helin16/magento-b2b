@echo off

:: setting params
set db_name=bpcinternal

for /f %%x in ('wmic path win32_localtime get /format:list ^| findstr "="') do set %%x
if %Day% LSS 10 set Day=0%Day%
if %Month% LSS 10 set Month=0%Month%
set today=%Day%_%Month%_%Year%
set dump_file_name=%today%.7z

echo.
echo database name = %db_name%
echo dump file name = %dump_file_name%

echo.
echo is this right? 
Pause

:: Remove Existing databases
echo.
echo droping database %db_name% ...
mysql -u root -proot -e "DROP DATABASE IF EXISTS %db_name%;"
echo done.

:: Create new databases
echo.
echo creating database %db_name% ...
mysql -u root -proot -e "CREATE DATABASE %db_name% DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;"
echo done.

:: Import sql files
echo.
echo importing database from %dump_file_name%
7z x -so -pbudget123pc %dump_file_name% | mysql -u root -proot %db_name%
echo done
echo importing wamp.sql
mysql -u root -proot bpcinternal < ..\wamp.sql
echo importing datafeed.sql
mysql -u root -proot bpcinternal < ..\datafeed.sql
echo importing attributeSet.sql
mysql -u root -proot bpcinternal < ..\attributeSet.sql
echo running sync.php
php ..\sync.php

echo.
echo all good. good bye
Pause
