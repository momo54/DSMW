<?php

define( 'MEDIAWIKI', true );
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once '../../../includes/GlobalFunctions.php';
require_once 'p2pTest1.php';
require_once 'p2pTest2.php';
require_once 'p2pTest3.php';
require_once 'p2pTest4.php';
//require_once 'p2pTest5.php';
require_once 'p2pTest6.php';
require_once 'p2pTest10.php';
require_once 'p2pAttachmentsTest1.php';
require_once 'p2pAttachmentsTest2.php';
require_once 'p2pAttachmentsTest3.php';
require_once 'p2pAttachmentsTest4.php';
require_once 'p2pAttachmentsTest5.php';
require_once 'p2pAttachmentsTest6.php';
require_once 'p2pAttachmentsTest7.php';



/**
 * Description of AllTests
 * execute all functionnal tests
 * @author hantz, jean-philippe muller
 */

class AllTests {

    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
    	echo "Lancement des tests\n";
        $suite = new PHPUnit_Framework_TestSuite('p2p');
        echo "\nLancement de p2pTest1\n";
        $suite->addTestSuite('p2pTest1');
        echo "\nLancement de p2pTest2\n";
        $suite->addTestSuite('p2pTest2');
        echo "\nLancement de p2pTest3\n";
        $suite->addTestSuite('p2pTest3');
        echo "\nLancement de p2pTest4\n";
        $suite->addTestSuite('p2pTest4');
        //echo "\nLancement de p2pTest5\n";
        //$suite->addTestSuite('p2pTest5');
        echo "\nLancement de p2pTest6\n";
        $suite->addTestSuite('p2pTest6');
        echo "\nLancement de p2pTest10\n";
        $suite->addTestSuite('p2pTest10');
        echo "\nLancement de p2pAttachmentsTest1\n";
        $suite->addTestSuite('p2pAttachmentsTest1');
        echo "\nLancement de p2pAttachmentsTest2\n";
        $suite->addTestSuite('p2pAttachmentsTest2');
        echo "\nLancement de p2pAttachmentsTest3\n";
        $suite->addTestSuite('p2pAttachmentsTest3');
        echo "\nLancement de p2pAttachmentsTest4\n";
        $suite->addTestSuite('p2pAttachmentsTest4');
        echo "\nLancement de p2pAttachmentsTest5\n";
        $suite->addTestSuite('p2pAttachmentsTest5');
        echo "\nLancement de p2pAttachmentsTest6\n";
        $suite->addTestSuite('p2pAttachmentsTest6');
        echo "\nLancement de p2pAttachmentsTest7\n";
        $suite->addTestSuite('p2pAttachmentsTest7');
        echo "\nFin des tests\n";
        return $suite;
    }
}

AllTests::main();

?>
