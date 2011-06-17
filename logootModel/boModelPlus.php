<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of boModelPlus
 *
 * @author emmanuel Desmontils
 */
class boModelPlus extends boModel {

    private $cemetery = array();

    public function __construct() {
        parent::__construct();
    }

    public function getCemetery(LogootPosition $id) {
        $sid = $id->__toString();
        return isset($this->cemetery[$sid]) ? $this->cemetery[$sid] : 0;
    }

    public function setCemetery(LogootPosition $id, $vis = 1) {
        $sid = $id->__toString();
        if ($vis == 0)
            unset($this->cemetery[$sid]);
        else
            $this->cemetery[$sid] = $vis;
    }

    public function __toString() {
        $s = "- Page mémorisée (+) -\n";
        for ($i = 0; $i < sizeof($this->lineList); $i++) {
            $s .= "Line ($i) : " . $this->positionList[$i]
                    . " ; Content : '" . $this->lineList[$i]
                    . "'\n";
        }
        $s .= "- Lignes annulées (+) -\n";
        foreach ($this->cemetery as $id => $vis) {
            $s .= "Line : " . $id
                    . " ; visibility : " . $vis . "\n";
        }

        $s .= "--\n";
        return $s;
    }

}

?>
