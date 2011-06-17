<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogootPlusDel
 *
 * @author emmanuel Desmontils
 */
class LogootPlusDel extends LogootPlusOperation {

    public function __construct($position, $content) {
        parent::__construct($position, $content);
    }

    public function __clone() {
        $newOp = new LogootPlusDel(clone $this->mLogootPosition, $this->mLineContent);
        if ($this->isInv) $newOp->setInv();
        return $newOp;
    }

    public function type() {
        if ($this->isInv) return LogootOperation::INSERT;
        else return LogootOperation::DELETE;
    }
    
    public static function plus(LogootDel $ins){
        return new LogootPlusDel($ins->getLogootPosition(), $ins->getLineContent());
    }

}

?>
