<?php

/**
 * logootId is used to compose a logootPosition, necessary for the
 * logoot algorithm
 *
 * @author mullejea
 */
class LogootId {
    private $mInt;
    private $mSessionId;
    
    
    
    

    public function __construct($int, $sessionId/*, $clock*/) {
        $this->mInt = $int;
        $this->mSessionId = $sessionId;
        
    }

    public static function IdMin(){
        $IdMin = new LogootId(INT_MIN, INT_MIN/*, INT_MIN*/);
        return $IdMin;
    }

    public static function IdMax(){
        $IdMax = new LogootId(INT_MAX, INT_MAX/*, INT_MAX*/);
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
        $this->mInt = $int;
    }

    public function compareTo($id){
        $logid = $id;

        $val1 = $this->mInt;
        $val2 = $logid->mInt;

        if(gmp_cmp(gmp_init($val1), gmp_init($val2))<0)
        return -1;
        else if(gmp_cmp(gmp_init($val1), gmp_init($val2))>0)
        return 1;
        else if(strcmp($this->mSessionId, $logid->mSessionId)<0)
        return -1;
        else if(strcmp($this->mSessionId, $logid->mSessionId)>0)
        return 1;
        return 0;
    }

    public function toString(){
        return "< ".gmp_strval($this->mInt).",".$this->mSessionId." >";
    }

    public function __clone(){
        return new LogootId($this->mInt,$this->mSessionId);
    }

    public function gmpToStr(){
        return $this->setInt(gmp_strval($this->mInt));
    }

   
}
?>
