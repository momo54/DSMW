<?php
define( 'MEDIAWIKI', true );
require_once 'p2pBot.php';
require_once 'BasicBot.php';
include_once 'p2pAssert.php';
require_once '../../..//includes/GlobalFunctions.php';
require_once '../patch/Patch.php';
require_once '../files/utils.php';

/**
 * 
 *
 *
 * @author hantz
 */
class p2pTest3 extends PHPUnit_Framework_TestCase {

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
        exec('rm ./cache/*');
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = 'http://localhost/wiki1';
        $this->p2pBot1 = new p2pBot($basicbot1);
        $this->p2pBot1->updateProperies($this->p2pBot1->bot->wikiServer);

        $basicbot2 = new BasicBot();
        $basicbot2->wikiServer = 'http://localhost/wiki2';
        $this->p2pBot2 = new p2pBot($basicbot2);
        $this->p2pBot2->updateProperies($this->p2pBot2->bot->wikiServer);

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

    public function testSimple(){

        $this->p2pBot1->createPage('Moldova',
            'Moldova en-us-Moldova.ogg /mɒlˈdoʊvə/ (help·info), officially the Republic of Moldova (Republica Moldova) is a landlocked country in Eastern Europe, located between Romania to the west and Ukraine to the north, east and south.

In the Middle Ages, most of the present territory of Moldova was part of the Principality of Moldavia. In 1812, it was annexed by the Russian Empire, and became known as Bessarabia. Between 1856 and 1878, the southern part was returned to Moldavia. In 1859 it united with Wallachia to form modern Romania.

Upon the dissolution of the Russian Empire in 1917, an autonomous, then-independent Moldavian Democratic Republic was formed, which joined Romania in 1918. In 1940, Bessarabia was occupied by the Soviet Union and was split between the Ukrainian SSR and the newly created Moldavian SSR.

After changing hands in 1941 and 1944 during World War II, the territory of the modern country was subsumed by the Soviet Union until its declaration of independence on August 27, 1991. Moldova was admitted to the UN in March 1992.

In September 1990, a breakaway government was formed in Transnistria, a strip of Moldavian SSR on the east bank of the river Dniester. After a brief war in 1992, it became de facto independent, although no UN member has recognized its independence.

The country is a parliamentary democracy with a president as head of state and a prime minister as head of government. Moldova is a member state of the United Nations, Council of Europe, WTO, OSCE, GUAM, CIS, BSEC and other international organizations. Moldova currently aspires to join the European Union,[4] and has implemented the first three-year Action Plan within the framework of the European Neighbourhood Policy (ENP).[5] About a quarter of the population lives on less than US$ 2 a day.');

        $this->p2pBot1->createPush('PushPage_Moldova', '[[Moldova]]');
        $this->p2pBot1->push('PushFeed:PushPage_Moldova');

        $this->p2pBot2->createPull('PullMoldova', $this->p2pBot1->bot->wikiServer, 'PushPage_Moldova');
        $this->p2pBot2->pull('PullFeed:PullMoldova');

        //assert that there is the same changeSet on the 2 wikis
         $CSonWiki1 = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[ChangeSet:+]][[inPushFeed::PushFeed:PushPage_Moldova]]', '-3FchangeSetID');
        $CSonWiki2 = getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[ChangeSet:+]][[inPullFeed::PullFeed:PullMoldova]]', '-3FchangeSetID');
        $this->assertEquals($CSonWiki1,$CSonWiki2,'changeSet are not equals on the 2 wikis');

        //assert that there is the same patch on the 2 wikis
        $PatchonWiki1 = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::Moldova]]', '-3FpatchID');
        $PatchonWiki2 = getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[Patch:+]][[onPage::Moldova]]', '-3FpatchID');
        $PatchonWiki1 = arraytolower($PatchonWiki1);
        $PatchonWiki2 = arraytolower($PatchonWiki2);
        $this->assertEquals($PatchonWiki1,$PatchonWiki2,'patch are not equals on the 2 wikis');
        // assert that wiki1/Moldova == wiki2/Moldova
        assertContentEquals($this->p2pBot1->bot->wikiServer, $this->p2pBot2->bot->wikiServer, 'Moldova');
    }
}
?>
