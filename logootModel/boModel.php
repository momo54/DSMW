<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of boModel
 *
 * @author CUCUTEANU
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
