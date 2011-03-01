<?php


if (!defined('MEDIAWIKI')){define( 'MEDIAWIKI', true );}
require_once 'p2pAssert.php';
require_once '../../../includes/GlobalFunctions.php';
require_once '../patch/Patch.php';
require_once '../files/utils.php';
require_once 'settings.php';
require_once 'BasicBot.php';
require_once 'p2pBot.php';

/**
 * Description of UndoConcurentialTest
 *
 * @author MAILLET Laurent
 */
class UndoConcurentialTest extends PHPUnit_Framework_TestCase {
	var $p2pBot1;
	var $p2pBot2;
	var $wiki1 = WIKI1;
	var $wiki2 = WIKI2;
	
	var $valueA;
	var $valueB;
	var $valueC;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp() {
		exec('./initWikiTest.sh ./dump.sql');
		//exec('rm ./cache/*');
		$basicbot1 = new BasicBot();
		$basicbot1->wikiServer = $this->wiki1;
		$this->p2pBot1 = new p2pBot($basicbot1);
		
        $basicbot2 = new BasicBot();
        $basicbot2->wikiServer = $this->wiki2;
        $this->p2pBot2 = new p2pBot($basicbot2);
        
        $this->valueA="A";
        $this->valueB="B";
        $this->valueC="C";
	}



	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @access protected
	 */
	protected function tearDown() {
	}


	/**
	 *
	 * Check what undo generate on patch
	 *
	 */
	public function testScenario(){
		echo "testScenario\n";
		$pageName = "Page1";
		$content = "[[Category:city]]";
		$this->p2pBot1->createPage($pageName, $content);
		$this->p2pBot1->editPage($pageName, $this->valueA);
		$this->p2pBot1->editPage($pageName, $this->valueB);
		$this->p2pBot1->editPage($pageName, $this->valueC);
		
		
		$this->p2pBot2->createPage($pageName, $content);
		$this->p2pBot2->editPage($pageName, $this->valueA);
		$this->p2pBot2->editPage($pageName, $this->valueB);
		$this->p2pBot2->editPage($pageName, $this->valueC);

		$patchWiki1 = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'.$pageName.']]', '-3FpatchID');
		$patchWiki2 = getSemanticRequest($this->p2pBot2->bot->wikiServer, '[[Patch:+]][[onPage::'.$pageName.']]', '-3FpatchID');
		
		$this->assertEquals(4,count($patchWiki1), 'Failed , we should get 4 patches but we get '.count($patchWiki1).' patches: '.$this->p2pBot1->bot->results);
		$this->assertEquals(4,count($patchWiki2), 'Failed , we should get 4 patches but we get '.count($patchWiki2).' patches: '.$this->p2pBot1->bot->results);

		//Undo Insert B
		$this->p2pBot1->undos($pageName, $patchWiki1[2]);
		$this->p2pBot2->undos($pageName, $patchWiki2[2]);
		/**
		 * TODO ASSERT visibility !
		 */
		
		//Push after Undo Insert B
		$pushNameFirstWiki1 = "Push1Wiki1";
		$pushRequest = $content;
		$this->p2pBot1->createPush($pushNameFirstWiki1, $pushRequest);
		$this->p2pBot1->push('PushFeed:'.$pushNameFirstWiki1);
		
		$pushNameFirstWiki2 = "Push1Wiki2";
		$this->p2pBot2->createPush($pushNameFirstWiki1, $pushRequest);
		$this->p2pBot2->push('PushFeed:'.$pushNameFirstWiki2);
		
		//Undo Undo Insert B On first Wiki
		$this->p2pBot1->undos($pageName, $patchWiki1[2]);
		$this->assertEquals(getContentPage($wiki2, $pageName) ,
							$content.$this->valueB.$this->valueC);
		/**
		 * TODO ASSERT visibility !
		 */
		
		//createSecondPushFeed
		$pushNameSecondWiki1 = "Push2Wiki1";
		$pushRequest = $content;
		$this->p2pBot1->createPush($pushNameSecondWiki1, $pushRequest);
		$this->p2pBot1->push('PushFeed:'.$pushNameSecondWiki1);
		
		//Pull on wiki2 the first push of wiki1
		$pullNameFirstWiki2 = "Pull1Wiki2";
		$this->p2pBot2->createPull($pullNameFirstWiki2, $this->wiki1, $pushNameFirstWiki1);
		$this->p2pBot2->pull('PullFeed:'.$pullNameFirstWiki2);
		$this->assertEquals(getContentPage($wiki2, $pageName) ,
							$content.$this->valueA.$this->valueC);
		
		/**
		 * TODO ASSERT visibility !
		 */
							
		//Pull on wiki1 the first push of wiki2
		$pullNameFirstWiki1 = "Pull1Wiki1";
		$this->p2pBot1->createPull($pullNameFirstWiki1, $this->wiki2, $pushNameFirstWiki2);
		$this->p2pBot1->pull('PullFeed:'.$pullNameFirstWiki1);
		$this->assertEquals(getContentPage($wiki1, $pageName) ,
							$content.$this->valueA.$this->valueC);
		/**
		 * TODO ASSERT visibility !
		 */
							
		//Pull on wiki2 the second push of wiki1
		$pullNameSecondWiki2 = "Pull2Wiki2";
		$this->p2pBot2->createPull($pullNameSecondWiki2, $this->wiki1, $pushNameSecondWiki1);
		$this->p2pBot2->pull('PullFeed:'.$pullNameSecondWiki2);
		$this->assertEquals(getContentPage($wiki1, $pageName) ,
							$content.$this->valueA.$this->valueC);
		/**
		 * TODO ASSERT visibility !
		 */
	}

}


?>