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

    function testGetLastPatchIdwithoutConc() {

        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertFalse($lastPatchId);//false because there's no previous patch
        unset ($patch);


        /*1st patch*/
        $pageName = "Patch:localhost/wiki1901";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1901]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::none]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki1901', $lastPatchId);
        unset ($patch);

        /*2nd patch*/
        $pageName = "Patch:localhost/wiki1902";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1902]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki1901]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki1902', $lastPatchId);
        unset ($patch);

        /*3rd patch*/
        $pageName = "Patch:localhost/wiki1802";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1802]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki1902]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki1802', $lastPatchId);
        unset ($patch);

        /*4th patch*/
        $pageName = "Patch:localhost/wiki1803";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1803]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki1802]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki1803', $lastPatchId);
        unset ($patch);

        /*5th patch*/
        $pageName = "Patch:localhost/wiki1700";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1700]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki1803]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki1700', $lastPatchId);
        unset ($patch);

        /*6th patch*/
        $pageName = "Patch:localhost/wiki1905";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1905]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki1700]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki1905', $lastPatchId);
        unset ($patch);

    }

    function testGetLastPatchIdwithConc() {
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper1', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertFalse($lastPatchId);//false because there's no previous patch
        unset ($patch);


        /*1st patch*/
        $pageName = "Patch:localhost/wiki19010";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki19010]]
 onPage: [[onPage::cooper1]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::none]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper1', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki19010', $lastPatchId);
        unset ($patch);

        /*2nd patch*/
        $pageName = "Patch:localhost/wiki19020";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki19020]]
 onPage: [[onPage::cooper1]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki19010]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper1', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki19020', $lastPatchId);
        unset ($patch);

        /*3rd patch*/
        $pageName = "Patch:localhost/wiki18020";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki18020]]
 onPage: [[onPage::cooper1]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki19020]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper1', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki18020', $lastPatchId);
        unset ($patch);

        /*4th patch*/
        $pageName = "Patch:localhost/wiki18030";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki18030]]
 onPage: [[onPage::cooper1]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki19020]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper1', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki18030', current($lastPatchId));
        next($lastPatchId);
        $this->assertEquals('patch:localhost/wiki18020', current($lastPatchId));
        unset ($patch);

        /*5th patch*/
        $pageName = "Patch:localhost/wiki17000";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki17000]]
 onPage: [[onPage::cooper1]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki18030;Patch:Localhost/wiki18020]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper1', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki17000', $lastPatchId);
        unset ($patch);

        /*6th patch*/
        $pageName = "Patch:localhost/wiki19050";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki19050]]
 onPage: [[onPage::cooper1]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::Patch:Localhost/wiki17000]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);
        $patch = new Patch('', '', '', '');
        $lastPatchId = $patch->getLastPatchId('cooper1', $this->p2pBot1->bot->wikiServer);
        //assert
        $this->assertEquals('patch:localhost/wiki19050', $lastPatchId);
        unset ($patch);
    }

}

?>
