#/bin/sh

mysql -u root -padmin < createDBTest.sql

mysql -u root -padmin wikidbTest1 < dump.sql
mysql -u root -padmin wikidbTest2 < dump.sql
mysql -u root -padmin wikidbTest3 < dump.sql