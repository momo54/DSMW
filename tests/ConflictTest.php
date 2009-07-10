<?php
include("LogootTests/Phpwikibot.php");
require_once 'PHPUnit/Framework.php';
require_once 'LogootTests/LogootOp.php';
require_once 'LogootTests/LogootIns.php';
require_once 'LogootTests/LogootDel.php';
require_once 'LogootTests/BlobInfo.php';
require_once 'LogootTests/LogootId.php';
require_once 'LogootTests/LogootPosition.php';
require_once 'LogootTests/DiffEngine.php';
require_once 'LogootTests/persistentClock.php';
define ('INT_MAX', "18446744073709551616");
define ('INT_MIN', "0");
/**
 * Description of ConflictTest
 *
 * @author mullejea
 */
class ConflictTest extends PHPUnit_Framework_TestCase {

    function testPosGeneration(){



        $int = "5";
        $int1 = "6";
        $sid = "1";
        if ($int<$int1) {
            $id = new LogootId($int, $sid);
            $id1 = new LogootId($int1, $sid);
        }
        else{
            $id1 = new LogootId($int, $sid);
            $id = new LogootId($int1, $sid);
        }

        $pos = array($id);
        $pos1 = array($id1);
        $start = new LogootPosition($pos);
        $end = new LogootPosition($pos1);

        $BI = new BlobInfo;
        $BI->setBlobInfo(array($start, $end));
        $result = $BI->getNPositionID($start, $end, '1', session_id());

        $handle = fopen("/home/mullejea/Bureau/file.txt", "w");
        fwrite($handle, " start: ");
        foreach ($pos as $id){
            fwrite($handle, $id->toString());
        }
        fwrite($handle, " generated: ");
        foreach ($result as $position){
            foreach ($position->getThisPosition() as $Lid){
                fwrite($handle, $Lid->toString());
            }
        }
        fwrite($handle, " end: ");
        foreach ($pos1 as $id){
            fwrite($handle, $id->toString());
        }

        $this->assertLessThan($end, $result[0]);
        $this->assertGreaterThan($start, $result[0]);

    }

   

    function testIntegration(){

        $blobInfo = new BlobInfo;
        $oldtext = "";
        $fp = fopen(dirname( __FILE__ )."/LogootTests/text1.txt", "r");
        $actualtext = fread($fp, filesize(dirname( __FILE__ )."/LogootTests/text1.txt"));
        fclose($fp);
        $blobInfo->setTextImage($actualtext);

        $listTextLines = $blobInfo->getBlobInfoText();

        //le texte du fichier fait 43 lignes!!
        $this->assertEquals(114, count($listTextLines));

        //on ajoute 5 lignes
        $blobInfo->addLine('10', "line");
        $blobInfo->addLine('10', "line");
        $blobInfo->addLine('10', "line");
        $blobInfo->addLine('10', "line");
        $blobInfo->addLine('10', "line");
        $listTextLines = $blobInfo->getBlobInfoText();
        $this->assertEquals(119, count($listTextLines));

        //on retire 30 lignes
        for($i=0;$i<30;$i++){
            $blobInfo->deleteLine('1');
        }

        $listTextLines = $blobInfo->getBlobInfoText();
        $this->assertEquals(89, count($listTextLines));

        //on retire 89 lignes
        for($i=0;$i<89;$i++){
            $blobInfo->deleteLine('1');
        }
        $listTextLines = $blobInfo->getBlobInfoText();
        $this->assertEquals(0, count($listTextLines));
    }

    function testPosIntegration(){
//        $pc = new persistentClock();
//        $pc->load();
        $blob = new BlobInfo;
        $oldtext = "";
        $fp = fopen(dirname( __FILE__ )."/LogootTests/text1.txt", "r");
        $actualtext = fread($fp, filesize(dirname( __FILE__ )."/LogootTests/text1.txt"));
        fclose($fp);


        $diffs = $blob->handleDiff($oldtext, $actualtext, $firstRev=1/*, $pc*/);
        $listPositions = $blob->getBlobInfo();


                $handle = fopen("/home/mullejea/Bureau/file.txt", "w");
                foreach ($blob->getBlobInfo() as $key=>$pos){
                    fwrite($handle, "\n ".$key." ");
                    foreach ($pos->getThisPosition() as $id){
                        fwrite($handle, " ".$id->toString()." ");
                    }
                }
                fclose($handle);
        //le texte du fichier fait 129 lignes!!
        $this->assertEquals(114, count($listPositions));

//        $pc->store();
//        unset($pc);
        unset ($blob);
    }

    function testConcIntegration(){
        $blobInfo = new BlobInfo;
        $fp = fopen(dirname( __FILE__ )."/LogootTests/text2.txt", "r");
        $conctext = fread($fp, filesize(dirname( __FILE__ )."/LogootTests/text2.txt"));
        fclose($fp);
        //$blobInfo->setTextImage($conctext);
        $listPos = $blobInfo->handleDiff($oldtext/*V0*/, $conctext/*V2*/, $firstRev=1/*, $pc*/);
        $blobInfo1 = new BlobInfo;
        $oldtext = "";
        $fp = fopen(dirname( __FILE__ )."/LogootTests/text1.txt", "r");
        $actualtext = fread($fp, filesize(dirname( __FILE__ )."/LogootTests/text1.txt"));
        fclose($fp);

        $listPos = $blobInfo1->handleDiff($oldtext/*V0*/, $actualtext/*V2*/, $firstRev=1/*, $pc*/);
        //integration des diffs entre VO et V2 dans V1
        foreach ($listPos as $operation){
            $blobInfo->integrateBlob($operation/*, $pc*/);
        }

        $listPositions = $blobInfo->getBlobInfo();
        //text3 fait 27lignes et text2 fait 10lignes
        $this->assertEquals(124, count($listPositions));

    }

}
?>
