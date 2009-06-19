<?php

define( 'MEDIAWIKI', true );
require_once 'p2pBot.php';
require_once 'BasicBot.php';
include_once 'p2pAssert.php';
require_once '../../..//includes/GlobalFunctions.php';

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
        wfDebugLog('p2p','start p2p Test');
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

    /*public function test3Wiki() {
        $res = $this->p2pBot1->createPage('wiki1','tata','toto 1');
        $this->assertTrue($res);

        $res = $this->p2pBot2->createPage('wiki2','tata','toto 2');
        $this->assertTrue($res);

        $res = $this->p2pBot3->createPage('wiki3','tata','toto 3');
        $this->assertTrue($res);

        /*----------------- test push ---------------------- 

        //create page on wiki1
        $source = "Paris";
        $summary='sum';
        $content='content page Paris  [[Category:city]]';
        $res = $this->p2pBot1->createPage($source,$content);
        $this->assertTrue($res);

        $source = "Berlin";
        $summary='sum';
        $content='content page Berlin  [[Category:city]]';
        $res = $this->p2pBot1->createPage($source,$content);
        $this->assertTrue($res);

        //create push on wiki1
        $name = 'pushCity';
        $request = '[[Category:city]]';
        $res = $this->p2pBot1->createPush($name, $request);
        $this->assertTrue($res);

        $res = $this->p2pBot1->push('PushFeed:'.$name);
        $this->assertTrue($res);

    }*/

    public function testSimple1() {
    //create page on wiki1
        $pageName = "Paris";
        $content='content page Paris  [[Category:city]]';
        $res = $this->p2pBot1->createPage($pageName,$content);
        assertPageExist($this->p2pBot1->bot->wikiServer,$pageName);
        assertContentEquals($this->p2pBot1->bot->wikiServer,$pageName,$content);

        //create push on wiki1
        $pushName = 'PushCity';
        $pushRequest = '[[Category:city]]';
        $res = $this->p2pBot1->createPush($pushName, $pushRequest);
        assertPageExist($this->p2pBot1->bot->wikiServer,'PushFeed:'.$pushName);
        assertPushCreated($this->p2pBot1->bot->wikiServer,$pushName,$pushRequest);

        
        /*$res = $this->p2pBot1->createPush('pushCity', '[[Category:city]]');
        // assert failed
         */
        
        $res = $this->p2pBot1->push('PushFeed:'.$pushName);
        // test 1 cs dans la page, avec une opération insert qui contient category city
        assertPushUpdated($this->p2pBot1->bot->wikiServer,$pushName,$pushRequest);

        /*$res = $this->p2pBot1->push($name);
        // 2ième push nothing change...
        $this->assertTrue($res);*/


        $pullName = 'pullCity';
        $res = $this->p2pBot2->createPull($pullName, 'http://localhost/wiki1/push:pushcity');
        assertPageExist($this->p2pBot2->bot->wikiServer,'PullFeed:'.$pullName);
        assertPullCreated($this->p2pBot2->bot->wikiServer);
        // assert nouvelle page crée avec le contenu etc

        $res =  $this->p2pBot2->Pull($pullName);
        assertPullUpdated($this->p2pBot2->bot->wikiServer,$pullName);
        // assert cs from pushincluded
        // assert page paris exist
        // asssert patch is present
        // assert that wiki1/paris == wiki2/paris

        $res =  $this->p2pBot2->Pull('pullCity');
        // assert nothing done...

        $this->p2pBot2->append('Paris',"toto");
        // assert ...

        $res = $this->p2pBot2->createPush('pushCity', '[[Category:city]]');
        // assert

        $res = $this->p2pBot2->Push('pushCity');
        // asssert ..
        //

        $res = $this->p2pBot1->createPull('pullCity', 'http://localhost/wiki2/push:pushcity');
        // assert...

        $res=$this->p2pBot1->Pull('pullCity');
    // assert...
    // assert that wiki1/paris == wiki2/paris





    }


   /* public function testGet(){

    }*/
}
?>
