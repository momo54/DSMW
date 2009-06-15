<?php

require_once 'BasicBot.php';

function callbackTestFct($content1,$content2) {
    return $content1.$content2;
}

/**
 * Description of p2pTest
 *
 * @author marlene
 */

class p2pTest extends PHPUnit_Framework_TestCase {

    var $wiki1;
    var $wiki2;
    var $wiki3;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        exec('./initWikiTest.sh');
    //define('SERVER','http://localhost/wiki1');
        $this->wiki1 = new BasicBot('Test1');

        $this->wiki2 = new BasicBot('Test2');

        $this->wiki3 = new BasicBot('Test3');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
        exec('./deleteTest.sh');
    }

    public function test3Wiki() {
    // Remove the following lines when you implement this test.

    // Our settings
        $source = 'wiki1';
        $callback = 'callbackTestFct';
        $res = $this->wiki1->wikiFilter($source,$callback,'tata','toto');
        $this->assertTrue($res);
        
        $source = 'wiki2';
        $res = $this->wiki2->wikiFilter($source,$callback,'tata','toto');
        $this->assertTrue($res);

        $source = 'wiki3';
        $res = $this->wiki3->wikiFilter($source,$callback,'tata','toto');
        $this->assertTrue($res);
    }
}
?>
