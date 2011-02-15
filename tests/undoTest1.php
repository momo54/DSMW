<?php


if (!defined('MEDIAWIKI')){define( 'MEDIAWIKI', true );}
require_once 'p2pBot.php';
require_once 'BasicBot.php';
include_once 'p2pAssert.php';
require_once '../../..//includes/GlobalFunctions.php';
require_once '../patch/Patch.php';
require_once '../files/utils.php';
require_once 'settings.php';


/**
 * Description of p2pTest2
 *
 * @author MAILLET Laurent
 */
class UndoTest1 extends PHPUnit_Framework_TestCase {
	var $p2pBot1;
	var $wiki1 = WIKI1;


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 */
	protected function setUp() {
		exec('./initWikiTest.sh ./dump.sql');
		exec('rm ./cache/*');
		$basicbot1 = new BasicBot();
		$basicbot1->wikiServer = $this->wiki1;
		$this->p2pBot1 = new p2pBot($basicbot1);
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
	public function testUndo1(){
		echo "testUndo1\n";
		$pageName = "Page1";
		$content = "[[Category : city]]";
		$this->assertTrue($this->p2pBot1->createPage($pageName, $content),
    	 'Failed to create page '.$pageName.'('.$this->p2pBot1->bot->results.')');
		 
		 
		$addcontent = 'Page1 est une ville du nord de l\'espagne.';
		 
		$this->assertTrue($this->p2pBot1->editPage($pageName, $addcontent),
    	'Failed to edit page '. $pageName. '('.$this->p2pBot1->bot->results.')');
		 
		// Check that we have 2 patches
		$patch = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'.$pageName.']]', '-3FpatchID');
		 
		$this->assertEquals(2,count($patch), 'Failed , we should get 2 patches but we get '.count($patch).' patches: '.$this->p2pBot1->bot->results);
		 
		$this->assertTrue($this->p2pBot1->undo($patch[1]),
    		'Failed to undo patch : (' . $this->p2pBot1->bot->result. ')' );
		 
		// Check that we have 3 patches
		$patch = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'.$pageName.']]', '-3FpatchID');
		 
		$this->assertEquals(3,count($patch), 'Failed , we should get 3 patches but we get '.count($patch).' patches: '.$this->p2pBot1->bot->results);
		 
	}

}


?>