#!/bin/sh
mysql -u root -pMuller.3 < $1

mysql -u root -pMuller.3 wikidbTest1 < $2
mysql -u root -pMuller.3 wikidbTest2 < $2
mysql -u root -pMuller.3 wikidbTest3 < $2
