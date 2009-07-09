<?php

define( 'MEDIAWIKI', true );
require_once 'p2pBot.php';
require_once 'BasicBot.php';
include_once 'p2pAssert.php';
require_once '../../..//includes/GlobalFunctions.php';
require_once '../patch/Patch.php';
require_once '../files/utils.php';

/**
 * Description of p2pBotTest
 *
 * @author hantz
 */
class p2pBotTest extends PHPUnit_Framework_TestCase {
    var $p2pBot1;

    public static function main() {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('MyFileTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

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

    public function testAppendPage() {
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

    public function testCreatePush() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testPush() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testCreatePull() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testPull() {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
?>
