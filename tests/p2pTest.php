<?php

require_once 'p2pBot.php';
require_once 'BasicBot.php';

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

    public function test3Wiki() {
        $res = $this->p2pBot1->createPage('wiki1','tata','toto 1');
        $this->assertTrue($res);

        $res = $this->p2pBot2->createPage('wiki2','tata','toto 2');
        $this->assertTrue($res);

        $res = $this->p2pBot3->createPage('wiki3','tata','toto 3');
        $this->assertTrue($res);
    }

   /* public function testGet(){
        //create page on wiki1
        $callback = 'callbackTestFct';
        $source = "Paris";
        $summary='sum';
        $content='content page Paris  [[category::city]]';
        $res = $this->wiki1->wikiFilter($source,$callback,$summary,$content);
        $this->assertTrue($res);

        $callback = 'callbackTestFct';
        $source = "Berlin";
        $summary='sum';
        $content='content page Berlin  [[category::city]]';
        $res = $this->wiki1->wikiFilter($source,$callback,$summary,$content);
        $this->assertTrue($res);

        //create push on wiki1
        $url = $this->wiki1->wikiServer.'/index.php?w='.$this->wiki1->wikiName;
        $name = 'TestPush';
        $request = '[[category::city]]';
        
        $res = $this->wiki1->createPush($url, $name, $request);
        $this->assertTrue($res);
    }*/
}
?>
