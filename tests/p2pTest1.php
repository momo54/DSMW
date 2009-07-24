<?php

define( 'MEDIAWIKI', true );
require_once 'p2pBot.php';
require_once 'BasicBot.php';
include_once 'p2pAssert.php';
require_once '../../..//includes/GlobalFunctions.php';
require_once '../patch/Patch.php';
require_once '../files/utils.php';

$wgDebugLogGroups  = array(
    'p2p'=>"/tmp/p2p.log",
);

/**
 * Description of p2pTest
 *
 * @author hantz
 */


class p2pTest1 extends PHPUnit_Framework_TestCase {

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
        exec('./initWikiTest.sh  ./createDBTest.sql ./dump.sql');
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

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
    // exec('./deleteTest.sh');
    }


    public function testSimple1() {
        $pageName = "Lambach";
        $content='content page Lambach
[[Category:city1]]';
        $this->assertTrue($this->p2pBot1->createPage($pageName,$content),
            'Failed to create page '.$pageName.' ('.$this->p2pBot1->bot->results.')');

        //create push on wiki1
        $pushName = 'PushCity10';
        $pushRequest = '[[Category:city1]]';
        $this->assertTrue($this->p2pBot1->createPush($pushName, $pushRequest),
            'Failed to create push : '.$pushName.' ('.$this->p2pBot1->bot->results.')');

        //push on wiki1
        $this->assertTrue($this->p2pBot1->push('PushFeed:'.$pushName),
            'failed to push '.$pushName.' ('.$this->p2pBot2->bot->results.')');

        //create pull on wiki2
        $pullName = 'PullCity';
        $this->assertTrue($this->p2pBot2->createPull($pullName,'http://localhost/wiki1', $pushName),
            'failed to create pull '.$pullCity.' ('.$this->p2pBot2->bot->results.')');

        //pull
        $this->assertTrue($this->p2pBot2->Pull('PullFeed:'.$pullName),
            'failed to pull '.$pullName.' ('.$this->p2pBot2->bot->results.')');

        // assert page paris exist
        assertPageExist($this->p2pBot1->bot->wikiServer, $pageName);

        // assert that wiki1/paris == wiki2/paris
        assertContentEquals($this->p2pBot1->bot->wikiServer, $this->p2pBot2->bot->wikiServer, 'Lambach');

        //assert that there is the same patch on the 2 wikis
        $PatchonWiki1 = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]]', '-3FpatchID');
        $PatchonWiki2 = getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[Patch:+]]', '-3FpatchID');
        $PatchonWiki1 = $this->arraytolower($PatchonWiki1);
        $PatchonWiki2 = $this->arraytolower($PatchonWiki2);
        $this->assertEquals($PatchonWiki1,$PatchonWiki2);

        //assert that there is the same changeSet on the 2 wikis
        $CSonWiki1 = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[ChangeSet:+]]', '-3FchangeSetID');
        $CSonWiki2 = getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[ChangeSet:+]]', '-3FchangeSetID');
        $this->assertEquals($CSonWiki1,$CSonWiki2);
    }

/*    function testSimple2() {
        $this->testSimple1();

        $countCSonWiki1 = count(getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]]', '-3FpatchID'));

        //create push on wiki2
        $pushName = 'PushCity10';
        $pushRequest = '[[Category:city1]]';
        $this->assertTrue($this->p2pBot2->createPush($pushName, $pushRequest),
            'Failed to create push : '.$pushName.' ('.$this->p2pBot2->bot->results.')');

        $this->assertTrue($this->p2pBot2->push('PushFeed:'.$pushName),
            'failed to push '.$pushName.' ('.$this->p2pBot2->bot->results.')');

        // assert that wiki1/Lambach == wiki2/Lambach
        assertContentEquals($this->p2pBot1->bot->wikiServer, $this->p2pBot2->bot->wikiServer, 'Lambach');

        //create pull on wiki1
        $pullName = 'pullCity';
        $this->assertTrue($this->p2pBot1->createPull($pullName,'http://localhost/wiki2', $pushName),
            'failed to create pull '.$pullCity.' ('.$this->p2pBot1->bot->results.')');

        //pull
        $this->assertTrue($this->p2pBot1->Pull('PullFeed:'.$pullName),
            'failed to pull '.$pullName.' ('.$this->p2pBot1->bot->results.')');

        $countCS = count(getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]]', '-3FpatchID'));
        //assert no patch created
        $this->assertTrue($countCSonWiki1==$countCS);

        // assert that wiki1/Lambach == wiki2/Lambach
        $contentWiki1 = getContentPage($this->p2pBot1->bot->wikiServer, 'Lambach');
        $contentWiki2 = getContentPage($this->p2pBot2->bot->wikiServer, 'Lambach');
        assertPageExist($this->p2pBot2->bot->wikiServer, 'Lambach');
        $this->assertEquals($contentWiki1, $contentWiki2,
            'Failed content page Lambach');

        //assert that there is the same patch on the 2 wikis
        $PatchonWiki1 = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]]', '-3FpatchID');
        $PatchonWiki2 = getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[Patch:+]]', '-3FpatchID');
        $PatchonWiki1 = $this->arraytolower($PatchonWiki1);
        $PatchonWiki2 = $this->arraytolower($PatchonWiki2);
        $this->assertEquals($PatchonWiki1,$PatchonWiki2);

        //assert that there is the same changeSet on the 2 wikis
        $CSonWiki1 = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[ChangeSet:+]]', '-3FchangeSetID');
        $CSonWiki2 = getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[ChangeSet:+]]', '-3FchangeSetID');
        $this->assertEquals($CSonWiki1,$CSonWiki2);
    }

    function testSimple3() {
        $this->testSimple2();

        $countCSonWiki1 = count(getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]]', '-3FpatchID'));
        $countCSonWiki2 = count(getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[Patch:+]]', '-3FpatchID'));

        $pushName = 'PushCity10';
        $pullName = 'pullCity';
        $this->assertTrue($this->p2pBot1->push('PushFeed:'.$pushName),
            'failed to push '.$pushName.' ('.$this->p2pBot1->bot->results.')');

        $this->assertTrue($this->p2pBot2->Pull('PullFeed:'.$pullName),
            'failed to pull '.$pullName.' ('.$this->p2pBot2->bot->results.')');

        $countCS = count(getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[Patch:+]]', '-3FpatchID'));
        //assert no patch created
        $this->assertTrue($countCSonWiki2==$countCS);

        // assert that wiki1/Lambach == wiki2/Lambach
        $contentWiki1 = getContentPage($this->p2pBot1->bot->wikiServer, 'Lambach');
        $contentWiki2 = getContentPage($this->p2pBot2->bot->wikiServer, 'Lambach');
        assertPageExist($this->p2pBot2->bot->wikiServer, 'Lambach');
        $this->assertEquals($contentWiki1, $contentWiki2,
            'Failed content page Lambach');

        //assert that there is the same changeSet on the 2 wikis
        $CSonWiki1 = count(getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[ChangeSet:+]]', '-3FchangeSetID'));
        $CSonWiki2 = count(getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[ChangeSet:+]]', '-3FchangeSetID'));
        $this->assertEquals($CSonWiki1,$CSonWiki2);

        //assert that there is the same patch on the 2 wikis
        $PatchonWiki1 = count(getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]]', '-3FpatchID'));
        $PatchonWiki2 = count(getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[Patch:+]]', '-3FpatchID'));
        $this->assertEquals($PatchonWiki1,$PatchonWiki2);
    }
*/
    function arraytolower($array) {
      foreach($array as $key => $value) {
          $array[$key] = strtolower($value);
      }
    return $array;
  }
}
?>
