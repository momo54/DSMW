<?php

/**
 * Undo operation used in the logoot algorithm
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author Fortun Manoël
 */
class LogootUndo {
    
	private $mLogootPosition;
    private $mPatchId;
    private $mOpDegree;

    /**
     *
     * @param <Object> $patchId patch id 
     * @param <String> $degree operation degree 
     */
    public function __construct($logootPosition, $patchId, $degree='1') {
    	$this->setLogootPosition($position);
        $this->setPatchId($patchId);
        $this->mOpDegree=$degree;
    }


    public function setLogootPosition($position){
        $this->mLogootPosition = $position;
    }
 public function getLogootPosition(){
        return $this->mLogootPosition;
    }

    public function getPatchId(){
        return $this->mPatchId;
    }

    public function setPatchId($patchId){
        $this->mPatchId = $patchId;
    }


	public function setLogootDegree($degree){
    	$this->mOpDegree=$degree;
    }
    
	public function getLogootDegree(){
    	return $this->mOpDegree;
    }
    
}
?>
