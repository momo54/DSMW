<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogootPlusIns
 *
 * @author emmanuel Desmontils
 */
class LogootPlusIns extends LogootPlusOperation {

    public function __construct($position, $content) {
        parent::__construct($position, $content);
    }

    public function __clone() {
        $newOp = new LogootPlusIns(clone $this->mLogootPosition, $this->mLineContent);
        if ($this->isInv) $newOp->setInv();
        return $newOp;
    }
    
    public function type() {
        if ($this->isInv)
            return LogootOperation::DELETE;
        else
            return LogootOperation::INSERT;
    }

    public static function plus(LogootIns $ins){
        return new LogootPlusIns($ins->getLogootPosition(), $ins->getLineContent());
    }
}

?>
