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
        $myBot = new BasicBot();
        $result = $myBot->createPush(SITE1, 'POUCHE2', '[[Category:City]]', SITE1);//on Site1
        $this->assertTrue($result);
//        $url = SITE1;
//        $name = 'Prof1Course1';
//        $result = $myBot->createPull($url, $name, SITE2);//on Site2
//        $this->assertTrue($result);
    }

}

?>
