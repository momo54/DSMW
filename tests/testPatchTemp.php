<?php
if (!defined('MEDIAWIKI')){define( 'MEDIAWIKI', true );}
include_once('../includes/IntegrationFunctions.php');
require_once('../files/utils.php');
require_once('p2pBot.php');
require_once('BasicBot.php');
include_once('p2pAssert.php');
require_once('../patch/Patch.php');
require_once('../patch/EditablePatch.php');
require_once('settings.php');


class testPatchTemp extends PHPUnit_Framework_TestCase {

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
        exec('rm -f ./cache/*');
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = $this->wiki1;
        $this->p2pBot1 = new p2pBot($basicbot1);
    }
    
	protected function tearDown() {
    	// exec('./deleteTest.sh');
    }
    
    public function testRetrieve() {
    	echo "testRetrieve\n";
    	
    	$pageName = "Lambach";
        $content='content page Lambach
[[Category:city1]]';
        
        $this->assertTrue($this->p2pBot1->createPage($pageName,$content),
            'Failed to create page '.$pageName.' ('.$this->p2pBot1->bot->results.')');
        
        echo "Page created\n";
        
        $patchId = getSemanticRequest($this->p2pBot1->bot->wikiServer, '[[onPage::Lambach]]', '-3FpatchID');
        
        echo 'patchId = ' . $patchId[0] . "\n";
    	
        $post_vars['patchID'] = $patchId[0];
        
        $this->p2pBot1->doRequest('TESTUNDO', $post_vars);
        
        echo 'done ?';
    }
    
}


?>