<?php

/**
 * Deletion operation used in the logoot algorithm
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe, emmanuel Desmontils
 */
class LogootDel extends LogootOperation {

    /**
     *
     * @param <Object> $position LogootPosition
     * @param <String> $content line content 
     */
    function  __construct(LogootPosition $position, $content) {
        parent::__construct($position, $content);
    }

    public function type() {
        return LogootOperation::DELETE;
    }

    public function __clone() {
        return new LogootDel(clone $this->mLogootPosition, $this->mLineContent);
    }
}
?>
