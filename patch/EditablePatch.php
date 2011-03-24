<?php

class EditablePatch {
	private $mPatchPage;
	
	private $mPatchId;


	/**
	 *
	 * Creates a patch object from its id and the article.
	 * @param string $patchId Should be like Patch:blahblah
	 */
	public function __construct($patchId) {
		 
		//echo 'patchid is '. $patchId . "\n";
		 
		$this->mPatchId = $patchId;
		 
		$title = Title::newFromText($patchId, PATCH);
		 
		$dbr = wfGetDB( DB_SLAVE );
		$revision = Revision::loadFromTitle($dbr, $title);
		$this->mPatchPage = $revision->getText();
		 
	}

	
	public function getPageTitle() {
		preg_match('`\[\[onPage::([^[]+)]]`', $this->mPatchPage, $out);
		
		return $out[1];
	}


	/**
	 * Returns an array that contains the operations.
	 */
	public function getOperations() {
		preg_match_all('`\[\[hasOperation::([^%]+)]]`',$this->mPatchPage,$out);
		 
		if(count($out) == 2) {
			foreach($out[0] as $op) {
				$retour[] = operationToLogootOp($op);
			}
		}
		 
		return $retour;
	}


	/**
	 * Replaces all operations of the patch by the given $ops.
	 * 
	 * @param unknown_type $ops
	 */
	public function setOperations($ops) {
		
		$res = preg_replace('`\|\[\[hasOperation::([^%]+)\|\-\n`', '', $this->mPatchPage, -1);
		
		$opstxt = '';
		
		foreach ($ops as $operation) {
			$lineContent = $operation->getLineContent();
			$lineContent1 = utils::contentEncoding($lineContent); //base64 encoding
			$type = "";
			if ($operation instanceof LogootIns){
				$type = "Insert";
			}  elseif ($operation instanceof LogootDel){
				$type="Delete";
			} else {
				$type="Undo";
			}
			$operationID = $operation->getId();
			$text.='|[[hasOperation::' . $operationID . ';' . $type . ';'
			. $operation->getLogootPosition()->toString() . ';' . $lineContent1 . ';'.$operation->getLogootDegree(). '| ]]' . $type;
				
			//displayed text
			$lineContent2 = $lineContent;//.
			$text.='
|<nowiki>' . $lineContent2 . '</nowiki>
|-
';
			$opstxt = $opstxt . $text;
		}
		
		
		$bouts_de_page = preg_split("`Content\n\|\-\n`", $res);
		
		$newpage = $bouts_de_page[0] . "Content\n|-\n" . $opstxt . $bouts_de_page[1];
		
		$this->mPatchPage = $newpage;
	}
	
	


}

?>