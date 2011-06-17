<?php

/**
 * Model of a wiki page.
 * Represented by a list of page's lines and a list of the logootPositions
 * associated to each line
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author Muller Jean-Philippe, emmanuel Desmontils
 */
class boModel {
    protected $positionList = array();
    protected $lineList = array();

    public function  __construct() {
        $this->lineList = array("","");
        $this->positionList = array(LogootPosition::minPosition(),LogootPosition::maxPosition());
    }
    
    public function  __call($name, $arguments) {
        wfDebugLog('p2p', $this->clock . ' - function unknown '.$name." / ".$arguments);
        exit();
    }

    public function  __get($name) {
         wfDebugLog('p2p', $this->clock . ' - field unknown '.$name);
         exit();
    }

    public function  __set($name, $value) {
        wfDebugLog('p2p', $this->clock . ' - field unknown '.$name." / ".$value);
        exit();
    }

    public function setPositionlist($positionList) {
        $this->positionList = $positionList;
    }

    public function setLinelist($lineList) {
        $this->lineList = $lineList;
    }

    public function getPositionlist() {
        return $this->positionList;
    }

    public function getLinelist() {
        return $this->lineList;
    }

    /**
 * transforms the text array into a string
 * @return <String>
 */
    public function getText(){
        $textImage="";
        $tmp = $this->lineList;
        $nb=0;

        $nb = sizeof($tmp)-2;
        for($i=1; $i<$nb; $i++){
            $textImage .= $tmp[$i]."\n";
        }
        if ($nb>0) $textImage .= $tmp[$nb];
        return $textImage;
    }

    public function  __toString() {
         $s = "- Page mémorisée -\n";
         for($i=0; $i<sizeof($this->lineList);$i++) {
             $s .= "Line ($i) : ".$this->positionList[$i]
                     . " ; Content : '".$this->lineList[$i]."'\n";
         }
         $s .= "--\n";
         return $s;
    }
}
?>
