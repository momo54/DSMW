<?php
/**
 * Insertion operation used in the logoot algorithm
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe
 */
class LogootIns {
	private $mLogootPosition;
	private $mLineContent;
	private $mOpDegree;
	private $mId;


	/**
	 *
	 * @param <Object> $position LogootPosition
	 * @param <String> $content line content
	 */
	public function __construct($position, $content, $id = '', $degree='1') {
		$this->setLogootPosition($position);
		$this->setLineContent($content);
		$this->setLogootDegree($degree);
		$this->mId = $id;
	}

	public function getLogootPosition(){
		return $this->mLogootPosition;
	}

	public function getLineContent(){
		return $this->mLineContent;
	}

	public function setLogootPosition($position){
		$this->mLogootPosition = $position;
	}

	public function setLineContent($content) {
		$this->mLineContent = $content;
	}


	public function setLogootDegree($degree){
		$this->mOpDegree=$degree;
	}

	public function getLogootDegree(){
		return $this->mOpDegree;
	}

	public function execute(){

	}

	public function getId() {
		return $this->mId;
	}
}
?>
