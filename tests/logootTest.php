<?php
//require_once 'LogootTests/LogootId.php';
//require_once 'LogootTests/LogootPosition.php';
require_once '../logootEngine/LogootId.php';
require_once '../logootEngine/LogootPosition.php';
require_once '../logootEngine/BlobInfo.php';
require_once '../logootop/LogootIns.php';
require_once '../logootop/LogootDel.php';
define ('INT_MAX', "1000000000000000000000");//22
define ('INT_MIN', "0");
/**
 * Description of Test_1
 *
 * @author mullejea
 */
class logootTest  extends PHPUnit_Framework_TestCase{

    function testIdCompareTo(){
        $id1 = new LogootId("10000", "10000");
        $id2 = new LogootId("1000000", "1000000");
        $this->assertEquals('-1', $id1->compareTo($id2));
        $id1->setInt("500000");
        $id1->setSessionId("500000");
        $id2->setInt("20");
        $id2->setSessionId("50");
        $this->assertEquals('1', $id1->compareTo($id2));
        $id1->setInt("10");
        $id1->setSessionId("10");
        $id2->setInt("10");
        $id2->setSessionId("10");
        $this->assertEquals('0', $id1->compareTo($id2));
        $id1->setInt(INT_MIN);
        $id1->setSessionId("10");
        $id2->setInt(INT_MAX);
        $id2->setSessionId("10");
        $this->assertEquals('-1', $id1->compareTo($id2));
        $this->assertEquals('1', LogootId::IdMax()->compareTo(LogootId::IdMin()));
    }

    function testPositionCompareTo(){
        $pos = new LogootPosition(array(LogootId::IdMin(), LogootId::IdMin()));
        $pos1 = new LogootPosition(array(LogootId::IdMax(), LogootId::IdMax()));
        $this->assertEquals('-1', $pos->compareTo($pos1));
        $this->assertEquals('1', $pos1->compareTo($pos));
        $this->assertEquals('0', $pos->compareTo($pos));
        $this->assertEquals('0', $pos1->compareTo($pos1));
    }

    function testVectorMinSize(){
        $id1 = new LogootId("10000", "10000");
        $pos = new LogootPosition(array($id1,$id1,$id1, $id1, $id1));
        $pos1 = new LogootPosition(array($id1, $id1, $id1));
        $this->assertEquals('3', $pos->vectorMinSizeComp($pos1));
    }

    function testVectorSizeComp(){
        $id1 = new LogootId("10000", "10000");
        $pos = new LogootPosition(array($id1,$id1,$id1, $id1, $id1));
        $pos1 = new LogootPosition(array($id1, $id1, $id1));
        $this->assertEquals('-1', $pos->vectorSizeComp($pos1));
        $pos = new LogootPosition(array($id1,$id1,$id1, $id1, $id1));
        $pos1 = new LogootPosition(array($id1, $id1, $id1, $id1, $id1));
        $this->assertEquals('5', $pos->vectorSizeComp($pos1));
    }

    function testEquals(){
         $id1 = new LogootId("10000", "10000");
         $id2 = new LogootId("1000000", "1000000");
         $pos = new LogootPosition(array($id1));
         $this->assertFalse($pos->equals($id1, $id2));
         $id2 = new LogootId("10000", "10000");
         $this->assertTrue($pos->equals($id1, $id2));
    }

    function testNEquals(){
         $pos = new LogootPosition(array(LogootId::IdMin(), LogootId::IdMin()));
        $pos1 = new LogootPosition(array(LogootId::IdMax(), LogootId::IdMax()));
        $this->assertEquals('0', $pos->nEquals($pos1));
        $pos1 = new LogootPosition(array(LogootId::IdMin(), LogootId::IdMin()));
        $this->assertEquals('1', $pos->nEquals($pos1));
        $pos1 = new LogootPosition(array(LogootId::IdMin(), LogootId::IdMin(),
                LogootId::IdMin()));
        $this->assertEquals('0', $pos->nEquals($pos1));
    }

    function testInsert(){
        $blobInfo = BlobInfo::loadBlobInfo(0);
        //setup the first and the last lines
        $start = new LogootPosition(array(LogootId::IdMin(), LogootId::IdMin()));
        $end = new LogootPosition(array(LogootId::IdMax(), LogootId::IdMax()));
        
        //insert1
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid=session_id());
        $position1 = $positions[0];
        $insert1 = LogootIns(1, $position1, 'X');
        
        
        //insert2
        $start = $blobInfo->getPrevPosition(1);
        $end = $blobInfo->getNextPosition(1);
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid=session_id());
        $position2 = $positions[0];
        $insert2 = LogootIns(1, $position2, 'Y');


        //insert3
        $start = $blobInfo->getPrevPosition(1);
        $end = $blobInfo->getNextPosition(1);
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid=session_id());
        $position3 = $positions[0];
        $insert3 = LogootIns(1, $position3, 'Z');


        $blobInfo->integrateBlob($insert1);
        $blobInfo->integrateBlob($insert2);
        $blobInfo->integrateBlob($insert3);

        //assert
    }
}
?>
