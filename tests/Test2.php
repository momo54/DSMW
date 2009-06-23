<?php
//require_once('../p2pExtension.php');
require_once('../patch/Patch.php');
require_once('../files/utils.php');
require_once('../clockEngine/persistentClock.php');
require_once 'p2pBot.php';
require_once 'BasicBot.php';

/**
 * Description of Test_2
 *
 * @author mullejea
 */




class Test2 extends PHPUnit_Framework_TestCase {


    var $p2pBot1;
    var $p2pBot2;
    var $p2pBot3;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        exec('./initWikiTest.sh');
        //wfDebugLog('p2p','start p2p Test');
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = 'http://localhost/wiki1';
        $this->p2pBot1 = new p2pBot($basicbot1);

//        $basicbot2 = new BasicBot();
//        $basicbot2->wikiServer = 'http://localhost/wiki2';
//        $this->p2pBot2 = new p2pBot($basicbot2);
//
//        $basicbot3 = new BasicBot();
//        $basicbot3->wikiServer = 'http://localhost/wiki3';
//        $this->p2pBot3 = new p2pBot($basicbot3);
    }
/**
 *
 */
//    function testGetReqPagesFct(){
//
//    }

      function testGetLastPatchIdwithoutConc(){
        $patchIds = array();
        $previous = array();

        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertFalse($lastPatchId);//false because there no previous patch
        unset ($patch);
        
        
        /*1st patch*/
        $pageName = "Patch:localhost/wiki1901";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1901]]
 onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::none]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patchIds[]='localhost/wiki1901';
        $previous[]='none';
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('Patch:localhost/wiki1901', $lastPatchId);
        unset ($patch);
        
        /*2nd patch*/
        $pageName = "Patch:localhost/wiki1902";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1902]]
 onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0901]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patchIds[]='localhost/wiki1902';
        $previous[]='Patch:Localhost/www/mediawiki-1.14.0901';
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('Patch:localhost/wiki1902', $lastPatchId);
        unset ($patch);

        /*3rd patch*/
        $pageName = "Patch:localhost/wiki1802";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1802]]
 onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0902]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patchIds[]='localhost/wiki1802';
        $previous[]='Patch:Localhost/www/mediawiki-1.14.0902';
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('Patch:localhost/wiki1802', $lastPatchId);
        unset ($patch);

        /*4th patch*/
        $pageName = "Patch:localhost/wiki1803";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1803]]
 onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0802]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patchIds[]='localhost/wiki1803';
        $previous[]='Patch:Localhost/www/mediawiki-1.14.0802';
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('Patch:localhost/wiki1803', $lastPatchId);
        unset ($patch);

        /*5th patch*/
        $pageName = "Patch:localhost/wiki1700";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1700]]
 onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0803]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patchIds[]='localhost/wiki1700';
        $previous[]='Patch:Localhost/www/mediawiki-1.14.0803';
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('Patch:localhost/wiki1700', $lastPatchId);
        unset ($patch);

        /*6th patch*/
        $pageName = "Patch:localhost/wiki1905";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1905]]
 onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0700]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patchIds[]='localhost/wiki1905';
        $previous[]='Patch:Localhost/www/mediawiki-1.14.0700';
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('Patch:localhost/wiki1905', $lastPatchId);
        unset ($patch);
        
      }

//      function testGetLastPatchIdwithConc(){
//        $patchIds = array();
//        $previous = array();
//        $pageName = "Patch:localhost/wiki1901";
//        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1901]]
// onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
//Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
//previous: [[previous::none]]';
//        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
//        $patchIds[]='localhost/wiki1901';
//        $previous[]='none';
//        $patch = new Patch('', '', '', '');
//        $lastPatchId = $patch->getLastPatchId($pageName);
//        //assert
//        unset ($patch);
//
//        $pageName = "Patch:localhost/wiki1902";
//        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1902]]
// onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
//Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
//previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0901]]';
//        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
//        $patchIds[]='localhost/wiki1902';
//        $previous[]='Patch:Localhost/www/mediawiki-1.14.0901';
//        $patch = new Patch('', '', '', '');
//        $lastPatchId = $patch->getLastPatchId($pageName);
//        //assert
//        unset ($patch);
//
//        $pageName = "Patch:localhost/wiki1802";
//        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1802]]
// onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
//Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
//previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0902]]';
//        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
//        $patchIds[]='localhost/wiki1802';
//        $previous[]='Patch:Localhost/www/mediawiki-1.14.0902';
//        $patch = new Patch('', '', '', '');
//        $lastPatchId = $patch->getLastPatchId($pageName);
//        //assert
//        unset ($patch);
//
//        $pageName = "Patch:localhost/wiki1803";
//        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1803]]
// onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
//Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
//previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0902]]';
//        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
//        $patchIds[]='localhost/wiki1803';
//        $previous[]='Patch:Localhost/www/mediawiki-1.14.0902';
//        $patch = new Patch('', '', '', '');
//        $lastPatchId = $patch->getLastPatchId($pageName);
//        //assert
//        unset ($patch);
//
//        $pageName = "Patch:localhost/wiki1700";
//        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1700]]
// onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
//Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
//previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0803]]';
//        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
//        $patchIds[]='localhost/wiki1700';
//        $previous[]='Patch:Localhost/www/mediawiki-1.14.0803';
//        $patch = new Patch('', '', '', '');
//        $lastPatchId = $patch->getLastPatchId($pageName);
//        //assert
//        unset ($patch);
//
//        $pageName = "Patch:localhost/wiki1905";
//        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1905]]
// onPage: [[onPage::Paris]]  hasOperation: [[hasOperation::localhost/wiki1902;
//Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
//previous: [[previous::Patch:Localhost/www/mediawiki-1.14.0700]]';
//        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
//        $patchIds[]='localhost/wiki1905';
//        $previous[]='Patch:Localhost/www/mediawiki-1.14.0700';
//        $patch = new Patch('', '', '', '');
//        $lastPatchId = $patch->getLastPatchId($pageName);
//        //assert
//        unset ($patch);
//
//
//      }

}

?>
