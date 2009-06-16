<?php
abstract class LogootOp{

    private $mopid;
    private $mpagename;

    abstract function execute();
    //abstract function precond();

    public function getOpid(){
        return $this->mopid;
    }

    public function setOpid($opid){
        $this->mopid = $opid;
    }

    public function toString(){
        //to be continued...
    }

    public function equals($obj){
        //to be continued...
    }

    public function getPageName(){
        return $this->mpagename;
    }

    public function setPageName($pageName){
        $this->mpagename = $pageName;
    }

}

?>
