# Distributed Semantic MediaWiki 1.2

Contents:
* Disclaimer
* Requirements
* Installation
* Example: LocalSettings.php
* Contact

## Disclaimer 

In general, the extension can be installed into a working wiki without making
any irreversible changes to the source code or database, so you can try out
the software without much risk (though no dedicated uninstall mechanism is
provided).

## Requirements

DSMW-1.2 has been developped on MW1.16.4. SMW-1.6.1 The DSMW extension
requires an install of Semantic MediaWiki > 1.6 (cf:
http://semantic-mediawiki.org/wiki/Semantic_MediaWiki).  For more
details, see those extensions' own installation requirements.

Important: If you want to run the DSMW tests, you must make a dump of your 
database immediately after having installed DSMW but before using DSMW.
For more details, see the README.md file in the tests directory
[wikipath]/extensions/DSMW/tests.

## Installation

* Download the zip file and extract it. Paste the folder named DSMW
  into the Mediawiki extension folder "[wikipath]/extensions/".

* Edit LocalSettings.php in the root of your MediaWiki installation,
  and add the following lines near the bottom but BEFORE THE LINES
  THAT ACTIVATE SEMANTIC MEDIAWIKI(cf. requirements):

```php
  require_once "$IP/extensions/DSMW/DSMW.php";

    == Example: LocalSettings.php ==

    The end of this file should look like this:

    //DSMW
    require_once "$IP/extensions/DSMW/DSMW.php";

    //SMW
    include_once("$IP/extensions/SemanticMediaWiki/includes/SMW_Settings.php");
    enableSemantics($_SERVER['SERVER_NAME']);
```

* Once LocalSettings is updated, go to special pages in "DSMW
  Settings" (special:DSMWAdmin).  click on "initialise tables", next
  on "update properties type", and finally on "Article update"

* "initialize tables" creates some new table in current "wikidb" in
  order to store specific data for DSMW. You can check that the table
  "p2p_params" is now part of wikidb.
 
* "update properties type" installs the vocabulary used by DSMW. for
  example the page "Property:hasoperation" should exist in mediawiki
  after this.

* "article update" processes existing pages in MW. DSMW stores the
  history of pages differently. Any page that exists before DSMW
  installation has to be saved again to get a DSMW history for that
  page. If not, DSMW will fail to display this page. In a fresh MW,
  the main page has to be processed, otherwise an error will be
  displayed when getting the main page.

* Go to Main page, you should see a new tab for the main page called
  "dsmw".  click on it, you will a see the DSMW history for that page.

* Next, you should read the DSMW manual for using DSMW.

## Contact

If you have any issues or questions, please consult:
https://momo54.github.com/DSMW


