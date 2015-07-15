#!/bin/bash
mysqldump -uroot -proot bpcinternal | 7za a -aoa -t7z -m0=lzma2 -mx=9 -mfb=64 -md=32m -ms=on -mhe -si`date +"%d_%m_%Y.sql"` /tmp/`date +"%d_%m_%Y.7z"`
scp /tmp/`date +"%d_%m_%Y.7z"` admin@192.168.1.2:/share/website_backup/internalSystemBackup/
rm -f /tmp/`date +'%d_%m_%Y.sql'`
rm -f /tmp/`date +'%d_%m_%Y.7z'`