#!/bin/sh
#/Applications/MAMP/Library/bin/mysqldump -u "root" -p"root" wikidbTest1 > dump.sql
mysql -u "root" -p"momo44" < createDBTest.sql
mysql -u "root" -p"momo44" wikidbTest1 < dump.sql
mysql -u "root" -p"momo44" wikidbTest2 < dump.sql
mysql -u "root" -p"momo44" wikidbTest3 < dump.sql


