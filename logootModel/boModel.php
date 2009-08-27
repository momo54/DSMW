<?php

/**
 * Model of a wiki page.
 * Represented by a list of page's lines and a list of the logootPositions
 * associated to each line
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author Muller Jean-Philippe
 */
class boModel {
    private $positionList = array();
    private $lineList = array();
    
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

        $nb = sizeof($tmp);
        for($i=1; $i<=$nb; $i++){

            if($i==1) $textImage = $tmp[$i];
            else $textImage = $textImage."\n".$tmp[$i];
        }
        return $textImage;
    }
}
?>
