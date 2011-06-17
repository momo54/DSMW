#!/bin/sh
/Applications/MAMP/Library/bin/mysqldump -u "root" -p"root" wikidbTest1 > dump.sql
/Applications/MAMP/Library/bin/mysqldump -u "root" -p"root" wikidbTest1 > dump1.sql
/Applications/MAMP/Library/bin/mysqldump -u "root" -p"root" wikidbTest2 > dump2.sql
/Applications/MAMP/Library/bin/mysqldump -u "root" -p"root" wikidbTest3 > dump3.sql
