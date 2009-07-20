<?php

define( 'MEDIAWIKI', true );
require_once '../p2pBot.php';
require_once '../BasicBot.php';
require_once '../../../../includes/GlobalFunctions.php';
require_once '../../patch/Patch.php';
require_once '../../files/utils.php';
include_once '../p2pAssert.php';


/**
 * Description of pushPullTest
 *
 * @author hantz
 */
class pushTest extends PHPUnit_Framework_TestCase {

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

        exec('../initWikiTest.sh ../createDBTest.sql ../dump.sql');
        exec('rm ../cache/*');
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = 'http://localhost/wiki1';
        $this->p2pBot1 = new p2pBot($basicbot1);

        $basicbot2 = new BasicBot();
        $basicbot2->wikiServer = 'http://localhost/wiki2';
        $this->p2pBot2 = new p2pBot($basicbot2);

        $basicbot3 = new BasicBot();
        $basicbot3->wikiServer = 'http://localhost/wiki3';
        $this->p2pBot3 = new p2pBot($basicbot3);

        $this->p2pBot1->bot->wikiConnect();
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

    public function testPatch() {
    // test patch after creating page
        $pageName = "Pouxeux";
        $contentPage='content page Pouxeux
toto titi
[[Category:city]]';

        $this->assertTrue($this->p2pBot1->createPage($pageName,$contentPage),'Create page Pouxeux failed : '.$this->p2pBot1->bot->results);

        $patch = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'.$pageName.']]', '-3FpatchID');

        //assert that one patch was created
        $this->assertTrue(count($patch)==1);

        // test patch after editing page
        $this->assertTrue($this->p2pBot1->editPage($pageName,'toto'),
            'failed to edit page '.$pageName.' : '.$this->p2pBot1->bot->results);

        $patch = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'.$pageName.']]', '-3FpatchID');

        //assert that one patch was created
        $this->assertTrue(count($patch)==2);
    }

    public function testCreatePush() {
        $this->assertTrue($this->p2pBot1->createPush('PushCity11', '[[Category:titi]]'),
            'failed to create push PushCity : ('.$this->p2pBot1->bot->results.')');

        assertPageExist($this->p2pBot1->bot->wikiServer,'PushFeed:PushCity11');

        //assert that patch contains is ok
        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushCity11]]','-3Fname/-3FhasSemanticQuery');

        $this->assertEquals('PushFeed:PushCity11',$pushFound[0],
            'Create push PushCity error, push name must be PushFeed:PushCity but '.$pushFound[0].' was found');

        $this->assertEquals(utils::encodeRequest('[[Category:titi]]'),substr($pushFound[1],0,-1),
            'Create push PushCity error, semantic request must be [[Category:city]] but '.
            utils::decodeRequest(substr($pushFound[1],0,-1)).' was found');
    }

    public function testPushWithNoChangeSet() {
    //create pushFeed
        $this->testCreatePush();

        $CS = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[ChangeSet:+]]','');
        $res = count($CS);
        // push without changeSet creationg
        $this->assertTrue($this->p2pBot1->push('PushFeed:PushCity11'),
            'failed to push PushCity : ('.$this->p2pBot1->bot->results.')');

        $CS = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[ChangeSet:+]]','');
        $res1 = count($CS);

        //assert that no changeSet was created
        $this->assertTrue($res == $res1,
            'failed on push, no changeSet must be created but '.$res1-$res.' changeSet were found');

        //assert that pushHead attribute is always null
        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushCity11]]','-3FhasPushHead');
        $this->assertEquals('',substr($pushFound[0],0,-1),
            'push PushCity error, pushHead must be null but '.$pushFound[0].' was found');
    }

    public function testPushWithChangeSet1() {
        $this->testCreatePush();

        $this->assertTrue($this->p2pBot1->createPage('Arches',"content arches [[Category:titi]]",
            'failed to create page Arches ('.$this->p2pBot1->bot->results.')'));
        $this->assertTrue($this->p2pBot1->createPage('Paris11','content Paris11 [[Category:titi]]',
            'failed to create page Paris11 ('.$this->p2pBot1->bot->results.')'));

        $this->assertTrue($this->p2pBot1->push('PushFeed:PushCity11'),
            'failed to push '.$pushName.' ('.$this->p2pBot1->bot->results.')');

        //assert that pushHead attribute is not null and the changeSet page exist
        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushCity11]]','-3FhasPushHead');
        $this->assertNotEquals('',$pushFound[0]);

        $CSIDFound = substr($pushFound[0],0,-1);
        assertPageExist($this->p2pBot1->bot->wikiServer,$CSIDFound);

        $CSName = strtolower(substr($CSIDFound, strlen('ChangeSet:')));

        //assert the changeSet created is ok
        $CSFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[changeSetID::'.$CSName.']]','-3FchangeSetID/-3FinPushFeed/-3FpreviousChangeSet/-3FhasPatch');
        //assert inPushFeed
        $this->assertEquals(strtolower('PushFeed:PushCity11'),strtolower($CSFound[1]),
            'failed to push PushCity11, ChangeSet push name must be PushFeed:'.$pushName.' but '.$CSFound[1].' was found');
        //assert previousChangeSet
        $this->assertEquals('none',strtolower($CSFound[2]),
            'failed to push PushCity11, ChangeSet previous must be None but '.$CSFound[2].' was found');

        $patchCS = split(',',substr($CSFound[3],0,-1));
        $this->assertTrue(count($patchCS)==2,
            'failed to push PushCity11, ChangeSet must contains 2 patchs but '.count($patchCS).' patchs were found');

        //assert patchs contains in the changeSet is ok
        $lastPatchNancy = utils::getLastPatchId('Arches', $this->p2pBot1->bot->wikiServer);
        $lastPatchParis = utils::getLastPatchId('Paris11', $this->p2pBot1->bot->wikiServer);
        $assert1 = strtolower($lastPatchNancy) == strtolower($patchCS[0]) || strtolower($lastPatchNancy) == strtolower($patchCS[1]);
        $assert2 = strtolower($lastPatchParis) == strtolower($patchCS[0]) || strtolower($lastPatchParis) == strtolower($patchCS[1]);
        $this->assertTrue($assert1 && $assert2,
            'failed to push '.$pushName.', wrong patch in changeSet');
    }

    public function testPushWithChangeSet2() {
        $this->testPushWithChangeSet1();

        $allCS = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[ChangeSet:+]][[inPushFeed::PushFeed:PushCity11]]', '-3FchangeSetID');
        $previousCS = substr($allCS[0],0,-1);
        $this->p2pBot1->editPage(Arches, 'content added on the page');

        $this->p2pBot1->push('PushFeed:PushCity11');

        $pushFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[name::PushFeed:PushCity11]]','-3FhasPushHead');
        $CSIDFound = substr($pushFound[0],0,-1);

        $this->assertNotEquals($previousCS, $CSIDFound);
        $CSName = strtolower(substr($CSIDFound, strlen('ChangeSet:')));

        //assert that previousChangeSet is ok
        $CSFound = getSemanticRequest($this->p2pBot1->bot->wikiServer,'[[changeSetID::'.$CSName.']]','-3FpreviousChangeSet');
        $this->assertEquals('changeset:'.strtolower($previousCS), strtolower(substr($CSFound[0],0,-1)));
    }

    public function testMultiPush() {
        $this->assertTrue($this->p2pBot1->createPage('Toto12', '[[Category:toto]]'));
        $this->assertTrue($this->p2pBot1->createPage('Titi12', '[[Category:titi]]'));
        $this->assertTrue($this->p2pBot1->createPage('Tata12', '[[Category:tata]]'));

        $this->assertTrue($this->p2pBot1->createPush('PushToto12', '[[Category:toto]]'));
        $this->assertTrue($this->p2pBot1->createPush('PushTiti12', '[[Category:titi]]'));
        $this->assertTrue($this->p2pBot1->createPush('PushTata12', '[[Category:tata]]'));

        $array = array('PushFeed:PushToto12','PushFeed:PushTiti12','PushFeed:PushTata12');
        $this->assertTrue($this->p2pBot1->push($array));

        //assert that allchange set were created
        $countCS = count(getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[inPushFeed::PushFeed:PushToto12]]', '-3FchangeSetID'));
        $this->assertTrue($countCS==1);

        $countCS = count(getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[inPushFeed::PushFeed:PushTiti12]]', '-3FchangeSetID'));
        $this->assertTrue($countCS==1);

        $countCS = count(getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[inPushFeed::PushFeed:PushTata12]]', '-3FchangeSetID'));
        $this->assertTrue($countCS==1);
    }

}
?>
