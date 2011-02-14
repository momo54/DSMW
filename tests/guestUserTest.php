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


class guestUserTestUndo extends PHPUnit_Framework_TestCase {
	
	
	  var $p2pBot1;
    var $p2pBot2;
    var $p2pBot3;
    var $wiki1 = WIKI1;
    var $wiki2 = WIKI2;
    var $wiki3 = WIKI3;
    var $pageName;
	
	/*
	 * Check after the number of patch created
	 * the content of the patch example undo a patch that containt : 2 delete and 3 insert the 
	 * undoying patch must containt 3 insert 2 delete
	 * 
	 * 
	 * index.php?title=Thoan&action=admin
	 * 
	 * {{#ask: [[onPage::Thoan]] }}
	 * 
	 * $res = utils::getSemanticQuery('[[Patch:+]][[onPage::'.$title.']]', '?patchID');
	 * 
	 * $this->p2pBot1->editPage($pageName, 'create the second changeSet')
	 * 
	 * $this->p2pBot1->createPage($pageName,$content)
	 * 
	 * $this->bot->submit($this->bot->wikiServer.PREFIX.'/index.php',$post_vars
	 */
	
 protected function setUp() {
        exec('./initWikiTest.sh ./dump.sql');
        exec('rm ./cache/*');
        $basicbot1 = new BasicBot();
        $basicbot1->wikiServer = $this->wiki1;
        $this->p2pBot1 = new p2pBot($basicbot1);

        /**
        $basicbot2 = new BasicBot();
        $basicbot2->wikiServer = $this->wiki2;
        $this->p2pBot2 = new p2pBot($basicbot2);

        $basicbot3 = new BasicBot();
        $basicbot3->wikiServer = $this->wiki3;
        $this->p2pBot3 = new p2pBot($basicbot3);
        */
        
        $this->pageName="pagesss";
        $content=" Du contenu 
        
        
        [[Category=test]]
        ";
        
        $this->p2pBot1->createPage($pageName,$content);
        $this->p2pBot1->editPage($pageName, 'create the second changeSet');
        $this->p2pBot1->editPage($pageName, 'create the third changeSet');
        $this->p2pBot1->editPage($pageName, 'create the four changeSet');
        $this->p2pBot1->editPage($pageName, 'create the five changeSet');
        $this->p2pBot1->editPage($pageName, 'create the six changeSet');
        $this->p2pBot1->editPage($pageName, 'create the seven changeSet');
        $this->p2pBot1->editPage($pageName, 'create the height changeSet');
        $this->p2pBot1->editPage($pageName, 'create the nine changeSet');
        
        
        
    }
	
	
	public function testUndoAllPatch(){
		
			
		$res =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]'); 
		//utils::orderPatchByPrevious($pageName);
	
		$nbPatchPreUndo=count($res);
		
		
		assert($this->p2pBot1->undo($pageName, $res));
		
		$resPostTest =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]');
		
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo*2)==count($resPostTest));
		
		//check content of the patch that they are inverse
	} 
	
	
	public function testUndoNoPatch(){
		$res=array();
		assert($this->p2pBot1->undo($pageName, $res));
	}
	
	public function testUndoOnePatch(){
			$res =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]'); 
		//utils::orderPatchByPrevious($pageName);
	
		$nbPatchPreUndo=count($res);
		
		$res=$this->orderPatches($res);
		
		
		assert($this->p2pBot1->undo($pageName, $res[3]));
		
		$resPostTest =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]');
		
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+1)==count($resPostTest));
		
		//check the content
		
		
	}
	
	public function testUndoLastPacth(){
		$res =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]'); 
		//utils::orderPatchByPrevious($pageName);
	
		$nbPatchPreUndo=count($res);
		
		$res=$this->orderPatches($res);
		
	
		
		assert($this->p2pBot1->undo($pageName,$res[count($res)-1] ));
		
		$resPostTest =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]');
		
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+2)==count($resPostTest));
		
		//check the content
		
	}
	
	public function testUndoFirstPatch(){
		$res =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]'); 
		//utils::orderPatchByPrevious($pageName);
	
		$nbPatchPreUndo=count($res);
		
		$res=$this->orderPatches($res);
		
		
		assert($this->p2pBot1->undo($pageName,$res[0] ));
		
		$resPostTest =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]');
		
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+2)==count($resPostTest));
		
		//check the content
		
	}
	
	
	public function testUndoTwoLastPatches(){
			$res =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]'); 
		//utils::orderPatchByPrevious($pageName);
	
		$nbPatchPreUndo=count($res);
		
		$res=$this->orderPatches($res);
		
		$tempPatch[0]=$res[count($res)-1];
		$tempPatch[1]=$res[count($res)-2];
		
		assert($this->p2pBot1->undo($pageName,$tempPatch ));
		
		$resPostTest =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]');
		
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+2)==count($resPostTest));
		
		//check the content
	}
	
	public function testUndoTwoFirstPatches(){
		$res =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]'); 
		//utils::orderPatchByPrevious($pageName);
	
		$nbPatchPreUndo=count($res);
		
		$res=$this->orderPatches($res);
		
		$tempPatch[0]=$res[0];
		$tempPatch[1]=$res[1];
		
		assert($this->p2pBot1->undo($pageName,$tempPatch ));
		
		$resPostTest =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]');
		
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+2)==count($resPostTest));
		
		//check the content
		
	}
	
	
	public function testUndoTwoFollowingPatches(){
	$res =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]'); 
		//utils::orderPatchByPrevious($pageName);
	
		$nbPatchPreUndo=count($res);
		
		$res=$this->orderPatches($res);
		
		$tempPatch[0]=$res[3];
		$tempPatch[1]=$res[4];
		
		assert($this->p2pBot1->undo($pageName,$tempPatch ));
		
		$resPostTest =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]');
		
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+2)==count($resPostTest));
		
		//check the content
		
	}
	
	public function testUndoTwoNonFollowingPacthes(){
		$res =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]'); 
		//utils::orderPatchByPrevious($pageName);
	
		$nbPatchPreUndo=count($res);
		
		$res=$this->orderPatches($res);
		
		$tempPatch[0]=$res[2];
		$tempPatch[1]=$res[6];
		
		assert($this->p2pBot1->undo($pageName,$tempPatch ));
		
		$resPostTest =getSemanticRequestArrayResult($this->p2pBot1->bot->wikiServer, '[[Patch:+]][[onPage::'. $this->pageName.']]');
		
		
		//check if the number of pacth is expected
		assert(($nbPatchPreUndo+2)==count($resPostTest));
		
		//check the content
		
	}
	
	public function orderPatches($patches){
		return $patches;
	}
	
}

?>