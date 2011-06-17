<?php
/**
 * Insertion operation used in the logoot algorithm
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe, emmanuel Desmontils
 */
class LogootIns extends LogootOperation {

    /**
     *
     * @param <Object> $position LogootPosition
     * @param <String> $content line content
     */
    function  __construct($position, $content) {
        parent::__construct($position, $content);
    }

    public function type() {
        return LogootOperation::INSERT;
    }

    public function __clone() {
        return new LogootIns(clone $this->mLogootPosition, $this->mLineContent);
    }
}
?>
