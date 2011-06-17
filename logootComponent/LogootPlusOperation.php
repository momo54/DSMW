<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author emmanuel Desmontils
 */
class LogootPlusOperation extends LogootOperation {
    protected $isInv = false;

    public function __construct(LogootPosition $position, $content) {
        parent::__construct($position, $content);
        $this->isInv = false;
    }

    public function __clone() {
        $newOp = new LogootPlusOperation(clone $this->mLogootPosition, $this->mLineContent);
        if ($this->isInv) $newOp->setInv();
        return $newOp;
    }

    public function  __toString() {
        $res = "LogootPlus Operation ".get_class($this).($this->isInv?" -1":"")."\n\t Pos :"
                                      .$this->mLogootPosition."\n\t Content : '"
                                      .$this->mLineContent."'\n";
        return $res;
    }

    public static function inv(LogootPlusOperation $op) {
        $op_inv = clone $op;
        $op_inv->setInv();
        return $op_inv;
    }

    public function setInv(){
        $this->isInv = ! $this->isInv;
    }

    public function isInv() {return $this->isInv;}
}
?>
