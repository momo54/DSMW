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
 * @author marlene
 */


class p2pTest extends PHPUnit_Framework_TestCase {

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
    //create page on wiki1
        $pageName = "Paris";
        $content='content page Paris
[[Category:city]]';
        $this->p2pBot1->createPage($pageName,$content);

        //create push on wiki1
        $pushName = 'PushCity';
        $pushRequest = '[[Category:city]]';
        $res = $this->p2pBot1->createPush($pushName, $pushRequest);

        $res = $this->p2pBot1->push('PushFeed:'.$pushName);

        $pullName = 'pullCity';
        $res = $this->p2pBot2->createPull($pullName,'http://localhost/wiki1', $pushName);

        $res =  $this->p2pBot2->Pull($pullName);

        // assert cs from pushincluded
        assertCSFromPushIncluded($this->p2pBot1->bot->wikiServer, $pushName, $this->p2pBot2->bot->wikiServer, $pullName);

        // asssert patch is present

        // assert page paris exist
        assertPageExist($this->p2pBot1->bot->wikiServer, $pageName);

        // assert that wiki1/paris == wiki2/paris
        $contentWiki1 = getContentPage($this->p2pBot1->bot->wikiServer, 'Paris');
        $contentWiki2 = getContentPage($this->p2pBot2->bot->wikiServer, 'Paris');
        assertPageExist($this->p2pBot2->bot->wikiServer, 'Paris');
        $this->assertEquals($contentWiki1, $contentWiki2);
    }
}
?>
