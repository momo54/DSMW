<?php
//require_once 'LogootTests/LogootId.php';
//require_once 'LogootTests/LogootPosition.php';
require_once '../logootEngine/LogootId.php';
require_once '../logootEngine/LogootPosition.php';
require_once '../logootEngine/BlobInfo.php';
require_once '../logootop/LogootOp.php';
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

        $id1 = new LogootId("10000", "10000");
        $id2 = new LogootId("1000000", "1000000");
        $id3 = new LogootId("2000000", "2000000");
        $position1 = new LogootPosition(array($id1, $id3));
        $position2 = new LogootPosition(array($id2));
        $this->assertEquals('0', $position1->compareTo($position1));
        $this->assertEquals('-1', $position1->compareTo($position2));
        $this->assertEquals('1', $position2->compareTo($position1));
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
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid='123');
        $position1 = $positions[0];
        $insert1 = new LogootIns(1, $position1, 'X');
        $blobInfo->integrateBlob($insert1);
        
        //insert2
        $start = $blobInfo->getPrevPosition(1);
        $end = $blobInfo->getNextPosition(1);
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid='123');
        $position2 = $positions[0];
        $insert2 = new LogootIns(1, $position2, 'Y');
        $blobInfo->integrateBlob($insert2);

        //insert3
        $start = $blobInfo->getPrevPosition(1);
        $end = $blobInfo->getNextPosition(1);
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid='123');
        $position3 = $positions[0];
        $insert3 = new LogootIns(1, $position3, 'Z');
        $blobInfo->integrateBlob($insert3);
        //blobInfo is the page model where the 3 inserts (at line 1) are generated
        
        
        //blobInfo1 is another page model where we execute the 3 inserts (at line 1) in another order
        $blobInfo1 = BlobInfo::loadBlobInfo(0);
        $blobInfo1->integrateBlob($insert2);
        $blobInfo1->integrateBlob($insert1);
        $blobInfo1->integrateBlob($insert3);

        $this->assertEquals($blobInfo->getBlobInfo(), $blobInfo1->getBlobInfo());
        $this->assertEquals($blobInfo->getBlobInfoText(), $blobInfo1->getBlobInfoText());

        unset($blobInfo1);

        //assert
        //blobInfo1 is another page model where we execute the 3 inserts in another order
        $blobInfo1 = BlobInfo::loadBlobInfo(0);
        $blobInfo1->integrateBlob($insert3);
        $blobInfo1->integrateBlob($insert2);
        $blobInfo1->integrateBlob($insert1);

        $this->assertEquals($blobInfo->getBlobInfo(), $blobInfo1->getBlobInfo());
        $this->assertEquals($blobInfo->getBlobInfoText(), $blobInfo1->getBlobInfoText());

        unset($blobInfo1);

        //assert
        //blobInfo1 is another page model where we execute the 3 inserts in another order
        $blobInfo1 = BlobInfo::loadBlobInfo(0);
        $blobInfo1->integrateBlob($insert1);
        $blobInfo1->integrateBlob($insert3);
        $blobInfo1->integrateBlob($insert2);

        $this->assertEquals($blobInfo->getBlobInfo(), $blobInfo1->getBlobInfo());
        $this->assertEquals($blobInfo->getBlobInfoText(), $blobInfo1->getBlobInfoText());

        unset($blobInfo1);

        //assert
        //blobInfo1 is another page model where we execute the 3 inserts in another order
        $blobInfo1 = BlobInfo::loadBlobInfo(0);
        $blobInfo1->integrateBlob($insert2);
        $blobInfo1->integrateBlob($insert3);
        $blobInfo1->integrateBlob($insert1);

        $this->assertEquals($blobInfo->getBlobInfo(), $blobInfo1->getBlobInfo());
        $this->assertEquals($blobInfo->getBlobInfoText(), $blobInfo1->getBlobInfoText());

        unset($blobInfo1);

        //assert
        //blobInfo1 is another page model where we execute the 3 inserts in another order
        $blobInfo1 = BlobInfo::loadBlobInfo(0);
        $blobInfo1->integrateBlob($insert3);
        $blobInfo1->integrateBlob($insert1);
        $blobInfo1->integrateBlob($insert2);

        $this->assertEquals($blobInfo->getBlobInfo(), $blobInfo1->getBlobInfo());
        $this->assertEquals($blobInfo->getBlobInfoText(), $blobInfo1->getBlobInfoText());
    }

    function testInsertDelete(){
         $blobInfo = BlobInfo::loadBlobInfo(0);
        //setup the first and the last lines
        $start = new LogootPosition(array(LogootId::IdMin(), LogootId::IdMin()));
        $end = new LogootPosition(array(LogootId::IdMax(), LogootId::IdMax()));

        //insert1
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid='123');
        $position1 = $positions[0];
        $insert1 = new LogootIns(1, $position1, 'X');
        $blobInfo->integrateBlob($insert1);

        //insert2
        $start = $blobInfo->getPrevPosition(1);
        $end = $blobInfo->getNextPosition(1);
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid='123');
        $position2 = $positions[0];
        $insert2 = new LogootIns(1, $position2, 'Y');
        $blobInfo->integrateBlob($insert2);

        $position = $blobInfo->getPosition(1);
        $delete = new LogootDel($position, ' ');
        $blobInfo->integrateBlob($delete);

        //blobInfo is the page model where the 3 operations are generated
        /*ins(1,X), ins(1,Y), del(1)*/


        //blobInfo1 is another page model where we execute the 3 operations in another order

         /*ins(1,X), ins(1,Y), del(1)*/
        $blobInfo1 = BlobInfo::loadBlobInfo(0);
        $blobInfo1->integrateBlob($insert1);
        $blobInfo1->integrateBlob($insert2);
        $blobInfo1->integrateBlob($delete);
        //assert
        $this->assertEquals($blobInfo->getBlobInfo(), $blobInfo1->getBlobInfo());
        $this->assertEquals($blobInfo->getBlobInfoText(), $blobInfo1->getBlobInfoText());

        unset($blobInfo1);

        /*ins(1,Y), ins(1,X), del(1)*/
        $blobInfo1 = BlobInfo::loadBlobInfo(0);
        $blobInfo1->integrateBlob($insert2);
        $blobInfo1->integrateBlob($insert1);
        $blobInfo1->integrateBlob($delete);

        $this->assertEquals($blobInfo->getBlobInfo(), $blobInfo1->getBlobInfo());
        $this->assertEquals($blobInfo->getBlobInfoText(), $blobInfo1->getBlobInfoText());
    }

    function testManyIdPosition(){
        $blobInfo = BlobInfo::loadBlobInfo(0);
        //setup the first and the last lines
        $start = new LogootPosition(array(LogootId::IdMin(), LogootId::IdMin()));
        $end = new LogootPosition(array(LogootId::IdMax(), LogootId::IdMax()));

        //insert1
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid='123');
        $position1 = $positions[0];
        $insert1 = new LogootIns(1, $position1, 'X');
        $blobInfo->integrateBlob($insert1);

        //inserts
        for ($i=0; $i<500; $i++){
        $start = $blobInfo->getPrevPosition($i+2);
        $end = $blobInfo->getNextPosition($i+2);
        $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid='123');
        $position2 = $positions[0];
        $insert2 = new LogootIns(1, $position2, 'Y');
        $blobInfo->integrateBlob($insert2);
        }
        for ($j=1; $j<500; $j++){
            $testpos = $blobInfo->getPosition($j);
            $testpos1 = $blobInfo->getPosition($j+1);
            $this->assertEquals('-1', $testpos->compareTo($testpos1));
            $this->assertEquals('1', $testpos1->compareTo($testpos));
        }
    }
}
?>
