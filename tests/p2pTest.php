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


    public function testbotappend() {
        $pageName = "Paris";
        $content='content page Paris
[[Category:city]]';
        $this->p2pBot1->createPage($pageName,$content);
        assertPageExist($this->p2pBot1->bot->wikiServer,$pageName);
        assertContentEquals($this->p2pBot1->bot->wikiServer,$pageName,$content);

        $this->p2pBot1->editPage($pageName,"toto");
        assertContentEquals($this->p2pBot1->bot->wikiServer,$pageName,$content."
toto");

    }

    public function testCreatePage() {
        $pageName = "Paris";
        $content='content page Paris
[[Category:city]]';
        $this->p2pBot1->createPage($pageName,$content);
        assertPageExist($this->p2pBot1->bot->wikiServer,$pageName);
        assertContentEquals($this->p2pBot1->bot->wikiServer,$pageName,$content);
    }

    public function testPatch() {
        $clock = 1;
        $pageName = "Nancy";
        $contentNancy='content page Nancy
toto titi
[[Category:city]]';

        $op[$pageName][]['insert'] = 'content page Nancy';
        $op[$pageName][]['insert'] = 'toto titi';
        $op[$pageName][]['insert'] = '[[Category:city]]';

        $this->p2pBot1->createPage($pageName,$contentNancy);

        $patchId1 = 'localhost/wiki1'.$clock;
        $clock += 1;
        assertPageExist($this->p2pBot1->bot->wikiServer, 'Patch:'.$patchId1);
        assertPatch($this->p2pBot1->bot->wikiServer,$patchId1,$clock,$pageName,$op,'None');

        $op = null;
        $clock += 3;
        $patchId2 = 'localhost/wiki1'.$clock;
        $this->p2pBot1->editPage($pageName,'toto');
        $clock += 1;
        $op[$pageName][]['insert'] = 'toto';
        $contentNancy .= '
toto' ;
        assertPageExist($this->p2pBot1->bot->wikiServer, 'Patch:'.$patchId2);
        assertPatch($this->p2pBot1->bot->wikiServer,$patchId2,$clock,$pageName,$op,$patchId1);

        $op = null;
        $clock += 1;
        $pageName = "Paris";
        $contentParis = 'content page Paris';
        $op[$pageName][]['insert'] = $contentParis;
        $this->p2pBot1->createPage($pageName, $content);

        $patchId = 'localhost/wiki1'.$clock;
        $clock += 1;
        assertPageExist($this->p2pBot1->bot->wikiServer, 'Patch:'.$patchId);
        assertPatch($this->p2pBot1->bot->wikiServer,$patchId1,$clock,$pageName,$op,'None');

        $clock += 1;
        $patchId3 = 'localhost/wiki1'.$clock;
        $op = null;
        $this->p2pBot1->editPage($pageName,'titi');
        $op[$pageName][]['insert'] = 'titi';
        $contentNancy .= '
titi';
        $clock += 1;
        assertPatch($this->p2pBot1->bot->wikiServer,$patchId3,$clock,$pageName,$op,$patchId2);

    }

    public function testCreatePush() {
        $this->p2pBot1->createPush('PushCity', '[[Category:city]]');
        assertPageExist($this->p2pBot1->bot->wikiServer,'PushFeed:PushCity');

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushCity]]','-3Fname/-3FhasSemanticQuery');
        $this->assertEquals('PushFeed:PushCity',$pushFound[0]);
        $this->assertEquals(convertRequest('[[Category:city]]'),substr($pushFound[1],0,-1));


        $this->p2pBot1->createPush('PushTest1', '[[Category:toto]]');
        assertPageExist($this->p2pBot1->bot->wikiServer,'PushFeed:PushTest1');

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushTest1]]','-3Fname/-3FhasSemanticQuery');
        $this->assertEquals('PushFeed:PushTest1',$pushFound[0]);
        $this->assertEquals(convertRequest('[[Category:toto]]'),substr($pushFound[1],0,-1));


        $this->p2pBot1->createPush('PushTest2', '[[Category:city]]');
        assertPageExist($this->p2pBot1->bot->wikiServer,'PushFeed:PushTest2');

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushTest2]]','-3Fname/-3FhasSemanticQuery');
        $this->assertEquals('PushFeed:PushTest2',$pushFound[0]);
        $this->assertEquals(convertRequest('[[Category:city]]'),substr($pushFound[1],0,-1));

    }

    public function testPush() {
    //create page on wiki1
        $pageName = "Nancy";
        $content='content page Nancy
[[Category:city]]';
        $this->p2pBot1->createPage($pageName,$content);

        //create push on wiki1
        $pushName = 'PushCity';
        $pushRequest = '[[Category:city]]';
        $res = $this->p2pBot1->createPush($pushName, $pushRequest);


        $res = $this->p2pBot1->push('PushFeed:'.$pushName);
        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:'.$pushName.']]','-3FhasPushHead');

        $CSIDFound = substr($pushFound[0],0,-1);
        $this->assertNotNull($CSIDFound);
        assertPageExist($this->p2pBot1->bot->wikiServer,$CSIDFound);

        $CSName = strtolower(substr($CSIDFound, strlen('ChangeSet:')));
        $CSFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[changeSetID::'.$CSName.']]','-3FchangeSetID/-3FinPushFeed/-3FpreviousChangeSet');
        $this->assertEquals(strtolower('PushFeed:'.$pushName),strtolower($CSFound[1]));
        $this->assertEquals('None',substr($CSFound[2],0,-1));

        //patchId1 = getLastPatchId(Nancy);
        //jusqu'à previouspatch = none

        //edit page
        $this->p2pBot1->editPage($pageName,'toto');
        $res = $this->p2pBot1->push('PushFeed:'.$pushName);

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:'.$pushName.']]','-3FhasPushHead');

        $previousCS = $CSIDFound;
        $CSIDFound = substr($pushFound[0],0,-1);
        $this->assertNotNull($CSIDFound);
        assertPageExist($this->p2pBot1->bot->wikiServer,$CSIDFound2);

        $CSName = strtolower(substr($CSIDFound, strlen('ChangeSet:')));
        $CSFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[changeSetID::'.$CSName.']]','-3FchangeSetID/-3FinPushFeed/-3FpreviousChangeSet');
        $this->assertEquals(strtolower('PushFeed:'.$pushName),strtolower($CSFound[1]));
        $this->assertEquals($previousCS,substr($CSFound[2],0,-1));

        $contentCS = getContentPage($this->p2pBot1->bot->wikiServer,$CSIDFound);
        //patchId2 = getlastPatchId(Nancy);
        //jusqu'à previouspatch = patchId1

        $this->p2pBot1->push('PushFeed:'.$pushName);
        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:'.$pushName.']]','-3FhasPushHead');
        $this->assertEquals($CSIDFound,substr($pushFound[0],0,-1));
        assertContentEquals($this->p2pBot1->bot->wikiServer,$CSIDFound, $contentCS);

        $this->p2pBot1->createPage('Paris','content page Paris [[Category:city]]');
        $this->p2pBot1->createPage('toto','toto');

        $this->p2pBot1->push('PushFeed:'.$pushName);

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:'.$pushName.']]','-3FhasPushHead');

        $previousCS = $CSIDFound;
        $CSIDFound = substr($pushFound[0],0,-1);
        $this->assertNotNull($CSIDFound);
        assertPageExist($this->p2pBot1->bot->wikiServer,$CSIDFound);

        $CSName = strtolower(substr($CSIDFound, strlen('ChangeSet:')));
        $CSFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[changeSetID::'.$CSName.']]','-3FchangeSetID/-3FinPushFeed/-3FpreviousChangeSet');
        $this->assertEquals(strtolower('PushFeed:'.$pushName),strtolower($CSFound[1]));
        $this->assertEquals($previousCS,substr($CSFound[2],0,-1));

    //patchId3 = getlastPatchId(Nancy);
    //jusqu'à previouspatch = patchId3
    //patchId4 = getlastPatchId(Nancy);
    //jusqu'à previouspatch = none
    }


    public function testCreatePull() {
        $pullName = 'pullCity';
        $pushFeed = 'http://localhost/wiki1/PushFeed:'.$pushName;
        $res = $this->p2pBot2->createPull($pullName, $pushFeed);
        assertPageExist($this->p2pBot2->bot->wikiServer,'PullFeed:'.$pullName);

        $pullFound = getSemanticRequest($this->p2pBot2->bot->wikiServer,'[[name::PullFeed:'.$pullName.']][[relatedPushFeed::'.$pushFeed.']]','-3FrelatedPushFeed/-3FhasPullHead');

        $this->assertEquals(strtolower($pushFeed),strtolower($pullFound[0]));
        $this->assertEquals('',substr($pullFound[1],0,-1));
    }


    public function testGetChangeSet(){
        
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testPull() {
        $pushFeed = 'http://localhsot/wiki1/PushFeed:PushCity';
        $this->p2pBot2->createPull('PullCity', $pushFeed);

        $this->p2pBot2->pull('PullCity');
    }
    /*public function testPull(){
        
    }*/

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

        $content = getContentPage($this->p2pBot1->bot->wikiServer, 'PullFeed:'.$pushName);
        $res = $this->p2pBot1->push('PushFeed:'.$pushName);;
        assertContentEquals($this->p2pBot1->bot->wikiServer, 'PullFeed:'.$pushName, $content);


        $pullName = 'pullCity';
        $pushFeed = 'http://localhost/wiki1/PushFeed:'.$pushName;
        $res = $this->p2pBot2->createPull($pullName, $pushFeed);
        // assert nouvelle page crée avec le contenu etc

        $res =  $this->p2pBot2->Pull($pullName);
        assertPullUpdated($this->p2pBot2->bot->wikiServer,$this->p2pBot1->bot->wikiServer,'PushFeed:'.$pushName,$pullName);
        // assert cs from pushincluded
        // assert page paris exist
        // asssert patch is present
        // assert that wiki1/paris == wiki2/paris

        $content = getContentPage($this->p2pBot2->wikiServer, 'PullFeed:pullCity');
        $res =  $this->p2pBot2->Pull('pullCity');
        assertContentEquals($this->p2pBot2->wikiServer, 'PullFeed:pullCity', $content);

        $this->p2pBot2->append($pageName,'toto');
        assertPageUpdated($this->p2pBot2->bot->wikiServer,$pageName,'toto');

        $res = $this->p2pBot2->createPush($pushName, $pushRequest);
        assertPageExist($this->p2pBot2->bot->wikiServer,'PushFeed:'.$pushName);
        assertPushCreated($this->p2pBot2->bot->wikiServer, $pushName, $pushRequest);

        $res = $this->p2pBot2->Push('pushCity');
        assertPushUpdated($this->p2pBot2->bot->wikiServer,$pushName,$pushRequest,'none');

        $res = $this->p2pBot1->createPull('pullCity', 'http://localhost/wiki2/push:pushcity');
        assertPageExist($this->p2pBot1->bot->wikiServer,'PushFeed:pullCity');
        assertPullCreated($this->p2pBot1->bot->wikiServer, 'pullCity', $pushRequest);

        $res=$this->p2pBot1->Pull('pullCity');
    // assert...
    // assert that wiki1/paris == wiki2/paris


    }
}
?>
