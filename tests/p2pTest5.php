<?php
define( 'MEDIAWIKI', true );
require_once 'p2pBot.php';
require_once 'BasicBot.php';
include_once 'p2pAssert.php';

 /*
 * Description of p2pTest5
 *
 * @author mullejea
 */
class p2pTest5 extends PHPUnit_Framework_TestCase {

    var $p2pBot1;

    /**
     *
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
    }

/**
 * Main_Page and UNTITLED pages are created before the installation of DSMW
 * so these tests focuses on these pages.
 * First we test that these article aren't editable (they should not)
 * Than we execute the "Articles update" feature
 * And we test if these article are now editable (they should)
 */

    public function testDSMWPagesUpdateFunction(){
       
       
       //edit Main_Page on wiki1
        $this->assertFalse($this->p2pBot1->editPage('Main_Page', 'edition test'),
            'succeeded to edit page Main_Page ( '.$this->p2pBot1->bot->results.' )');

        //edit UNTITLED on wiki1
        $this->assertFalse($this->p2pBot1->editPage('UNTITLED', 'edition test'),
            'succeeded to edit page UNTITLED ( '.$this->p2pBot1->bot->results.' )');

        //perform the "articles update" feature
        $this->p2pBot1->articlesUpdate();


        //edit Main_Page on wiki1
        $this->assertTrue($this->p2pBot1->editPage('Main_Page', 'another edition test'),
            'failed to edit page Main_Page ( '.$this->p2pBot1->bot->results.' )');

        //edit UNTITLED on wiki1
        $this->assertTrue($this->p2pBot1->editPage('UNTITLED', 'another edition test'),
            'failed to edit page UNTITLED ( '.$this->p2pBot1->bot->results.' )');

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
      
    }
}
?>
