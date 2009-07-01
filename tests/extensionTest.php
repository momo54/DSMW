<?php
//require_once('../p2pExtension.php');
require_once('p2pExtension.php');
require_once('../files/utils.php');
require_once 'p2pBot.php';
require_once 'BasicBot.php';
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of extensionTest
 *
 * @author mullejea
 */
class extensionTest extends PHPUnit_Framework_TestCase {
     var $p2pBot1;
     var $tmpServerName;
     var $tmpScriptPath;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        //storage of the global variable value
        global $wgServerName, $wgScriptPath;
        $this->tmpServerName = $wgServerName;
        $this->tmpScriptPath = $wgScriptPath;

        exec('./initWikiTest.sh');
        //wfDebugLog('p2p','start p2p Test');
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = 'http://localhost/wiki1';
        $this->p2pBot1 = new p2pBot($basicbot1);

    }


    function testGetOperations() {
     global $wgServerName, $wgScriptPath;
     $wgServerName = $this->p2pBot1->bot->wikiServer;
     $wgScriptPath = '';
        /*1st patch*/
        $pageName = "Patch:localhost/wiki1901";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1901]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]] [[hasOperation::localhost/wiki1903;
Insert;( 5053487913627490222,42601d9c1af38da968d697efde65a473 ) 901;content1]]
previous: [[previous::none]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);

        $operations = getOperations($patchId);
        //assert
        $this->assertEquals('2', count($operations));
        //$this->assertEquals('patch:localhost/wiki1901', $lastPatchId);
       // unset ($patch);



    }

     protected function tearDown() {
        global $wgServerName, $wgScriptPath;
        $wgServerName = $this->tmpServerName;
        $wgScriptPath = $this->tmpScriptPath;
    }

}
?>
