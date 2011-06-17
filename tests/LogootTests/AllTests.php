<?php
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

// <ED> =====================================================================
if (!defined('DIGIT')) {
    define('DIGIT', 2);
}
if (!defined('INT_MAX')) {
    define('INT_MAX', (integer) pow(10, DIGIT));
}
if (!defined('INT_MIN')) {
    define('INT_MIN', 0);
}
if (!defined('BASE')) {
    define('BASE', (integer) (INT_MAX - INT_MIN));
}

if (!defined('CLOCK_MAX')) {
    define('CLOCK_MAX', "100000000000000000000000");
}
if (!defined('CLOCK_MIN')) {
    define('CLOCK_MIN', "0");
}

if (!defined('SESSION_MAX')) {
    define('SESSION_MAX', "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF");//.CLOCK_MAX);
                         //050F550EB44F6DE53333AE460EE85396
}
if (!defined('SESSION_MIN')) {
    define('SESSION_MIN', "0");
}

if (!defined('BOUNDARY')) {
    define('BOUNDARY', (integer) pow(10, DIGIT / 2));
}
// </ED> ====================================================================


require_once 'logootTest.php';
require_once 'logootTest1.php';
require_once 'logootTest2.php';

/**
 * Description of logootTestSuite
 *
 * @author Jean-Philippe Muller
 */

class logootTestSuite {

    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('p2p');
        $suite->addTestSuite('logootTest');
        $suite->addTestSuite('logootTest1');
        $suite->addTestSuite('logootTest2');
        return $suite;
    }
}

logootTestSuite::main();

?>
