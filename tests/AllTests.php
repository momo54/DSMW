<?php

define( 'MEDIAWIKI', true );
//ini_set("include_path", "..".PATH_SEPARATOR);
require_once 'PHPUnit/Framework/TestSuite.php';
require_once '../../../includes/GlobalFunctions.php';
require_once 'p2pTest1.php';
require_once 'p2pTest2.php';
require_once 'p2pTest3.php';



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
        $suite->addTestSuite('p2pTest1');
        $suite->addTestSuite('p2pTest2');
        $suite->addTestSuite('p2pTest3');
        return $suite;
    }
}

AllTests::main();

?>
