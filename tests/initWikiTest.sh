#!/bin/sh
mysql -u root -padmin < $1

mysql -u root -padmin wikidbTest1 < $2
mysql -u root -padmin wikidbTest2 < $2
mysql -u root -padmin wikidbTest3 < $2
mysql -u root -padmin wikidb < $2
