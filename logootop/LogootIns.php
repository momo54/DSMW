<?php
/**
 * Description of LogootIns
 *
 * @author mullejea
 */
class LogootIns extends LogootOp{
    private $mLineNumber;
    private $mLogootPosition;
    private $mLineContent;

    function  __construct($number, $position, $content) {
        $this->setLineNumber($number);
        $this->setLogootPosition($position);
        $this->setLineContent($content);
    }

    public function getLineNumber(){
        return $this->mLineNumber;
    }

    public function getLogootPosition(){
        return $this->mLogootPosition;
    }

    public function getLineContent(){
        return $this->mLineContent;
    }

    public function setLineNumber($number){
        $this->mLineNumber = $number;
    }

    public function setLogootPosition($position){
        $this->mLogootPosition = $position;
    }

    public function setLineContent($content) {
        $this->mLineContent = $content;
    }

    public function execute(){

    }
}
?>
