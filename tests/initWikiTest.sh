#!/bin/sh
mysql -u "wikidbTest1" -p"wiki" wikidbTest1 < dump15.sql
mysql -u "wikidbTest2" -p"wiki" wikidbTest2 < dump15.sql
mysql -u "wikidbTest3" -p"wiki" wikidbTest3 < dump15.sql

#mysql -u "wikidbTest4" -p"wiki" wikidbTest4 < dump16.sql
#mysql -u "wikidbTest5" -p"wiki" wikidbTest5 < dump16.sql
#mysql -u "wikidbTest6" -p"wiki" wikidbTest6 < dump16.sql
