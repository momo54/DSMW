<?php
//require_once('../p2pExtension.php');
//require_once('../p2pExtension.php');
define( 'MEDIAWIKI', true );
require_once 'p2pBot.php';
require_once 'BasicBot.php';
include_once 'p2pAssert.php';
require_once '../../..//includes/GlobalFunctions.php';
require_once '../patch/Patch.php';
require_once '../files/utils.php';

/* To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of extensionTest
 *
 * @author mullejea
 */
class extensionTest extends PHPUnit_Framework_TestCase {
    var $p2pBot1;
    var $p2pBot2;
    var $p2pBot3;
    var $tmpServerName;
    var $tmpScriptPath;

    function  __construct() {

    }
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
    //storage of the global variable value
     /*   global $wgServerName, $wgScriptPath;
        $this->tmpServerName = $wgServerName;
        $this->tmpScriptPath = $wgScriptPath;*/


        exec('./initWikiTest.sh');
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = 'http://localhost/wiki1';
        $this->p2pBot1 = new p2pBot($basicbot1);

        $basicbot2 = new BasicBot();
        $basicbot2->wikiServer = 'http://localhost/wiki2';
        $this->p2pBot2 = new p2pBot($basicbot2);

        $basicbot3 = new BasicBot();
        $basicbot3->wikiServer = 'http://localhost/wiki3';
        $this->p2pBot3 = new p2pBot($basicbot3);
    }


    function testGetOperations() {
       /* global $wgServerName, $wgScriptPath;
        $wgServerName = $this->p2pBot1->bot->wikiServer;
        $wgScriptPath = '';
        //1st patch
        $pageName = "Patch:localhost/wiki1901";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1901]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]] [[hasOperation::localhost/wiki1903;
Insert;( 5053487913627490222,42601d9c1af38da968d697efde65a473 ) 901;content1]]
previous: [[previous::none]]';
        $res = $this->p2pBot1->createPage($pageName,$Patchcontent);

        $operations = getOperations($patchId);
        //assert
        $this->assertEquals('2', count($operations));*/
    //$this->assertEquals('patch:localhost/wiki1901', $lastPatchId);
    // unset ($patch);
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testGetRequestedPages() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testGetPushFeedRequest() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testGetPreviousCSID() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testGetPublishedPatches() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testUpdatePushFeed() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testIntegrate() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testOperationToLogootOp() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testLogootIntegrate() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    function testGetPatch() {
        $pageName = 'ChangeSet:localhost/wiki1';
        $content = '[[changeSetID::localhost/wiki1]] [[inPullFeed::pulltoto]]
            [[previousChangeSet::none]] [[hasPatch::Patch:localhost/wiki2]]';
        $this->p2pBot1->createPage($pageName, $content);

        $pageName = 'Patch:localhost/wiki2';
        $content = '[[patchID::'.$pageName.']] [[onPage::Paris]] [[previous::none]]
        [[hasOperation::Localhost/wiki121;Insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577) ;content page Paris]]';
        $this->p2pBot1->createPage($pageName,$content);


        $patchXML = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=patch&papatchId=Patch:Localhost/wiki2&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($patchXML);
        $patchs = $dom->getElementsByTagName('patch');

        foreach($patchs as $p) {
            $a = $p->getAttribute('id');
            $this->assertEquals('Patch:Localhost/wiki2', $p->getAttribute('id'));
            $a = $p->getAttribute('onPage');
            $this->assertEquals('Paris', $p->getAttribute('onPage'));
            $a = $p->getAttribute('previous');
            $this->assertEquals('None', substr($p->getAttribute('previous'),0,-1));
        }

        $listeOp = $dom->getElementsByTagName('operation');

        foreach($listeOp as $o)
            $op[] = $o->firstChild->nodeValue;
        $this->assertTrue(count($op)==1);
        $this->assertEquals('Localhost/wiki121;Insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577) ;content page Paris',$op[0]);

        $pageName = 'ChangeSet:localhost/wiki3';
        $content = '[[changeSetID::localhost/wiki3]] [[inPullFeed::pulltoto]]
            [[previousChangeSet::changeSetID::localhost/wiki1]] [[hasPatch::Patch:localhost/wiki4]]';
        $this->p2pBot1->createPage($pageName, $content);

        $pageName = 'Patch:localhost/wiki4';
        $content = '[[patchID::'.$pageName.']] [[onPage::Paris]] [[previous::none]]
        [[hasOperation::Localhost/wiki121;Insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577) ;content page Paris]]';
        $this->p2pBot1->createPage($pageName,$content);

        $patchXML = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=patch&papatchId=Patch:Localhost/wiki4&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($patchXML);
        $patchs = $dom->getElementsByTagName('patch');

        foreach($patchs as $p) {
            $a = $p->getAttribute('id');
            $this->assertEquals('Patch:Localhost/wiki4', $p->getAttribute('id'));
            $a = $p->getAttribute('onPage');
            $this->assertEquals('Paris', $p->getAttribute('onPage'));
            $a = $p->getAttribute('previous');
            $this->assertEquals('None', substr($p->getAttribute('previous'),0,-1));
        }

        $listeOp = $dom->getElementsByTagName('operation');
        $op = null;
        foreach($listeOp as $o)
            $op[] = $o->firstChild->nodeValue;
        $this->assertTrue(count($op)==1);
        $this->assertEquals('Localhost/wiki121;Insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577) ;content page Paris',$op[0]);

    }

    public function testGetChangeSet() {
        $pageName = "ChangeSet:localhost/wiki12";
        $content='ChangeSet:
changeSetID: [[changeSetID::localhost/wiki12]]
inPushFeed: [[inPushFeed::PushFeed:PushCity]]
previousChangeSet: [[previousChangeSet::none]]
 hasPatch: [[hasPatch::"Patch:Berlin1"]] hasPatch: [[hasPatch::"Patch:Paris0"]]';
        $this->p2pBot1->createPage($pageName, $content);

        $pageName = 'PushFeed:PushCity';
        $content = 'PushFeed:
Name: [[name::CityPush2]]
hasSemanticQuery: [[hasSemanticQuery::-5B-5BCategory:city-5D-5D]]
Pages concerned:
{{#ask: [[Category:city]]}} hasPushHead: [[hasPushHead::ChangeSet:localhost/mediawiki12]]';
        $this->p2pBot1->createPage($pageName,$content);

        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=changeSet&cspushName=PushCity&cschangeSet=none&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($cs);
        $changeSet = $dom->getElementsByTagName('changeSet');
        foreach($changeSet as $cs) {
            if ($cs->hasAttribute("id")) {
                $CSID = $cs->getAttribute('id');
            }
        }

        $this->assertEquals('Localhost/wiki12',$CSID);

        $listePatch = $dom->getElementsByTagName('patch');

        foreach($listePatch as $pays)
            $patch[] = $pays->firstChild->nodeValue;

        $this->assertTrue(count($patch)==2);
        $this->assertEquals('Patch:Berlin1',$patch[0]);
        $this->assertEquals('Patch:Paris0',substr($patch[1],0,-1));

        $pageName = "ChangeSet:localhost/wiki13";
        $content='ChangeSet:
changeSetID: [[changeSetID::localhost/wiki13]]
inPushFeed: [[inPushFeed::PushFeed:PushCity]]
previousChangeSet: [[previousChangeSet::ChangeSet:localhost/wiki12]]
 hasPatch: [[hasPatch::"Patch:Berlin2"]]';
        $this->p2pBot1->createPage($pageName, $content);

        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=changeSet&cspushName=PushCity&cschangeSet=ChangeSet:localhost/wiki12&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($cs);

        $changeSet = $dom->getElementsByTagName('changeSet');
        foreach($changeSet as $cs) {
            if ($cs->hasAttribute("id")) {
                $CSID = $cs->getAttribute('id');
            }
        }

        $this->assertEquals('Localhost/wiki13',$CSID);
        $listePatch = $dom->getElementsByTagName('patch');

        $patch = null;
        foreach($listePatch as $pays)
            $patch[] = $pays->firstChild->nodeValue;

        $this->assertTrue(count($patch)==1);
        $this->assertEquals('Patch:Berlin2',substr($patch[0],0,-1));

        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=changeSet&cspushName=PushCity&cschangeSet=ChangeSet:localhost/wiki13&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($cs);
        $changeSet = $dom->getElementsByTagName('changeSet');
        $CSID = null;
        foreach($changeSet as $cs) {
            if ($cs->hasAttribute("id")) {
                $CSID = $cs->getAttribute('id');
            }
        }

        $this->assertEquals(null, $CSID);

        $patch = null;
        $listePatch = $dom->getElementsByTagName('patch');
        foreach($listePatch as $pays)
            $patch[] = $pays->firstChild->nodeValue;
        $this->assertEquals(null, $patch);
    }

    protected function tearDown() {
       /* global $wgServerName, $wgScriptPath;
        $wgServerName = $this->tmpServerName;
        $wgScriptPath = $this->tmpScriptPath;*/
    }

}
?>
