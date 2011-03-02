<?php
if (!defined('MEDIAWIKI')){define( 'MEDIAWIKI', true );}
require_once 'p2pBot.php';
require_once 'BasicBot.php';
include_once 'p2pAssert.php';
require_once '../../../includes/GlobalFunctions.php';
require_once '../patch/Patch.php';
require_once '../files/utils.php';
require_once 'settings.php';

$wgDebugLogGroups  = array(
        'p2p'=>"/tmp/p2p.log",
);

/**
 * Description of undoAdminTest
 * 
 * This is the test for the correspondant user story
 * 
 * @author Charles Dejean
 */
class undoAdminTest extends PHPUnit_Framework_TestCase {
	
	var $p2pBotUndo;
    var $p2pBotBasis;
    var $wiki1 = WIKI1;
    var $wiki2 = WIKI2;
    
    var $pageName1;
	var $pageName2;
	var $basisContent;
		
 	protected function setUp() {
        exec('./initWikiTest.sh ./dump.sql');
        exec('rm ./cache/*');
        
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = $this->wiki1;
        $this->p2pBotUndo = new p2pBot($basicbot1);
       
        $basicbot2 = new BasicBot();
        $basicbot2->wikiServer = $this->wiki2;
        $this->p2pBotBasis = new p2pBot($basicbot2);
           
        $this->pageName1="pages1";
        $this->basisContent=" Du contenu 
        
        
        [[Category=test]]
        ";
        
        $this->p2pBotUndo->createPage($pageName1,$basisContent);
        $this->p2pBotUndo->editPage($pageName, 'create the second changeSet on the pages 1');
        $this->p2pBotUndo->editPage($pageName, 'create the third changeSet on the pages 1');
        $this->p2pBotUndo->editPage($pageName, 'create the four changeSet on the pages 1');
        $this->p2pBotUndo->editPage($pageName, 'create the five changeSet on the pages 1');
        $this->p2pBotUndo->editPage($pageName, 'create the six changeSet on the pages 1');
        $this->p2pBotUndo->editPage($pageName, 'create the seven changeSet on the pages 1');
        $this->p2pBotUndo->editPage($pageName, 'create the height changeSet on the pages 1');
        $this->p2pBotUndo->editPage($pageName, 'create the nine changeSet on the pages 1');
        
        $this->pageName2="pages2";
        
        $this->p2pBotUndo->createPage($pageName2,$basisContent);
        $this->p2pBotUndo->editPage($pageName, 'create the second changeSet on the pages 2');
        $this->p2pBotUndo->editPage($pageName, 'create the third changeSet on the pages 2');
        $this->p2pBotUndo->editPage($pageName, 'create the four changeSet on the pages 2');
        $this->p2pBotUndo->editPage($pageName, 'create the five changeSet on the pages 2');
        
    }
    
	public function testUndoOnePatch(){
		
		//basis wiki fixture
		$this->p2pBotBasis->createPage($pageName1,$basisContent);
        $this->p2pBotBasis->editPage($pageName, 'create the second changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the third changeSet on the pages 1');
        // $this->p2pBotBasis->editPage($pageName, 'create the four changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        $this->p2pBotBasis->editPage($pageName, 'create the five changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the six changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the seven changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the height changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the nine changeSet on the pages 1');
        
        $this->p2pBotBasis->createPage($pageName2,$basisContent);
        $this->p2pBotBasis->editPage($pageName, 'create the second changeSet on the pages 2');
        $this->p2pBotBasis->editPage($pageName, 'create the third changeSet on the pages 2');
        $this->p2pBotBasis->editPage($pageName, 'create the four changeSet on the pages 2');
        $this->p2pBotBasis->editPage($pageName, 'create the five changeSet on the pages 2');
		
        // semantic request for collect all the patch of the category : test. Before undo
		$res=getSemanticRequestArrayResult($this->p2pBotUndo->bot->wikiServer, '[[Patch:+]][[onCategory::test]]'); 
	
		// count patch was selected by the semantic request. Before undo
		$nbPatchPreUndo=count($res);
		
		// undo the third patch
		assert($this->p2pBotUndo->undos($res[3]));
		
		// semantic request for collect all the patch of the category : test. After undo
		$resPostTest =getSemanticRequestArrayResult($this->p2pBotUndo->bot->wikiServer, '[[Patch:+]][[onCategory::test]]');
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+1)==count($resPostTest));
		
		//check the content of the page1
		assert(getContentPage($this->p2pBotBasis->bot->wikiServer, $pageName1) == getContentPage($this->p2pBotUndo->bot->wikiServer, $pageName1));
		//check the content of the page2
		assert(getContentPage($this->p2pBotBasis->bot->wikiServer, $pageName2) == getContentPage($this->p2pBotUndo->bot->wikiServer, $pageName2));
	}
	
	public function testUndoOnePatchByPage(){
		
		//basis wiki fixture
		$this->p2pBotBasis->createPage($pageName1,$basisContent);
        $this->p2pBotBasis->editPage($pageName, 'create the second changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the third changeSet on the pages 1');
        // $this->p2pBotBasis->editPage($pageName, 'create the four changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        $this->p2pBotBasis->editPage($pageName, 'create the five changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the six changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the seven changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the height changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the nine changeSet on the pages 1');
        
        $this->p2pBotBasis->createPage($pageName2,$basisContent);
        $this->p2pBotBasis->editPage($pageName, 'create the second changeSet on the pages 2');
        $this->p2pBotBasis->editPage($pageName, 'create the third changeSet on the pages 2');
        // $this->p2pBotBasis->editPage($pageName, 'create the four changeSet on the pages 2'); --> this patch will be undo on the p2pBotUndo
        $this->p2pBotBasis->editPage($pageName, 'create the five changeSet on the pages 2');
		
        // semantic request for collect all the patch of the category : test. Before undo
		$res=getSemanticRequestArrayResult($this->p2pBotUndo->bot->wikiServer, '[[Patch:+]][[onCategory::test]]'); 
	
		// count patch was selected by the semantic request. Before undo
		$nbPatchPreUndo=count($res);
		
		// undo the patch number three in the semantic request
		assert($this->p2pBotUndo->undos($res[3]));
		// undo the patch number eleven in the semantic request
		assert($this->p2pBotUndo->undos($res[12]));
		
		// semantic request for collect all the patch of the category : test. After undo
		$resPostTest =getSemanticRequestArrayResult($this->p2pBotUndo->bot->wikiServer, '[[Patch:+]][[onCategory::test]]');
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+2)==count($resPostTest));
		
		//check the content of the page1
		assert(getContentPage($this->p2pBotBasis->bot->wikiServer, $pageName1) == getContentPage($this->p2pBotUndo->bot->wikiServer, $pageName1));
		//check the content of the page2
		assert(getContentPage($this->p2pBotBasis->bot->wikiServer, $pageName2) == getContentPage($this->p2pBotUndo->bot->wikiServer, $pageName2));
	}
    
	public function testUndoAllPatchInOnePage(){
	
		//basis wiki fixture
		$this->p2pBotBasis->createPage($pageName1,$basisContent);
        $this->p2pBotBasis->editPage($pageName, 'create the second changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the third changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the four changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the five changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the six changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the seven changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the height changeSet on the pages 1');
        $this->p2pBotBasis->editPage($pageName, 'create the nine changeSet on the pages 1');
        
        $this->p2pBotBasis->createPage($pageName2,""); // --> no content on the first patch
        // $this->p2pBotBasis->editPage($pageName, 'create the second changeSet on the pages 2'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the third changeSet on the pages 2'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the four changeSet on the pages 2'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the five changeSet on the pages 2'); --> this patch will be undo on the p2pBotUndo
		
        // semantic request for collect all the patch of the category : test. Before undo
		$res=getSemanticRequestArrayResult($this->p2pBotUndo->bot->wikiServer, '[[Patch:+]][[onCategory::test]]'); 
	
		// count patch was selected by the semantic request. Before undo
		$nbPatchPreUndo=count($res);
		
		// undo all the patch of the page2 
		assert($this->p2pBotUndo->undos($res[9]));
		assert($this->p2pBotUndo->undos($res[10]));
		assert($this->p2pBotUndo->undos($res[11]));
		assert($this->p2pBotUndo->undos($res[12]));
		assert($this->p2pBotUndo->undos($res[13]));
		
		// semantic request for collect all the patch of the category : test. After undo
		$resPostTest =getSemanticRequestArrayResult($this->p2pBotUndo->bot->wikiServer, '[[Patch:+]][[onCategory::test]]');
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+4)==count($resPostTest));
		
		//check the content of the page1
		assert(getContentPage($this->p2pBotBasis->bot->wikiServer, $pageName1) == getContentPage($this->p2pBotUndo->bot->wikiServer, $pageName1));
		//check the content of the page2
		assert(getContentPage($this->p2pBotBasis->bot->wikiServer, $pageName2) == getContentPage($this->p2pBotUndo->bot->wikiServer, $pageName2));
	}
		
	public function testUndoAllPatchOfTheSemanticRequest(){
		
		//basis wiki fixture
		$this->p2pBotBasis->createPage($pageName1,""); // --> no content on the first patch
        // $this->p2pBotBasis->editPage($pageName, 'create the second changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the third changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the four changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the five changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the six changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the seven changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the height changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the nine changeSet on the pages 1'); --> this patch will be undo on the p2pBotUndo
        
        $this->p2pBotBasis->createPage($pageName2,""); // --> no content on the first patch
        // $this->p2pBotBasis->editPage($pageName, 'create the second changeSet on the pages 2'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the third changeSet on the pages 2'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the four changeSet on the pages 2'); --> this patch will be undo on the p2pBotUndo
        // $this->p2pBotBasis->editPage($pageName, 'create the five changeSet on the pages 2'); --> this patch will be undo on the p2pBotUndo
		
        // semantic request for collect all the patch of the category : test. Before undo
		$res=getSemanticRequestArrayResult($this->p2pBotUndo->bot->wikiServer, '[[Patch:+]][[onCategory::test]]'); 
	
		// count patch was selected by the semantic request. Before undo
		$nbPatchPreUndo=count($res);
		
		// undo all the patch of the semantic request
		for ($i=0 ; $i<$nbPatchPreUndo ; $i++){
			assert($this->p2pBotUndo->undos($res[$i]));
		}
		
		// semantic request for collect all the patch of the category : test. After undo
		$resPostTest =getSemanticRequestArrayResult($this->p2pBotUndo->bot->wikiServer, '[[Patch:+]][[onCategory::test]]');
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+4)==count($resPostTest));
		
		//check the content of the page1
		assert(getContentPage($this->p2pBotBasis->bot->wikiServer, $pageName1) == getContentPage($this->p2pBotUndo->bot->wikiServer, $pageName1));
		//check the content of the page2
		assert(getContentPage($this->p2pBotBasis->bot->wikiServer, $pageName2) == getContentPage($this->p2pBotUndo->bot->wikiServer, $pageName2));
	}
		
}

?>   
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    