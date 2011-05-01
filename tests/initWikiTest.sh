#!/bin/sh
mysql -u root -pmomo44 wikidbTest1 < $1
mysql -u root -pmomo44 wikidbTest2 < $1
mysql -u root -pmomo44 wikidbTest3 < $1

#mysql -u "wikidbTest4" -p"wiki" wikidbTest4 < dump16.sql
#mysql -u "wikidbTest5" -p"wiki" wikidbTest5 < dump16.sql
#mysql -u "wikidbTest6" -p"wiki" wikidbTest6 < dump16.sql
