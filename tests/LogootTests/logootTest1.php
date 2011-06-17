<?php

if (!defined('MEDIAWIKI')) {
    define('MEDIAWIKI', true);
}
$wgDebugLogFile  = "debug.log";
$wgDebugLogGroups  = array(
    'p2p'     => "debug-p2p-t1.log",
    'ed'      => "debug-ed-t1.log"
);
// <ED> =====================================================================
if (!defined('DIGIT')) {
    define('DIGIT', "2");
}
if (!defined('INT_MAX')) {
    define('INT_MAX', (string) pow(10, DIGIT));
}
if (!defined('INT_MIN')) {
    define('INT_MIN', "0");
}
if (!defined('BASE')) {
    define('BASE', (string) (INT_MAX - INT_MIN));
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
    define('BOUNDARY', (string) pow(10, DIGIT / 2));
}
// </ED> ====================================================================

require_once '../../logootComponent/LogootOperation.php';
require_once '../../logootComponent/LogootPlusOperation.php';
require_once '../../logootComponent/LogootId.php';
require_once '../../logootComponent/LogootPosition.php';
require_once '../../logootComponent/logoot.php';
require_once '../../logootComponent/logootPlus.php';
require_once '../../logootComponent/logootEngine.php';
require_once '../../logootComponent/logootPlusEngine.php';
require_once '../../logootComponent/LogootIns.php';
require_once '../../logootComponent/LogootDel.php';
require_once '../../logootComponent/LogootPlusIns.php';
require_once '../../logootComponent/LogootPlusDel.php';
require_once '../../logootComponent/LogootPatch.php';

require_once '../../logootComponent/DiffEngine.php';
require_once '../../logootComponent/Math/BigInteger.php';

require_once '../../logootModel/boModel.php';
require_once '../../logootModel/dao.php';
require_once '../../logootModel/manager.php';
require_once '../../logootModel/boModelPlus.php';


require_once '../../../../includes/GlobalFunctions.php';
require_once 'utils.php';


/**
 * Description of ConflictTest
 *
 * @author mullejea
 */
class logootTest1 extends PHPUnit_Framework_TestCase {

    function testPosGeneration() {

        $int = "5";
        $int1 = "6";
        $sid = "1";
        if ($int < $int1) {
            $id = new LogootId($int, $sid);
            $id1 = new LogootId($int1, $sid);
        } else {
            $id1 = new LogootId($int, $sid);
            $id = new LogootId($int1, $sid);
        }

        $pos = array($id);
        $pos1 = array($id1);
        $start = new LogootPosition($pos);
        $end = new LogootPosition($pos1);

        $model = manager::loadModel(0);
        $model->setPositionlist(array(0 => LogootPosition::minPosition(), 
            1 => $start, 2 => $end,
            3 => LogootPosition::maxPosition()));
        $model->setLinelist(array(0 => "", 
            1 => 'start', 2 => 'end',
            3 => ""));
        //$logoot = new logootEngine($model);
        $logoot = manager::getNewEngine($model);

        //insert X
        $oldContent = "start\nend";
        $newContent = "start\nline1\nend";
        $listOp1 = $logoot->generate($oldContent, $newContent);

        //$this->assertGreaterThan($end, $listOp1[0]->getLogootPosition());
        //$this->assertLessThan($end, $listOp1[0]->getLogootPosition());
        $this->assertEquals(1, $end->compareTo($listOp1[0]->getLogootPosition()));
        //$this->assertGreaterThan($start, $listOp1[0]->getLogootPosition());
        $this->assertEquals(-1, $start->compareTo($listOp1[0]->getLogootPosition()));
    }

    function testIntegration() {
        $oldtext = "";
        $fp = fopen(dirname(__FILE__) . "/text1.txt", "r");
        $actualtext = fread($fp, filesize(dirname(__FILE__) . "/text1.txt"));
        fclose($fp);
        $model = manager::loadModel(0);
        //$logoot = new logootEngine($model);
        $logoot = manager::getNewEngine($model);
        $listOp = $logoot->generate($oldtext, $actualtext);
        $modelAssert = $logoot->getModel();


        //the file's text has 114 lines!!
        $this->assertEquals(116, count($modelAssert->getPositionlist()));
        $this->assertEquals(116, count($modelAssert->getLinelist()));

        //we add 5 lines
        $oldtext = $actualtext;
        $actualtext .= "\nline1\nline2\nline3\nline4\nline5";
        $listOp = $logoot->generate($oldtext, $actualtext);
        $modelAssert = $logoot->getModel();
        $this->assertEquals(121, count($modelAssert->getPositionlist()));
        $this->assertEquals(121, count($modelAssert->getLinelist()));

        //delete 30 lines
        $oldtext = $actualtext;
        $textTab = explode("\n", $actualtext);
        for ($i = 0; $i < 30; $i++) {
            array_shift($textTab);
        }
        $actualtext = implode("\n", $textTab);
        $listOp = $logoot->generate($oldtext, $actualtext);
        $modelAssert = $logoot->getModel();
        $this->assertEquals(91, count($modelAssert->getPositionlist()));
        $this->assertEquals(91, count($modelAssert->getLinelist()));

        //delete 89 lines, it should remain 1 empty line
        $oldtext = $actualtext;
        $actualtext = "";
        $listOp = $logoot->generate($oldtext, $actualtext);
        $modelAssert = $logoot->getModel();
        $this->assertEquals(3, count($modelAssert->getPositionlist()));
        $this->assertEquals(3, count($modelAssert->getLinelist()));
    }

    function testConcIntegration() {


        $oldtext = "";
        $fp = fopen(dirname(__FILE__) . "/text2.txt", "r");
        $conctext = fread($fp, filesize(dirname(__FILE__) . "/text2.txt"));
        fclose($fp);
        $model = manager::loadModel(0);
        //$logoot = new logootEngine($model);
        $logoot = manager::getNewEngine($model);
        $listOp = $logoot->generate($oldtext, $conctext);



        //We get the operations list generated on a text 'text1'
        $oldtext = "";
        $fp = fopen(dirname(__FILE__) . "/text1.txt", "r");
        $actualtext = fread($fp, filesize(dirname(__FILE__) . "/text1.txt"));
        fclose($fp);
        $model1 = manager::loadModel(0);
        //$logoot1 = new logootEngine($model1);
        $logoot1 = manager::getNewEngine($model1);
        $listOp1 = $logoot1->generate($oldtext, $actualtext);

        // we integrate the op list into the model generated from the text2
        $logoot->integrate($listOp1);
        $modelAssert = $logoot->getModel();

        $this->assertEquals(126, count($modelAssert->getPositionlist()));
        $this->assertEquals(126, count($modelAssert->getLinelist()));
    }

    function testConcDelOpIntegration() {


        $oldtext = "";
        $conctext = "line1\nline2\nline3\nline4";
        $model = manager::loadModel(0);
        //$logoot = new logootEngine($model);
        $logoot = manager::getNewEngine($model);
        $listOp = $logoot->generate($oldtext, $conctext);
        //$model has 4 lines created by 4 ins operations

        $tmpMod = $logoot->getModel();
        $this->assertEquals(6, count($tmpMod->getPositionlist()));
        $this->assertEquals(6, count($tmpMod->getLinelist()));

        $oldtext = "line1\nline2\nline3\nline4";
        $actualtext = "line1\nline2\nline4";

        $listOp1 = $logoot->generate($oldtext, $actualtext);

        $tmpMod = $logoot->getModel();
        $this->assertEquals(5, count($tmpMod->getPositionlist()));
        $this->assertEquals(5, count($tmpMod->getLinelist()));

        $logoot->integrate($listOp1);
        $modelAssert = $logoot->getModel();

        $this->assertEquals(5, count($modelAssert->getPositionlist()));
        $this->assertEquals(5, count($modelAssert->getLinelist()));
    }

}

?>
