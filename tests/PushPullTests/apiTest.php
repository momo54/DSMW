<?php

define( 'MEDIAWIKI', true );
if( defined( 'MW_INSTALL_PATH' ) ) {
    $IP = MW_INSTALL_PATH;
} else {
    $IP = dirname('../../../../.');
}

require_once '../p2pBot.php';
require_once '../BasicBot.php';
require_once '../../logootComponent/LogootId.php';
require_once '../../logootComponent/LogootPosition.php';
require_once '../../logootComponent/LogootIns.php';
require_once '../../logootComponent/LogootDel.php';
require_once '../../p2pExtension.php';
require_once '../../patch/Patch.php';
require_once '../../files/utils.php';
include_once '../p2pAssert.php';


/**
 * apiQueryChangeSet, apiQueryPatch and apiPatchPush tests
 *
 * @author hantz
 */
class apiTest extends PHPUnit_Framework_TestCase {
    var $p2pBot1;
    var $p2pBot2;
    var $p2pBot3;
    var $tmpServerName;
    var $tmpScriptPath;

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
    }

    /**
     * @access protected
     */
    protected function tearDown() {

    }

    /**
     * test ApiQueryPatch
     */
    function testGetPatch() {

        $patchName = 'patch:localhost/wiki1';
        $content = '[[patchID::'.$patchName.']] [[onPage::Berlin]] [[previous::localhost/wiki0]]
        [[hasOperation::Localhost/wiki111;Insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577) ;content page berlin]]';
        $this->assertTrue($this->p2pBot1->createPage($patchName,$content),
            'failed to create page '.$patchName.' ('.$this->p2pBot1->bot->results.')');

        $this->assertTrue($this->p2pBot1->createPage($patchName,$content),
            'failed to create page '.$patchName.' ('.$this->p2pBot1->bot->results.')');
        $patchName = 'Patch:localhost/wiki2';
        $content = '[[patchID::'.$patchName.']] [[onPage::Paris]] [[previous::none]]
        [[hasOperation::Localhost/wiki121;Insert;(15555995255933583146:900c17ebee311fb6dd00970d26727577) ;content page Paris]]';

        $this->assertTrue($this->p2pBot1->createPage($patchName,$content),
            'failed to create page '.$patchName.' ('.$this->p2pBot1->bot->results.')');


        //ApiQueryPatch call
        $patchXML = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=patch&papatchId=Patch:localhost/wiki2&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($patchXML);
        $patchs = $dom->getElementsByTagName('patch');

        foreach($patchs as $p) {
            $this->assertEquals('patch:localhost/wiki2', strtolower($p->getAttribute('id')));
            $this->assertEquals('paris', strtolower($p->getAttribute('onPage')));
            $t = $p->getAttribute('previous');
            $this->assertEquals('none', strtolower($p->getAttribute('previous')));
        }

        $listeOp = $dom->getElementsByTagName('operation');

        $op = null;
        foreach($listeOp as $o)
            $op[] = $o->firstChild->nodeValue;

        $this->assertTrue(count($op)==1,'failed to count operation, '.count($op).' were found, but 1 operation is required');

        $contentOp = str_replace(" ", "",'Localhost/wiki121; Insert; (15555995255933583146:900c17ebee311fb6dd00970d26727577); content page Paris');
        $this->assertEquals($contentOp,str_replace(" ","", $op[0]));
    }

    /**
     * test ApiQueryChangeSet whithout previous changeSet
     */
    public function testGetChangeSetWhithoutPreviousCS() {
    // test with no previousChangeSet
        $pageName = "ChangeSet:localhost/wiki12";
        $content='ChangeSet:
changeSetID: [[changeSetID::localhost/wiki12]]
inPushFeed: [[inPushFeed::PushFeed:PushCity11]]
previousChangeSet: [[previousChangeSet::none]]
 hasPatch: [[hasPatch::"Patch:Berlin1"]] hasPatch: [[hasPatch::"Patch:Paris0"]]';
        $this->p2pBot1->createPage($pageName, $content);

        $pageName = 'PushFeed:PushCity11';
        $content = 'PushFeed:
Name: [[name::CityPush2]]
hasSemanticQuery: [[hasSemanticQuery::-5B-5BCategory:city-5D-5D]]
Pages concerned:
{{#ask: [[Category:city]]}} hasPushHead: [[hasPushHead::ChangeSet:localhost/mediawiki12]]';
        $this->p2pBot1->createPage($pageName,$content);

        //apiQueryChangeSet call
        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=changeSet&cspushName=PushCity11&cschangeSet=none&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($cs);
        $changeSet = $dom->getElementsByTagName('changeSet');
        foreach($changeSet as $cs) {
            if ($cs->hasAttribute("id")) {
                $CSID = $cs->getAttribute('id');
            }
        }

        $this->assertEquals('localhost/wiki12',strtolower($CSID));

        $listePatch = $dom->getElementsByTagName('patch');

        foreach($listePatch as $pays)
            $patch[] = $pays->firstChild->nodeValue;

        $this->assertTrue(count($patch)==2);
        $this->assertEquals('Patch:Berlin1',$patch[0]);
        $this->assertEquals('Patch:Paris0',$patch[1]);
    }

    /**
     * test apiQueryChangeSet with a previous changeSet
     */
    public function testGetChangeSetWhithPreviousCS() {
        $pageName = "ChangeSet:localhost/wiki13";
        $content='ChangeSet:
changeSetID: [[changeSetID::localhost/wiki13]]
inPushFeed: [[inPushFeed::PushFeed:PushCity]]
previousChangeSet: [[previousChangeSet::ChangeSet:localhost/wiki12]]
 hasPatch: [[hasPatch::"Patch:Berlin2"]]';
        $this->p2pBot1->createPage($pageName, $content);

        //apiQueryChangeSet call
        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=changeSet&cspushName=PushCity&cschangeSet=ChangeSet:localhost/wiki12&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($cs);

        $changeSet = $dom->getElementsByTagName('changeSet');
        foreach($changeSet as $cs) {
            if ($cs->hasAttribute("id")) {
                $CSID = $cs->getAttribute('id');
            }
        }

        $this->assertEquals('localhost/wiki13',strtolower($CSID));

        $listePatch = $dom->getElementsByTagName('patch');

        $patch = null;
        foreach($listePatch as $pays)
            $patch[] = $pays->firstChild->nodeValue;

        $this->assertTrue(count($patch)==1);
        $this->assertEquals('Patch:Berlin2',$patch[0]);
    }

    /**
     * test apiQueryChangeSet with an unexist changeSet
     */
    public function testGetChangeSetWhithUnexistCS() {
        $this->p2pBot1->createPage('toto', 'titi');
        $cs = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=changeSet&cspushName=PushCity&cschangeSet=ChangeSet:localhost/wiki13&format=xml');

        $dom = new DOMDocument();
        $dom->loadXML($cs);
        $changeSet = $dom->getElementsByTagName('changeSet');
        $CSID = null;
        foreach($changeSet as $cs1) {
            if ($cs1->hasAttribute("id")) {
                $CSID = $cs1->getAttribute('id');
            }
        }

        $this->assertEquals(null, $CSID,'failed, changeSetId must be null but '.$CSID.' was found');

        $patch = null;
        $listePatch = $dom->getElementsByTagName('patch');
        foreach($listePatch as $pays)
            $patch[] = $pays->firstChild->nodeValue;
        $this->assertEquals(null, $patch);
    }

    /**
     * test apiPatchPush with no push
     */
    public function testPatchPushed1() {
        $pageName = 'Pouxeux';

        $pushName = 'PushFeed:PushCity11';
        $content = 'PushFeed:
Name: [[name::CityPush2]]
hasSemanticQuery: [[hasSemanticQuery::-5B-5BCategory:city-5D-5D]]
Pages concerned:
{{#ask: [[Category:city]]}}';
        $this->assertTrue($this->p2pBot1->createPage($pushName, $content),'failed to create page PushFeed:PushCity11');

        $published = $this->getListPatchPushed($pushName,$pageName);
        $this->assertNull($published);

        $this->assertTrue($this->p2pBot1->push($pushName),'failed to push '.$pushName.' ( '.$this->p2pBot1->bot->results.' )');

        $published = $this->getListPatchPushed($pushName,$pageName);
        $this->assertNull($published);

        $this->assertTrue($this->p2pBot1->createPage('Toto','toto [[Category:city]]'));

        $this->assertTrue($this->p2pBot1->push($pushName),'failed to push '.$pushName.' ( '.$this->p2pBot1->bot->results.' )');

        $published = $this->getListPatchPushed($pushName, $pageName);
        $this->assertNull($published);
    }

    /**
     *
     */
    public function testPatchPushed2() {
        $this->testPatchPushed1();

        $pageName = 'Pouxeux';
        $this->assertTrue($this->p2pBot1->createPage($pageName, 'Pouxeux [[Category:city]]'));
        $this->assertTrue($this->p2pBot1->push('PushFeed:PushCity11'));

        $published = $this->getListPatchPushed($pushName,$pageName);
        $this->assertTrue(count($published)==1);

        $onPage = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[patchID::'.$published[0].']]', '-3FonPage');
        $this->assertEquals($pageName, $onPage[0],'failed into apiPatchPush, the patch found must be on page '.$pageName.' but is on '.$onPage[0]);

        $this->p2pBot1->editPage($pageName, '....');

        $published = $this->getListPatchPushed($pushName,$pageName);
        $this->assertTrue(count($published)==1);

        $onPage = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[patchID::'.$published[0].']]', '-3FonPage');
        $this->assertEquals($pageName, $onPage[0],'failed into apiPatchPush, the patch found must be on page '.$pageName.' but is on '.$onPage[0]);

        $this->assertTrue($this->p2pBot1->push('PushFeed:PushCity11'));
        $published = $this->getListPatchPushed($pushName,$pageName);
        $this->assertTrue(count($published)==2);

        $onPage = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[patchID::'.$published[0].']]', '-3FonPage');
        $this->assertEquals($pageName, $onPage[0],'failed into apiPatchPush, the patch found must be on page '.$pageName.' but is on '.$onPage[0]);

        $onPage = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[patchID::'.$published[1].']]', '-3FonPage');
        $this->assertEquals($pageName, $onPage[0],'failed into apiPatchPush, the patch found must be on page '.$pageName.' but is on '.$onPage[1]);
    }

    private function getListPatchPushed($pushName,$pageName) {
        $patchXML = file_get_contents($this->p2pBot1->bot->wikiServer.'/api.php?action=query&meta=patchPushed&pppushName='.$pushName.'&pppageName='.$pageName.'&format=xml');
        $dom = new DOMDocument();
        $dom->loadXML($patchXML);
        $patchPublished = $dom->getElementsByTagName('patch');
        $published = null;
        foreach($patchPublished as $p) {
            $published[] = $p->firstChild->nodeValue;
        }
        return $published;
    }
}
?>
