<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Patch
 *
 * @author mullejea
 */
class Patch {
    private $mId;
    private $mPatchId;
    private $mOperations = array();
    private $mRevId;
    private $mActive;
    private $mPageId;

    public function __construct($patchid, $operations, $revid/*, $active*/, $pageId) {
        $this->mPatchId = $patchid;
        $this->mOperations = $operations;
        $this->mRevId = $revid;
        $this->mPageId = $pageId;
        //$this->active = $active;
    }

    public function getPatchid() {
        return $this->mPatchId;
    }

    public function setPatchid($mPatchId) {
        $this->mPatchId = $mPatchId;
    }
    public function getActive() {
        return $this->mActive;
    }

    public function setActive($active) {
        $this->mActive = $active;
    }

    public function getOperations() {
        return $this->mOperations;
    }

    public function setOperations($operations) {
        $this->mOperations = $operations;
    }

    public function getRevid() {
        return $this->mRevId;
    }

    public function setRevid($mRevId) {
        $this->mRevId = $mRevId;
    }

    public function getPageid() {
        return $this->mPageId;
    }

    public function setPageid($PageId) {
        $this->mPageId = $PageId;
    }

    public function getId() {
        return $this->mId;
    }

    public function setId($Id) {
        $this->mId = $Id;
    }




    /*******************Database access functions************************/
    public function store(){
         $operations = serialize($this->mOperations);
         $this->mId = $this->getNextPatchId();
        wfProfileIn( __METHOD__ );
        $dbw = wfGetDB( DB_MASTER );
        $dbw->insert( 'patchs', array(
            'id'        => $this->mId,
            'patch_id'        => $this->mPatchId,
            'operations'    => $operations,
            'is_active'     => $this->mActive,
            'rev_id'  => $this->mRevId,
            'page_id'  => $this->mPageId,
            ), __METHOD__ );

        wfProfileOut( __METHOD__ );
    }

    public function storePage($pageName){

        //$pageName = $this->getPageTitleWithId($this->mPageId);
        $previous = $this->getPreviousPatchId($pageName);
        if($previous==false) {
            $previous = "none";
            $ID = $pageName."_0";
        }else{
            $count = explode(" ", $previous);
            $cnt = $count[1] + 1;
            $ID = $pageName."_".$cnt;
        }

//        $pos = strrpos($ID, ":");//NS removing
//            if ($pos === false) {
//                // not found...
//            }else{
//                $articleName = substr($ID, $pos+1);
//                $ID = "Patch:".$articleName;
//            }

        $text = 'Patch: patchID: [[patchID::'.$ID.']]
 onPage: [[onPage::'.$pageName.']] ';
        $i=1;//op counter
        foreach ($this->mOperations as $operation){
            $lineContent = $operation->getLineContent();
            if(strpos($lineContent, '[[')!==false || strpos($lineContent, ']]')!==false){
                $lineContent = $this->encode($lineContent);
            }
            $type="";
            if($operation instanceof LogootIns) $type="Insert";
            else $type="Delete";
            $text.=' hasOperation: [[hasOperation::Op'.$i.']] :  [[Op'.$i.'::operationID'.$i.'| ]][[Op'.$i.'::opType'.$i.'| ]][[Op'.$i.'::position'.$i.'| ]][[Op'.$i.'::hasLineContent'.$i.'| ]]
( operationID'.$i.': [[operationID'.$i.'::'.$i.']]/
opType'.$i.': [[opType'.$i.'::'.$type.']]/
position'.$i.': [[position'.$i.'::'.$operation->getLogootPosition()->toString().']]/
hasLineContent'.$i.': [[hasLineContent'.$i.'::'.$lineContent.']] )';
            $i = $i + 1;
        }
        $text.=' previous: [[previous::'.$previous.']]';

        $title = Title::newFromText($ID, PATCH);
        $article = new Article($title);
        $article->doEdit($text, $summary="");
        //$article->doRedirect();

    }


function getPreviousPatchId($pageName){// methode a construire, csid=pfname+compteur
$req = '[[Patch:+]] [[onPage::'.$pageName.']]';
    $req = str_replace(
				          array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?'),
				          array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F'), $req);
    $url = dirname($_SERVER['HTTP_REFERER']);
    $url = $url."/index.php/Special:Ask/".$req."/-3FpatchID/headers=hide/order=desc/format=csv/limit=1";
    $string = file_get_contents($url);
    if ($string=="") return false;
//    $pos = strrpos($string, ":");
//    if ($pos === false) {
//        // not found...
//    }else{
//        $string = substr($string, $pos+1);
//    }
$string = explode(",", $string);
$string = $string[0];
    $string = str_replace(',', '', $string);
//    $string = strtr($string, "\"", "\0");
    $string = str_replace("\"", "", $string);
    return $string;
}

function getPageTitleWithId($id){//returns false if the article doesn't exist yet
        $dbr = wfGetDB( DB_SLAVE );
        $title = $dbr->selectField('page','page_title', array(
        'page_id'=>$id));
        return $title;
    }

//function getNextPatchId(){
//
//}

    public function load($id){
        $db = wfGetDB( DB_SLAVE );
        $fields = array(
            'patch_id',
			'operations',
			'is_active',
			'rev_id',
            'page_id');
        $conditions = array( "id=$id") ;
        $res = $db->select(
			array( 'patchs' ),
			$fields,
			$conditions,
			__METHOD__ );
        $res1 = $db->resultObject( $res );

        if( $res1 ) {
			$row = $res1->fetchObject();
			$res1->free();
			if( $row ) {
				$ret1 = new Patch( $row->patch_id, unserialize($row->operations), $row->rev_id, $row->page_id );
               
				return $ret1;
			}
		}
		$ret1 = null;
		return $ret1;
    }

    private function getNextPatchId(){
         wfProfileIn( __METHOD__ );
        $dbr = wfGetDB( DB_SLAVE );
        $lastid = $dbr->selectField('patchs','MAX(id)', array(
        'page_id'=>$this->mPageId));

        wfProfileOut( __METHOD__ );

        return $lastid + 1;
    }


function encode($request){
    $req = str_replace(
				          array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}'),
				          array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D'), $request);
                      return $req;
}

function decode($req){
    $request = str_replace(
				          array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D'),
                          array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}'), $req);
                      return $request;
}
}
?>
