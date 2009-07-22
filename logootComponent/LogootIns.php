<?php
/**
 * Description of LogootIns
 *
 * @author mullejea
 */
class LogootIns {
    private $mLogootPosition;
    private $mLineContent;

    function  __construct($position, $content) {
        $this->setLogootPosition($position);
        $this->setLineContent($content);
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

    public function execute(){

    }
}
?>
