<?php

/**
 * Undo operation used in the logoot algorithm
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author Fortun Manoël
 */
class LogootUndo {
    
    private $mPatchId;
    private $mOpDegree;

    /**
     *
     * @param <Object> $patchId patch id 
     * @param <String> $degree operation degree 
     */
    public function __construct($patchId, $degree='1') {
        $this->setPatchId($patchId);
        $this->mOpDegree=$degree;
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
