<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogootPatch
 *
 * @author emmanuel Desmontils
 */
class LogootPatch implements Iterator, ArrayAccess {

    private $listOp;
    private $patchID;
    private $applied;
    private $refPatch;

    // pour la simulation de tableau
    private $curOp;

    public function __construct($id, $opList = array()) {
        $this->curOp = 0;
        $this->listOp = $opList;
        $this->patchID = $id;
        $this->applied = false;
        $this->refPatch = -1;
    }

    public function  __call($name, $arguments) {
        wfDebugLog('p2p', ' - function unknown '.$name." / ".$arguments);
        exit();
    }

    public function  __get($name) {
        wfDebugLog('p2p', ' - field unknown '.$name);
         exit();
    }

    public function  __set($name, $value) {
        wfDebugLog('p2p', ' - field unknown '.$name." / ".$value);
        exit();
    }

    public function getRefPatch() {
        return $this->refPatch;
    }

    public function setRefPatch($refPatch) {
        $this->refPatch = $refPatch;
    }

    
    public function applied() {
        //$this->applied = true;
    }

    public function isApplied() {
        return $this->applied;
    }

    public function add(LogootOperation $op) {
        $this->listOp[] = $op;
    }

    public function addPatch(LogootPatch $p) {
        foreach($p as $i) $this->add($i);
    }

    public function  __toString() {
        $s ="--- Patch $this->patchID ($this->refPatch)---\n";
        foreach($this->listOp as $op) $s .= (string)$op;
        $s .= "\n--- ".($this->applied?"is applied":"is not applied")." ---\n";
        return $s;
    }

    public function size() {
        return count($this->listOp);
    }

    public function getId() {
        return $this->patchID;
    }
    
    public function setId($id) {
    	$this->patchID = $id;
    }
    
    // Fonctions pour manipuler un patch avec foreach : Iterator

    public function current() {
        return $this->listOp[$this->curOp];
    }

    public function next() {
        $this->curOp++;
    }

    public function valid() {
        return (($this->curOp>=0) && ($this->curOp < count($this->listOp)));
    }

    public function key() {
        return $this->curOp;
    }

    public function rewind() {
         $this->curOp = 0;
    }

    // Fonctions pour manipuler un patch comme un tableau : ArrayAccess

    public function offsetExists($offset) {
        return ($offset >= 0) && ($offset < count($this->listOp));
    }

    public function offsetGet($offset) {
        return $this->listOp[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->listOp[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->listOp[$offset]);
    }

}

?>
