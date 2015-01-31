mysqldump -uroot -proot bpcinternal > /tmp/`date +'%d_%m_%Y'`.sql;
7za a -t7z -m0=lzma -mx=9 -mfb=64 -md=32m -ms=on /tmp/`date +'%d_%m_%Y'`.7z /tmp/`date +'%d_%m_%Y'`.sql;
rm -f /tmp/`date +'%d_%m_%Y'`.sql;
rsync -avzh /tmp/*.7z admin@192.168.1.2:/share/website_backup/internalSystemBackup/;
rm -f /tmp/`date +'%d_%m_%Y'`.7z;