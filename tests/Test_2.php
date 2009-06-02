<?php
require_once('BasicBot.php');

/**
 * Description of Test_2
 *
 * @author mullejea
 */

function callbackTestFct($content){
        return $content;
}


class Test_2 extends PHPUnit_Framework_TestCase{


    function testSeq1(){//Site1 page creation
        $myBot = new BasicBot();
        $source = 'Exercises1';
        $source1 = 'Exam1';
        $result = $myBot->wikiFilter($source, 'callbackTestFct','toto','titi');
        $this->assertTrue($result);
        $result = $myBot->wikiFilter($source1, 'callbackTestFct','toto','titi');
        $this->assertTrue($result);
    }

    function testSeq2(){
//        $myBot = new BasicBot();
//        $result = $myBot->createPush(SERVER, 'Course1', '[[Patch:+]]');
//        $this->assertTrue($result);
    }

}

?>
