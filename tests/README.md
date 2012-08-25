# DSMW farming and test

Running DSMW tests require to install a family of three wikis wiki1,
wiki2 and wiki3.  Several strategies exist to install wiki families
and are detailed at: http://www.mediawiki.org/wiki/Manual:Wiki_family
We choosed a strategy inspired by "ultimate minimalist solution". It
take approximately 15-20mn to install the test environment.

* a complete semantic mediawiki with DSMW is assumed to be installed
in /var/www/mw.16.4. See INSTALL of DSMW for installation. This MW
will be the template.  Double check you dependencies:
* MW1.16.4 ?
* SMW 1.6.1 ?
* PHP 5.3.5 with curl support ?

* A complete working LocalSettings.php is available in directory
tests in file 'myLocalSettings.php'.

* Take a dump of the database :
```
$ mysqldump -uroot -pmomo44 wikidb > dump.sql
```

The dump.sql file should stored in extensions/DSMW/tests. It is better
to take the dump just after DSMW tables have been initialised,
properties installed, but the main page *has not* been updated (there
is test on update that will fail if update already done).

Important: Open the dump.sql file (with a text editor) and ensure that
in the following lines (~line 560-565), VALUES are set to ('0',
'0'). If not, set them to ('0', '0') and save the file.

``` SQL
LOCK TABLES `p2p_params` WRITE;
/*!40000 ALTER TABLE `p2p_params` DISABLE KEYS */;
INSERT INTO `p2p_params` (`value`, `server_id`) VALUES (0, '0');
/*!40000 ALTER TABLE `p2p_params` ENABLE KEYS */;
UNLOCK TABLES;
```

* create links in /var/www:

```shell
$ ln -s mw1.16.4 wiki1
$ ln -s mw1.16.4 wiki2
$ ln -s mw1.16.4 wiki3
```

You must have in /var/www:

```shell
drwxrwxrwx  4 root  root  4096 2011-05-01 07:52 .
drwxr-xr-x 16 root  root  4096 2011-04-30 14:50 ..
-rw-r--r--  1 root  root   177 2011-04-30 14:50 index.html
drwxr-xr-x 19 molli molli 4096 2011-05-01 08:01 mw1.16.4
drwxr-xr-x 17 molli molli 4096 2011-04-30 18:42 mw1.17
lrwxrwxrwx  1 molli molli    8 2011-05-01 07:52 wiki1 -> mw1.16.4
lrwxrwxrwx  1 molli molli    8 2011-05-01 07:52 wiki2 -> mw1.16.4
lrwxrwxrwx  1 molli molli    8 2011-05-01 07:52 wiki3 -> mw1.16.4
```

* clone the databases. Just create three databases : wikidbTest1, wikidbTest2, wikidbTest3
```sql
mysql -u root -pmomo44 < createDBTest.sql;
mysql -u root -pmomo44 wikidbTest1 < dump.sql
mysql -u root -pmomo44 wikidbTest2 < dump.sql
mysql -u root -pmomo44 wikidbTest3 < dump.sql
```

* update localSettings.php. Well, in fact all three wikis are sharing
the same source code and have a separate database. According to the
incoming urls, we have to select the right database, and set script path.
This is how i modified the LocalSettings.php:

```php
$wgSitename=0;
$mysites=array(array('DsmwDev','mw1.16.4','wikidb','/mw1.16.4'),
               array('wiki1','wiki1','wikidbTest1','/wiki1'),
               array('wiki2','wiki2','wikidbTest2','/wiki2'),
               array('wiki3','wiki3','wikidbTest3','/wiki3'));
foreach($mysites as $site){
  if(strpos($_SERVER['SCRIPT_FILENAME'],$site[1])!==false){
    $wgSitename=$site[0];$wgDBname=$site[2];$wgScriptPath= $site[3];
;break;
  }
}
if(!$wgSitename && $wgCommandLineMode) {
  foreach($mysites as $site){
    if(strpos($_SERVER['PWD'],$site[1])!==false){
      $wgSitename=$site[0];$wgDBname=$site[2];$wgScriptPath= $site[3];break;
    }
  }
}

if(!$wgSitename){
  trigger_error('unknown DSMW server',E_USER_ERROR);
}

## Database settings
$wgDBtype           = "mysql";
$wgDBserver         = "localhost";
#$wgDBname           = "wikidb";
$wgDBuser           = "root";
$wgDBpassword       = "momo44";
```

* set images path in order to make attachments test working. DSMW
tests uploads of pdf files, pdf files uploads must be allowed. I
modified the LocalSettings.php like that:

```php
## To enable image uploads, make sure the 'images' directory
## is writable, then set this to true:
$wgEnableUploads       = true;
$wgUploadPath="{$wgScriptPath}/$wgDBname/images";
$wgUploadDirectory = "{$IP}/$wgDBname/images/";
$wgFileExtensions = array_merge($wgFileExtensions, array('doc', 'xls', 'mpp', 'pdf','ppt','xlsx','jpg','tiff'));
```

It means that you have to create *manually* according directories in
/var/www/mw1.16.4:

```
molli@molli-VirtualBox:/var/www/mw1.16.4$ ls -l wikidb*
wikidb:
total 4
drwxrwxrwx 2 molli molli 4096 2011-04-30 22:06 images

wikidbTest1:
total 4
drwxrwxrwx 20 molli molli 4096 2011-04-30 22:13 images

wikidbTest2:
total 4
drwxrwxrwx 5 molli molli 4096 2011-04-30 22:11 images

wikidbTest3:
total 4
drwxrwxrwx 4 molli molli 4096 2011-04-30 22:12 images
```

Just try to upload a file in wiki1 for example to test image upload
configuration. Go in http://localhost/wiki1 and upload
DSMW/tests/Import/Ours1.jpg. Repear with Ours1.pdf.

* tests are runned using Phpunit. You have to install it. On Ubuntu (11.04)
```
$ sudo apt-get install phpunit
```

But i had some pbs after install. i needed to complete installation
using 'pear'. This is explained on phpunit web site (http://www.phpunit.de).

```
pear channel-discover pear.phpunit.de
pear channel-discover components.ez.no
pear channel-discover pear.symfony-project.com
pear install phpunit/PHPUnit
pear upgrade
pear install phpunit/PHP_CodeCoverage
```


* Check some tests configuration. Test are based on bots that simulate
concurrent manipulations. Bots need to be logged. This is done in
BasicBot.php. 

* Check if USERID, USERNAME and PASSWORD are correctly set.
```php
if (!defined('USERID')){	define('USERID','1');} // find it at Special:Preferences
if (!defined('USERNAME')){	define('USERNAME','WikiSysop');}
if (!defined('PASSWORD')){	define('PASSWORD','momo44');} // password in plain text. No md5 or anything.
```

* Check file tests/settings.php for wiki declarations if it does not match:

```php
<?php
/**
 * Put your wiki location
 */
define('WIKI1','http://localhost/wiki1');
define('WIKI2','http://localhost/wiki2');
define('WIKI3','http://localhost/wiki3');
?>
```

* Ready to run the tests. Be sure that you have the executable
permissions on DSMW/tests/initWikiTest.sh

```
$ cd /var/www/mw1.16.4/extensions/DSMW/tests
$ phpunit AllTests.php 
```
It takes 10-15mn to run all the test, and many output is generated during tests (ignore warning such as header already sent...).
Phpunit generates a resume at the end of test executions:

```
     Tests: 30, Assertions: 670, Failures: 5, Errors: 7.
```
Just check all tests are ok (so, it is not good for this example). 

If you have 
```php
1) p2pTest5::testDSMWPagesUpdateFunction
succeeded to edit page Main_Page (  )
Failed asserting that <boolean:true> is false.
```

It means that you dump the sql database after updating main page. So
This test cannot work.

* If you have error in p2pAttachmentsTest5, it is certainly because
you don't enable 'pdf' uploads. Update your localSetting.php:

```php
$wgFileExtensions = array_merge($wgFileExtensions, array('doc', 'xls', 'mpp', 'pdf','ppt','xlsx','jpg','tiff'));
```

* if you have any others errors, check the apache2 logs to get more
informations : it is located in /var/log/apache2/error.log. Check "PHP
Fatal error". Check also /tmp/dsmw.log if you have enabled dsmw logging.

