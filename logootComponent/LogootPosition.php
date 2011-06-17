<?php

/**
 * logootPosition is an array of logootId(s) which is assigned to a line
 * of an article
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe, emmanuel Desmontils
 */
//require_once './logootComponent/Math/BigInteger.php';

if (!defined('LPINTMINDIGIT')) {
    define('LPINTMINDIGIT', str_pad(INT_MIN, DIGIT, '0', STR_PAD_LEFT));
}

class LogootPosition {

    private $mPosition = array();

    public function __toString() {
        $s = "[";
        for ($i = 0; $i < sizeof($this->mPosition); $i++)
            $s .= $this->mPosition[$i];
        return $s . "]";
    }

    // <ED> == uniquement pour passer les tests unitaires ==

    function vectorMinSizeComp($position) {
        if ($this->size() > $position->size()) {
            return $position->size();
        } elseif ($position->size() > $this->size()) {
            return $this->size();
        }
        else
            return $this->size();
    }

    /**
     * postion size comparison, returns size of the position(if same) or -1
     */
    function vectorSizeComp($position) {
        if ($this->size() > $position->size()) {
            return -1;
        } elseif ($position->size() > $this->size()) {
            return -1;
        }
        else
            return $this->size();
    }

    /**
     * id comparison
     */
    function equals($id1, $id2) {
        return ($id1->compareTo($id2) == 0);
    }

    /**
     * position comparison (n ids position)
     */
    function nEquals($position) {

        //length test
        $eq = 1;
        $size = $this->vectorSizeComp($position);

        if ($size == -1) {//different size
            $eq = 0;
        }//end if sizecomp
        else {//same size
            for ($i = 0; $i < $size; $i++) {
                if (!$this->equals($this->mPosition[$i], $position->mPosition[$i])) {
                    $eq = 0;
                }
            }//end for
        }
        return $eq;
    }

    /**
     * id comparison
     */
    function greaterThan($id1, $id2) {
        return ($id1->compareTo($id2) == 1);
    }

    /**
     * position comparison (n ids position)
     */
    function nGreaterThan($position) {

        $lt = 0;
        $size = $this->vectorMinSizeComp($position); //size of the smallest vector

        for ($i = 0; $i < $size; $i++) {

            if ($this->greaterThan($this->mPosition[$i], $position->mPosition[$i])) {
                $lt = 1;
            }
        }//end for

        if ($lt == 0) {
            if ($this->size() > $position->size()) {
                $lt = 1;
            }
        }
        return $lt;
    }

    //id comparison
    function lessThan($id1, $id2) {
        return ($id1->compareTo($id2) == -1);
    }

    /**
     * position comparison (n ids position)
     */
    function nLessThan($position) {

        $lt = 0;
        $eq = 0;
        $size = $this->vectorMinSizeComp($position); //size of the smallest vector

        for ($i = 0; $i < $size; $i++) {

            if ($this->lessThan($this->mPosition[$i], $position->mPosition[$i])) {
                $lt = 1;
            }
            if ($this->equals($this->mPosition[$i], $position->mPosition[$i])) {
                $eq = 1;
            }
        }//end for

        if ($lt == 0) {
            if ($eq == 1 && $this->equals($this->mPosition[$size - 1], $position->mPosition[$size - 1])) {
                if ($position->size() > $this->size()) {
                    $lt = 1;
                }
            }
        }

        return $lt;
    }

    // </ED> ===============================================


    public function __call($name, $arguments) {
        wfDebugLog('p2p', ' - Position function unknown ' . $name . " / " . $arguments);
        exit();
    }

    public function __get($name) {
        wfDebugLog('p2p', ' - Position field unknown (get) ' . $name);
        exit();
    }

    public function __set($name, $value) {
        wfDebugLog('p2p', ' - Position field unknown (set) ' . $name . " / " . $value);
        exit();
    }

    public static function minPosition() {
        return new LogootPosition(array(LogootId::IdMin()));
    }

    public static function maxPosition() {
        return new LogootPosition(array(LogootId::IdMax()));
    }

    public function __construct($pos=NULL) {
        if (isset($pos))
            $this->mPosition = $pos;
        else
            $this->mPosition = array(); //LogootPosition::minPosition();

    }

    public function compareTo(LogootPosition $position) {
        $i = 0;
        $thisPos = $this->mPosition;
        $max = min($this->size(), $position->size());
        $cmp = 0;
        $ok = false;
        while (($i < $max) && (!$ok)) {
            if ($thisPos[$i]->compareTo($position->mPosition[$i]) != 0)
                $ok = true;
            else
                $i++;
        }

        if ($i >= $this->size() && $i >= $position->size())
            $cmp = 0;
        else if ($i >= $this->size())
            $cmp = -1;
        else if ($i >= $position->size())
            $cmp = 1;
        else
            $cmp= $thisPos[$i]->compareTo($position->mPosition[$i]);
        return $cmp;
    }

    public function get($i) {// returns a logootId
        if ($i < $this->size())
            return $this->mPosition[$i];
        else
            return LogootId::IdMin();
    }

    public function set($pos, $value, $sid, $clock = 0) {
        if ($pos < $this->size()) {
            unset($this->mPosition[$pos]);
            $this->mPosition[$pos] = new LogootId($value, $sid, $clock);
        } else {
            $this->mPosition[] = new LogootId($value, $sid, $clock);
        }
    }

    public function addId(LogootId $id) {
        $this->mPosition[] = $id;
    }

    public function size() {
        return count($this->mPosition);
    }

    public function __clone() {
        $locPos = array();
        for ($i = 0; $i < sizeof($this->mPosition); $i++)
            $locPos[] = clone ($this->mPosition[$i]);
        return new LogootPosition($locPos);
    }

    function toString() {
        $string = "";
        foreach ($this->mPosition as $id) {
            $string.=$id->toString() . " ";
        }
        //$string.=$this->mClock." ".$this->mVisibility;
        return $string;
    }

    /**
     * generation of a position, logoot algorithm
     * @param <LogootPosition> $p is the previous logootPosition
     * @param <LogootPosition> $q is the next logootPosition
     * @param $N number of positions generated (should be 1 in our case)
     * @param <Integer> $rep_sid session id
     * @param <Integer> $rep_clock session clock
     * @param $boundary Cf. method
     * @return <LogootPosition List> $N logootPosition(s) between $start and $end
     */
    static public function getLogootPosition(LogootPosition $p, LogootPosition $q, $nb, $rep_sid, $rep_clock=0, $boundary=NULL) {
        wfDebugLog('p2p', $rep_clock . " - function LogootPosition::getLogootPosition "
                . $p . " / " . $q . " pour " . $nb . " position(s)");
        $one = new Math_BigInteger("1");

        // Recherche de l'interval optimal
        $index = 0;
        $interval = INT_MIN;
        $size = max($p->size(), $q->size()) + 1;

        $prefix_p = array(0 => array('cum_val' => "", 'id_str_val' => ""));
        $prefix_q = array(0 => array('cum_val' => "", 'id_str_val' => ""));

        while ($interval < $nb) {
            $index += 1;

            // recherche de prefix($p, index);
            if ($index <= $p->size())
                $str_val_p = str_pad($p->get($index - 1)->getInt() ,
                                     DIGIT, "0", STR_PAD_LEFT);
            else $str_val_p = LPINTMINDIGIT;
            $prefix_p[$index] = array(
                'id_str_val' => $str_val_p,
                'cum_val' => $prefix_p[$index - 1]['cum_val'] . $str_val_p
            );

            // recherche de prefix($p, index);
            if ($index <= $q->size())
                $str_val_q = str_pad($q->get($index - 1)->getInt() ,
                                     DIGIT, "0", STR_PAD_LEFT);
            else $str_val_q = LPINTMINDIGIT;
            $prefix_q[$index] = array(
                'id_str_val' => $str_val_q,
                'cum_val' => $prefix_q[$index - 1]['cum_val'] . $str_val_q
            );

            // Calcul de l'interval sur les nouveaux prefixes
            $BI_p = new Math_BigInteger($prefix_p[$index]['cum_val']);
            $BI_q = new Math_BigInteger($prefix_q[$index]['cum_val']);
            $BIinterval = $BI_q->subtract($BI_p)->subtract($one);
            $interval = (integer) $BIinterval->__toString();
            /*wfDebugLog('p2p', $index
                    . " : Prefix_p " . (string) $prefix_p[$index]['cum_val'] . '/'
                    . $prefix_p[$index]['id_str_val']
                    . " Prefix_q " . (string) $prefix_q[$index]['cum_val'] . '/'
                    . $prefix_q[$index]['id_str_val']
                    . " Interval " . $interval);*/
        }

        // Construction des identifiants
        //wfDebugLog('p2p', "N " . $nb . " Interval " . $interval . " index " . $index);
        $step = (integer) $interval / $nb;
        if (isset($boundary))
            $step = $boundary < $step ? $boundary : $step;
        $BI_step = new Math_BigInteger($step);
        $BI_r = new Math_BigInteger($prefix_p[$index]['cum_val']);
        $list = array();
        //wfDebugLog('p2p', "Step :" . $step . "/" . $boundary);

        for ($j = 1; $j <= $nb; $j++) {

            $BI_nr = $BI_r->add(new Math_BigInteger(rand(1,$step)));
            //wfDebugLog('p2p', "nr " . (string) $BI_nr . " r " . (string) $BI_r);

            // pour découper une chaine en paquets de N car : str_split($cdc, $N) !
            $str_nr0 = (string) $BI_nr;

            // on fait en sorte que le découpage soit un multiple de DIGIT pour ne pas créer de décallage
            if (strlen($str_nr0) % ($index * DIGIT) != 0)
                $str_nr = str_pad($str_nr0, strlen($str_nr0) + (($index * DIGIT) - strlen($str_nr0) % ($index * DIGIT)), "0", STR_PAD_LEFT);
            else
                $str_nr = $str_nr0;

            //wfDebugLog('p2p', "str_nr0 " . $str_nr0 . " str_nr " . $str_nr);
            $tab_nr = str_split($str_nr, DIGIT);
            $pos = new LogootPosition();

            for ($i = 1; $i <= count($tab_nr); $i++) {
                $d = $tab_nr[$i - 1];
                //wfDebugLog('p2p', "$i#" . $prefix_p[$i]['id_str_val'] . "#" . $prefix_q[$i]['id_str_val'] . "#" . $d);
                if (($i <= $p->size()) && ($prefix_p[$i]['id_str_val'] == $d))
                    $id = new LogootId($d, $p->get($i - 1)->getSessionId(), $p->get($i - 1)->getClock());
                elseif (($i <= $q->size()) && ($prefix_q[$i]['id_str_val'] == $d))
                    $id = new LogootId($d, $q->get($i - 1)->getSessionId(), $q->get($i - 1)->getClock());
                else
                    $id = new LogootId($d, $rep_sid, $rep_clock);
                $pos->addId($id);
            }

            wfDebugLog('p2p', "===========>" . $pos->__toString());
            $list[] = $pos;
            $BI_r = $BI_r->add($BI_step);
        }
        return $list;
    }

}

?>
