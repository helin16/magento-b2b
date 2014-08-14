:: Import sql file
D:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot bpcinternal < .\CourierModule_beforeScript.sql
D:\wamp\bin\php\php5.5.12\php.exe CourierModule.php
D:\wamp\bin\mysql\mysql5.6.17\bin\mysql.exe -u root -proot bpcinternal < .\CourierModule_afterScript.sql
Pause

