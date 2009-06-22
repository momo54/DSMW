<?php
require_once('../p2pExtension.php');
require_once('../patch/Patch.php');

/**
 * Description of Test_2
 *
 * @author mullejea
 */




class Test2 extends PHPUnit_Framework_TestCase{


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

      function testGetLastPatchId(){
        $pageName = "Paris";
        $content='content';
        $res = $this->p2pBot1->createPage($pageName,$content);
        $res = $this->p2pBot1->createPage($pageName,$content);
        $res = $this->p2pBot1->createPage($pageName,$content);
        $res = $this->p2pBot1->createPage($pageName,$content);
        $res = $this->p2pBot1->createPage($pageName,$content);
        $res = $this->p2pBot1->createPage($pageName,$content);
        $patch = new Patch('', '', '', '');
        $lastPatch = $patch->getLastPatchId($pageName);
      }

}

?>
