<?php

/**
 * Undo operation used in the logoot algorithm
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author Fortun Manoël
 */
class LogootUndo {
    
	private $mLogootPosition;
    private $mLineContent;
    private $mOpDegree;
    private $mId;

    /**
     *
     * @param <Object> $patchId patch id 
     * @param <String> $degree operation degree 
     */
    public function __construct($logootPosition, $patchId, $degree='1', $id='') {
    	$this->setLogootPosition($position);
        $this->setLineContent($patchId);
        $this->mOpDegree=$degree;
        $this->mId = $id;
    }


    public function setLogootPosition($position){
        $this->mLogootPosition = $position;
    }
 public function getLogootPosition(){
        return $this->mLogootPosition;
    }

    public function getLineContent(){
        return $this->mLineContent;
    }

    public function setLineContent($patchId){
        $this->mLineContent = $patchId;
    }


	public function setLogootDegree($degree){
    	$this->mOpDegree=$degree;
    }
    
	public function getLogootDegree(){
    	return $this->mOpDegree;
    }
    
    public function getId() {
    	return $this->mId;
    }
    
}
?>
