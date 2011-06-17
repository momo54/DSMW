<?php

/**
 * logootId is used to compose a logootPosition, necessary for the
 * logoot algorithm
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe, emmanuel Desmontils
 */
class LogootId {
    private $mInt,$mStr;
    private $mSessionId;
    private $mClock;

    public function __construct($int, $sessionId, $clock = 0) {
        $this->setInt($int);
        $this->mStr = ((string)$this->mInt==""?"0":(string)$this->mInt);
        $this->setSessionId($sessionId);
        $this->setClock($clock);
    }

    public function __call($name, $arguments) {
        echo('p2p'. ' - LogootId function unknown ' . $name . " / " . $arguments);
        exit();
    }

    public function __get($name) {
        echo('p2p'. ' - LogootId get field unknown ' . $name);
        exit();
    }

    public function __set($name, $value) {
        echo('p2p'. ' - LogootId set field unknown ' . $name . " / " . $value);
        exit();
    }

    public static function IdMin(){
        $IdMin = new LogootId(INT_MIN, SESSION_MIN, CLOCK_MIN);
        return $IdMin;
    }

    public static function IdMax(){
        $IdMax = new LogootId(INT_MAX, SESSION_MAX, CLOCK_MAX);
        return $IdMax;
    }

    public function getSessionId(){
        return $this->mSessionId;
    }

    public function setSessionId($sessionId){
        $this->mSessionId = $sessionId;
    }

    public function getInt(){
        return $this->mInt;
    }

    public function setInt($int){
        $this->mInt = (integer)$int ;
    }

    public function getClock(){
        return $this->mClock;
    }

    public function setClock($int){
        $this->mClock = $int;
    }

    public function compareTo(LogootId $id){
        $logid = $id;

        $val1 = $this->mInt;
        $val2 = $logid->mInt;

        if ($val1 < $val2) $cmp = -1;
        else if ($val1 > $val2) $cmp = 1;
        else if(strcmp($this->mSessionId, $logid->mSessionId)<0) $cmp = -1;
        else if(strcmp($this->mSessionId, $logid->mSessionId)>0) $cmp = 1;
        else if(strcmp($this->mClock, $logid->mClock)<0) $cmp = -1;
        else if(strcmp($this->mClock, $logid->mClock)>0) $cmp = 1;
        else $cmp = 0;
        return $cmp;
    }

    public function toString(){
        return "(".$this->mStr.":".$this->mSessionId.":".$this->mClock.")";
    }

    public function  __toString() {
        return $this->toString();
    }

    public function __clone(){
        return new LogootId($this->mInt, $this->mSessionId, $this->mClock);
    }
}
?>
