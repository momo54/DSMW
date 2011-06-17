#!/bin/sh
#/Applications/MAMP/Library/bin/mysqldump -u "root" -p"root" wikidbTest1 > dump.sql
/Applications/MAMP/Library/bin/mysql -u "root" -p"root" < createDBTest.sql
/Applications/MAMP/Library/bin/mysql -u "root" -p"root" wikidbTest1 < dump.sql
/Applications/MAMP/Library/bin/mysql -u "root" -p"root" wikidbTest2 < dump.sql
/Applications/MAMP/Library/bin/mysql -u "root" -p"root" wikidbTest3 < dump.sql


