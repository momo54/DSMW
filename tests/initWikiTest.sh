#/bin/sh

#cp LocalSettings_Test1.php ../../../LocalSettings_Test1.php
#cp LocalSettings_Test2.php ../../../LocalSettings_Test2.php
#cp LocalSettings_Test3.php ../../../LocalSettings_Test3.php
#cp ../../../LocalSettings.php LocalSettingsO.php

#cp LocalSettings.php ../../..

mysql -u root -padmin < createDBTest.sql

mysql -u root -padmin wikidbTest1 < dump.sql
mysql -u root -padmin wikidbTest2 < dump.sql
mysql -u root -padmin wikidbTest3 < dump.sql