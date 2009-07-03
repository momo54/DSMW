<?php

define( 'MEDIAWIKI', true );
require_once 'PHPUnit/Framework/TestSuite.php';
require_once '../../..//includes/GlobalFunctions.php';
require_once 'extensionTest.php';
require_once 'pushPullTest.php';
require_once 'p2pBotTest.php';
require_once 'logootTest.php';

//PHPUnit_Util_Filter::addFileToFilter(__FILE__);


/**
 * Description of AllTests
 *
 * @author marlene
 */
 
class AllTests {

    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('p2p');
        $suite->addTestSuite('extensionTest');
        $suite->addTestSuite('pushPullTest');
        $suite->addTestSuite('p2pBotTest');
        $suite->addTestSuite('logootTest');
        return $suite;
    }
}

AllTests::main();

?>
