<?php

define( 'MEDIAWIKI', true );
require_once 'PHPUnit/Framework/TestSuite.php';
require_once '../../..//includes/GlobalFunctions.php';
require_once 'apiTest.php';
require_once 'pushPullTest.php';
require_once 'p2pTest.php';


/**
 * Description of AllTests
 *
 * @author hantz
 */

class AllTests {

    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('p2p');
        $suite->addTestSuite('apiTest');
        $suite->addTestSuite('pushPullTest');
        $suite->addTestSuite('p2pTest');
        return $suite;
    }
}

AllTests::main();

?>
