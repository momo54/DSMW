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


   /* public function testbotappend() {
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

        $pageName = "Nancy";
        $content='content page Nancy
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
        $op['Paris'][]['insert'] = 'content page Paris';
        $this->p2pBot1->createPage('Paris', 'content page Paris');

        $patchId = 'localhost/wiki1'.$clock;
        $clock += 1;
        assertPageExist($this->p2pBot1->bot->wikiServer, 'Patch:'.$patchId);
        assertPatch($this->p2pBot1->bot->wikiServer,$patchId,$clock,'Paris',$op,'None');

        $clock += 1;
        $patchId3 = 'localhost/wiki1'.$clock;
        $op = null;
        $this->p2pBot1->editPage($pageName,'titi');
        $op[$pageName][]['insert'] = 'titi';
        $clock += 1;
        assertPatch($this->p2pBot1->bot->wikiServer,$patchId3,$clock,$pageName,$op,$patchId2);
    }

    public function testCreatePush() {
        $this->p2pBot1->createPush('PushCity', '[[Category:city]]');
        assertPageExist($this->p2pBot1->bot->wikiServer,'PushFeed:PushCity');

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushCity]]','-3Fname/-3FhasSemanticQuery');
        $this->assertEquals('PushFeed:PushCity',$pushFound[0]);
        $this->assertEquals(utils::encodeRequest('[[Category:city]]'),substr($pushFound[1],0,-1));


        $this->p2pBot1->createPush('PushTest1', '[[Category:toto]]');
        assertPageExist($this->p2pBot1->bot->wikiServer,'PushFeed:PushTest1');

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushTest1]]','-3Fname/-3FhasSemanticQuery');
        $this->assertEquals('PushFeed:PushTest1',$pushFound[0]);
        $this->assertEquals(utils::encodeRequest('[[Category:toto]]'),substr($pushFound[1],0,-1));


        $this->p2pBot1->createPush('PushTest2', '[[Category:city]]');
        assertPageExist($this->p2pBot1->bot->wikiServer,'PushFeed:PushTest2');

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushTest2]]','-3Fname/-3FhasSemanticQuery');
        $this->assertEquals('PushFeed:PushTest2',$pushFound[0]);
        $this->assertEquals(utils::encodeRequest('[[Category:city]]'),substr($pushFound[1],0,-1));

    }

    public function testPush() {
    //create push on wiki1
        $pushName = 'PushCity';
        $pushRequest = '[[Category:city]]';
        $res = $this->p2pBot1->createPush($pushName, $pushRequest);

        $res = $this->p2pBot1->push('PushFeed:'.$pushName);
        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:'.$pushName.']]','-3FhasPushHead');
        $this->assertEquals('',substr($pushFound[0],0,-1));

        $res = $this->p2pBot1->createPage('Nancy','content nancy [[Category:city]]');
        $res = $this->p2pBot1->createPage('Paris','content Paris [[Category:city]]');
        $res = $this->p2pBot1->push('PushFeed:'.$pushName);

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:'.$pushName.']]','-3FhasPushHead');

        $CSIDFound = substr($pushFound[0],0,-1);
        assertPageExist($this->p2pBot1->bot->wikiServer,$CSIDFound);

        $CSName = strtolower(substr($CSIDFound, strlen('ChangeSet:')));
        $CSFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[changeSetID::'.$CSName.']]','-3FchangeSetID/-3FinPushFeed/-3FpreviousChangeSet/-3FhasPatch');
        $this->assertEquals(strtolower('PushFeed:'.$pushName),strtolower($CSFound[1]));
        $this->assertEquals('None',$CSFound[2]);

        $patch = new Patch('', '', '', '');
        $lastPatchNancy = $patch->getLastPatchId('Nancy', $this->p2pBot1->bot->wikiServer);
        $lastPatchParis = $patch->getLastPatchId('Paris', $this->p2pBot1->bot->wikiServer);
        $patchCS = split(',',substr($CSFound[3],0,-1));
        $this->assertTrue(count($patchCS)==2);

        $assert1 = strtolower($lastPatchNancy) == strtolower($patchCS[0]) || strtolower($lastPatchNancy) == strtolower($patchCS[1]);
        $assert2 = strtolower($lastPatchParis) == strtolower($patchCS[0]) || strtolower($lastPatchParis) == strtolower($patchCS[1]);
        $this->assertTrue($assert1 && $assert2);


        //edit page
        $this->p2pBot1->editPage('Nancy','toto');
        $this->p2pBot1->editPage('Nancy','titi');
        $res = $this->p2pBot1->push('PushFeed:'.$pushName);

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:'.$pushName.']]','-3FhasPushHead');

        $previousCS = $CSIDFound;
        $CSIDFound = substr($pushFound[0],0,-1);
        $this->assertNotNull($CSIDFound);
        assertPageExist($this->p2pBot1->bot->wikiServer,$CSIDFound2);

        $CSName = strtolower(substr($CSIDFound, strlen('ChangeSet:')));
        $CSFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[changeSetID::'.$CSName.']]','-3FchangeSetID/-3FinPushFeed/-3FpreviousChangeSet/-3FhasPatch');
        $this->assertEquals(strtolower('PushFeed:'.$pushName),strtolower($CSFound[1]));
        $this->assertEquals($previousCS,$CSFound[2]);

        $contentCS = getContentPage($this->p2pBot1->bot->wikiServer,$CSIDFound);
        $previousLastPatchNancy = $lastPatchNancy;
        $lastPatchNancy = $patch->getLastPatchId('Nancy',$this->p2pBot1->bot->wikiServer);
        $patchCS = split(',',substr($CSFound[3],0,-1));
        $this->assertTrue(count($patchCS)==2);
        $assert1 = strtolower($lastPatchNancy) == strtolower($patchCS[0]) || strtolower($lastPatchNancy) == strtolower($patchCS[1]);
        $patchId = substr($lastPatchNancy,strlen('patch:'));
        $prevPatch = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[patchID::'.$patchId.']]','-3Fprevious');
        $prevPatch = substr($prevPatch[0],0,-1);
        $assert2 = strtolower($prevPatch) == strtolower($patchCS[0]) || strtolower($prevPatch) == strtolower($patchCS[1]);
        $patchId = substr($prevPatch,strlen('patch:'));
        $prevPatch = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[patchID::'.$patchId.']]','-3Fprevious');
        $prevPatch = substr($prevPatch[0],0,-1);
        $this->assertEquals(strtolower($previousLastPatchNancy),strtolower($prevPatch));

        //patchId2 = getlastPatchId(Nancy);
        //jusqu'à previouspatch = patchId1

        $this->p2pBot1->push('PushFeed:'.$pushName);
        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:'.$pushName.']]','-3FhasPushHead');
        $this->assertEquals($CSIDFound,substr($pushFound[0],0,-1));
        assertContentEquals($this->p2pBot1->bot->wikiServer,$CSIDFound, $contentCS);

        /*$this->p2pBot1->createPage('Paris','content page Paris [[Category:city]]');
        $this->p2pBot1->createPage('toto','toto');

        $this->p2pBot1->push('PushFeed:'.$pushName);

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:'.$pushName.']]','-3FhasPushHead');
        $this->assertEquals($CSIDFound,substr($pushFound[0],0,-1));
        assertContentEquals($this->p2pBot1->bot->wikiServer,$CSIDFound, $contentCS);
    }
*/

    public function testCreatePull() {
        $pullName = 'pullCity';
        $pushFeed = 'http://localhost/wiki1/PushFeed:'.$pushName;
        $res = $this->p2pBot2->createPull($pullName, $pushFeed);
        assertPageExist($this->p2pBot2->bot->wikiServer,'PullFeed:'.$pullName);

        $pullFound = getSemanticRequest($this->p2pBot2->bot->wikiServer,'[[name::PullFeed:'.$pullName.']][[relatedPushFeed::'.$pushFeed.']]','-3FrelatedPushFeed/-3FhasPullHead');

        $this->assertEquals(strtolower($pushFeed),strtolower($pullFound[0]));
        $this->assertEquals('',substr($pullFound[1],0,-1));
    }


    public function testGetChangeSet() {
        $pageName = "ChangeSet:localhost/wiki12";
        $content='ChangeSet:
changeSetID: [[changeSetID::localhost/wiki12]]
inPushFeed: [[inPushFeed::PushFeed:PushCity]]
previousChangetSet: [[previousChangetSet::none]]
 hasPatch: [[hasPatch::"Patch:Berlin1"]] hasPatch: [[hasPatch::"Patch:Paris0"]]';
        $this->p2pBot1->createPage($pageName, $content);

        $pageName = 'PushFeed:PushCity';
        $content = 'PushFeed:
Name: [[name::CityPush2]]
hasSemanticQuery: [[hasSemanticQuery::-5B-5BCategory:city-5D-5D]]
Pages concerned:
{{#ask: [[Category:city]]}} hasPushHead: [[hasPushHead::ChangeSet:localhost/mediawiki12]]';
        $this->p2pBot1->createPage($pageName,$content);

        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'api.php?action=query&pushName=PushCity&changeSet=none');

        $CSPatch = split(',',substr($cs[0],0,-1));

        $this->assertTrue(count($CSPath)==2);
        $this->assertEquals('Patch:Berlin1',$CSPatch[0]);
        $this->assertEquals('Patch:Paris0',$CSPatch[1]);

        $pageName = "ChangeSet:localhost/wiki13";
        $content='ChangeSet:
changeSetID: [[changeSetID::localhost/wiki13]]
inPushFeed: [[inPushFeed::PushFeed:PushCity]]
previousChangetSet: [[previousChangetSet::localhost/wiki12]]
 hasPatch: [[hasPatch::"Patch:Berlin2"]]';
        $this->p2pBot1->createPage($pageName, $content);

        $pageName = 'PushFeed:PushCity';
        $content = 'PushFeed:
Name: [[name::CityPush2]]
hasSemanticQuery: [[hasSemanticQuery::-5B-5BCategory:city-5D-5D]]
Pages concerned:
{{#ask: [[Category:city]]}} hasPushHead: [[hasPushHead::ChangeSet:localhost/mediawiki12]]';
        $this->p2pBot1->createPage($pageName,$content);

        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'api.php?action=getChangeSet&pushName=PushCity&changeSet=none');

        $CSPatch = split(',',substr($cs[0],0,-1));

        $this->assertTrue(count($CSPath)==1);
        $this->assertEquals('Patch:Berlin2',$CSPatch[0]);
    }


    /*public function testIntegratedPatch() {
        $pageName = "Patch:localhost/wiki11";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1901]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::none]]';
        $this->p2pBot1->createPage($pageName, $content);

        $pageName = "Patch:localhost/wiki12";
        $Patchcontent='Patch: patchID: [[patchID::localhost/wiki1901]]
 onPage: [[onPage::cooper]]  hasOperation: [[hasOperation::localhost/wiki1902;
Insert;( 5053487913627490220,42601d9c1af38da968d697efde65a473 ) 901;content]]
previous: [[previous::none]]';
        $this->p2pBot1->createPage($pageName, $content);

        $pageName = "ChangeSet:localhost/wiki12";
        $content='ChangeSet:
changeSetID: [[changeSetID::localhost/mediawiki12]]
inPushFeed: [[inPushFeed::PushFeed:PushCity]]
previousChangetSet: [[previousChangetSet::none]]
 hasPatch: [[hasPatch::"Patch:localhost/wiki11"]] hasPatch: [[hasPatch::"Patch:localhost/wiki12"]]';
        $this->p2pBot1->createPage($pageName, $content);

        Path::integrate($pageName);

    }*/

    /*public function testPull() {
        $pushFeed = 'http://localhsot/wiki1/PushFeed:PushCity';
        $this->p2pBot2->createPull('PullCity', $pushFeed);

        $this->p2pBot2->pull('PullCity');
    }*/
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

        $pullName = 'pullCity';
        $pushFeed = 'http://localhost/wiki1/PushFeed:'.$pushName;
        $res = $this->p2pBot2->createPull($pullName, $pushFeed);

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
