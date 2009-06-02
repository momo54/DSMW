<?php
require_once 'LogootId.php';
require_once 'LogootPosition.php';
define ('INT_MAX', "1000000000000000000000");//22
define ('INT_MIN', "0");
/**
 * Description of Test_1
 *
 * @author mullejea
 */
class Test_1  extends PHPUnit_Framework_TestCase{

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
}
?>
