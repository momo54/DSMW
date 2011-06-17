<?php

/**
 * Implementation of the logoot algorithm
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author jean-Philippe Muller, emmanuel Desmontils
 */
class logootEngine implements logoot {

    protected $model;
    protected $clock;
    protected $sessionid;

    function __construct($model, $session = "0", $clock = 0) {
        $this->clock = $clock; //0;
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::__construct ');
        if (isset($model))
            $this->model = $model;
        else
            $this->model = manager::getNewBoModel();
        $this->sessionid = $session;
    }

    public function __call($name, $arguments) {
        wfDebugLog('p2p', $this->clock . ' - logootEngine function unknown ' . $name . " / " . $arguments);
        exit();
    }

    public function __get($name) {
        wfDebugLog('p2p', $this->clock . ' - logootEngine get field unknown ' . $name);
        exit();
    }

    public function __set($name, $value) {
        wfDebugLog('p2p', $this->clock . ' - logootEngine set field unknown ' . $name . " / " . $value);
        exit();
    }

    public function compute($XMLfile) {
        $doc = simplexml_load_file($XMLfile);

        foreach ($doc->patch_list->patch as $patch) {
            $tab = array();
            $last = 0;
            $i = 0;
            foreach ($patch->children() as $op) {
                $rang = (int) $op["position"];
                if ($op->getName() == "insert") {
                    $tab[$rang]["insert"][] = $op;
                } else
                    $tab[$rang]["delete"][] = $op;
            }
            $delta = 0;
            $this->clock = utils::getNextClock();
            foreach ($tab as $rang => $liste_op) {
                $ligne = $rang + 1 + $delta;
                $nb_ins = isset($liste_op["insert"]) ? count($liste_op["insert"]) : 0;
                $nb_del = isset($liste_op["delete"]) ? count($liste_op["delete"]) : 0;
                for ($i = 0; $i < $nb_del; $i++) {
                    $logoot_op = $this->generate_del_line($ligne, $liste_op["delete"][$i]);
                }
                if ($nb_ins > 0) {
                    $logoot_op_list = $this->generate_ins_text($ligne, $liste_op["insert"]);
                }
                $delta += $nb_ins - $nb_del;
            }
        }
    }

    /**
     *
     * @param <array or object> $opList operation list or operation object
     */
    public function integrate(LogootPatch $patch) {
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::integrate ');
        if (!$patch->isApplied()) {
            foreach ($patch as $op)
                $this->deliver($op);
            $patch->applied();
        }
        return $patch;
    }

    /**
     *
     * @param <array or object> $opList operation list or operation object
     */
    public function deliver(LogootOperation $operation) {
        $id = $operation->getLogootPosition();
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::deliver '
                . get_class($operation) . "(" . $id . ")");
        list($min, $val, $max) = $this->dichoSearch($id);
        if ($operation instanceof LogootIns) {
            $this->addPosition($max, $id);
            $this->addLine($max, $operation->getLineContent());
        } elseif ($operation instanceof LogootDel) {
            if (isset($val)) {
                $this->deletePosition($val);
                $this->deleteLine($val);
            }
        } else {
            wfDebugLog('p2p', $this->clock . '- ' . __METHOD__ . ' - ' . __CLASS__ . '- unkown operation !!!');
            exit();
        }
    }

    /**
     * Calculate the diff between two texts
     * Returns a list of operations applied on this blobinfo(document model)
     * For each operation (insert or delete), an operation object is created
     * an applied via the 'integrateBlob' function call. These objects are
     *  stored in an array and returned for further implementations.
     *
     * NB: the direct implementation is necessary because the generation of
     * a new position (LogootPosition) is based on the positions of the model
     * (BlobInfo) and so we have to update (immediat integration) this model after
     * each operation (that we get from the difference engine)
     * @global <Object> $wgContLang
     * @param <String> $oldtext
     * @param <String> $newtext
     * @return <array> list of logootOperation
     */
    protected function generate_del_line($line_nb, $line_txt) {
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate_del_line ' . $line_nb . '/' . $line_txt . '/');
        $position = $this->getPosition($line_nb);
        $LogootDel = new LogootDel($position, $line_txt, $this->clock);
        $this->deliver($LogootDel);
        return $LogootDel;
    }

    protected function generate_ins_line($line_nb, $line_txt) {
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate_ins_line ' . $line_nb . '/' . $line_txt . '/');
        list($start, $end) = $this->getPrevNextPosition($line_nb);
        $positions = LogootPosition::getLogootPosition($start, $end, 1, $this->sessionid, $this->clock, BOUNDARY);
        $LogootIns = new LogootIns($positions[0], $line_txt);
        $this->deliver($LogootIns);
        return $LogootIns;
    }

    protected function generate_ins_text($line_nb, $txt) {
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate_ins_text ' . $line_nb . ' ' . $txt);
        list($start, $end) = $this->getPrevNextPosition($line_nb);
        $positions = LogootPosition::getLogootPosition($start, $end, count($txt), $this->sessionid, $this->clock, BOUNDARY);

        $LogootInsList = new LogootPatch($this->sessionid . $this->clock);
        for ($i = 0; $i < count($txt); $i++) {
            $LogootIns = new LogootIns($positions[$i], $txt[$i]);
            $this->deliver($LogootIns);
            $LogootInsList->add($LogootIns);
        }
        $LogootInsList->applied();
        return $LogootInsList;
    }

    protected function locate($ota, $nta) {//recherche si $ota est inclus dans $nta
        $ok = false;
        $in = false;
        $debut = -1;
        $fin = -1;
        $lota = count($ota);
        $lnta = count($nta);
        if ($lota < $lnta) {
            $oi = 0;
            $ni = 0;
            while ($ni+$lota-$oi <= count($nta) && (!$ok)) {
                //echo $ota[$oi] . '/' . $nta[$ni] . '/' . ($in ? 'in' : 'out') . "\n";
                if ($ota[$oi] == $nta[$ni]) {
                    if (!$in) {
                        $debut = $ni;
                        $in = true;
                        $fin = $ni;
                    } else {
                        $fin = $ni;
                    }
                    $oi +=1;
                    $ok = ($oi == $lota);
                } elseif ($ota[$oi] != $nta[$ni]) {
                    if ($in) {
                        $debut = -1;
                        $in = false;
                        $fin = -1;
                        $oi = 0;
                    }
                }
                $ni +=1;
                //echo $ni . "::" . count($nta) . "::" . ($ok ? "ok" : "!ok") . '/' . ($in ? 'in' : 'out') . "\n";
            }
        }
        wfDebugLog('p2p', $this->clock . " - function logootEngine::locate -> " . ($ok ? "ok" : "!ok") . ", $debut, $fin ");
        return array($ok, $debut, $fin);
    }

    public function generate($oldText, $newText) {
        $this->clock = utils::getNextClock();
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate ');
        /* explode into lines */
        $ota = explode("\n", $oldText);
        $nta = explode("\n", $newText);
        $counter = 0;

        if ((count($ota) == 1) && ($ota[0] == "")) {// c'est un nouveau document
            unset($ota[0]);
            wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate - création ');
            $listOp = $this->generate_ins_text(1, $nta);
            return $listOp;
        } else {

            list($trouve, $deb, $fin) = $this->locate($ota, $nta);
            if ($trouve) {//il y a eu un ajout de texte au début et/ou à la fin uniquement
                $listOp = new LogootPatch($this->sessionid . $this->clock);
                if ($deb > 0) {
                    wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate - ajout au début ');
                    $listOp1 = $this->generate_ins_text(1, array_slice($nta, 0, $deb));
                    $listOp->addPatch($listOp1);
                    $delta = $listOp1->size();
                } else $delta = 0;
                if ($fin + 1 < count($nta)) {
                    wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate - ajout à la fin ');
                    $listOp2 = $this->generate_ins_text(count($ota) + 1 + $delta, array_slice($nta, $fin + 1, count($nta) - ($fin + 1)));
                    $listOp->addPatch($listOp2);
                }
                return $listOp;
            } else {

                $listOp = new LogootPatch($this->sessionid . $this->clock);
                $diffs = new Diff1($ota, $nta);
                /* convert 4 operations into 2 operations */
                foreach ($diffs->edits as $operation) {
                    switch ($operation->type) {
                        case "add":
                            $adds = $operation->closing;
                            ksort($adds, SORT_NUMERIC);
                            foreach ($adds as $lineNb => $linetxt) {
                                wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate - Ajout ' . $linetxt . " (" . $lineNb . ")");
                                $listOp->add($this->generate_ins_line($lineNb, $linetxt));
                                $counter += 1;
                            }
                            break;
                        case "delete":
                            foreach ($operation->orig as $lineNb => $linetxt) {
                                wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate - Suppression ' . $linetxt . " (" . $lineNb . ")");
                                $listOp->add($this->generate_del_line($lineNb + $counter, $linetxt));
                                $counter -= 1;
                            }
                            break;
                        case "change":
                            foreach ($operation->orig as $lineNb => $linetxt) {
                                wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate - Change/Suppression ' . $linetxt . " (" . $lineNb . ")");
                                $listOp->add($this->generate_del_line($lineNb + $counter, $linetxt));
                                $counter -= 1;
                            }
                            $adds1 = $operation->closing;
                            ksort($adds1, SORT_NUMERIC);
                            foreach ($adds1 as $lineNb => $linetxt) {
                                wfDebugLog('p2p', $this->clock . ' - function logootEngine::generate - Change/Ajout ' . $linetxt . " (" . $lineNb . ")");
                                $listOp->add($this->generate_ins_line($lineNb, $linetxt));
                                $counter += 1;
                            }
                            break;
                        case "copy": break;
                        default :;
                    }
                }
                $listOp->applied();
                return $listOp;
            }
        }
    }

    /**
     * to get the previous and the next position (logootPosition)
     * @param <Integer> $lineNumber
     * @return <array> 2 LogootPosition
     */
    private function getPrevNextPosition($lineNumber) {
        $listIds = $this->model->getPositionList();
        return array($listIds[$lineNumber - 1], $listIds[$lineNumber]);
    }

    /**
     * to get a position
     * @param <Integer> $lineNumber
     * @return <Object> logootPosition
     */
    protected function getPosition($lineNumber) {
        $listIds = $this->model->getPositionlist();
        return $listIds[$lineNumber];
    }

    /**
     * to add a position the model
     * @param <Integer> $lineNumber
     * @param <Object> $position
     */
    protected function addPosition($lineNumber, LogootPosition $position) {
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::' . __METHOD__ . " " . $lineNumber);
        $listIds = $this->model->getPositionlist();
        //position shifting
        $nbLines = count($listIds);

        for ($i = $nbLines; $i >= $lineNumber; $i--) {
            $listIds[$i] = $listIds[$i - 1];
        }
        unset($listIds[$lineNumber]);
        $listIds[$lineNumber] = $position;

        $this->model->setPositionlist($listIds);
    }

    /**
     * to add a line to the model
     * @param <Integer> $lineNumber
     * @param <Object> $line
     */
    protected function addLine($lineNumber, $line) {
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::' . __METHOD__ . " " . $lineNumber);
        $listLines = $this->model->getLinelist();
        //position shifting
        $nbLines = count($listLines);

        for ($i = $nbLines; $i >= $lineNumber; $i--) {
            $listLines[$i] = $listLines[$i - 1];
        }
        unset($listLines[$lineNumber]);
        $listLines[$lineNumber] = $line;

        $this->model->setLinelist($listLines);
        wfDebugLog('p2p', $this->clock . ' - line added ' . $line . "/" . $this->size());
    }

    /**
     * to delete a position in the model
     * @param <Integer> $lineNb
     */
    protected function deletePosition($lineNb) {
        $model = $this->model->getPositionlist();
        for ($i = $lineNb + 1; $i < count($model); $i++) {
            $model[$i - 1] = $model[$i];
        }
        unset($model[count($model) - 1]);
        $this->model->setPositionlist($model);
    }

    /**
     * to delete a line in the blobInfo (the model)
     * @param <Integr> $lineNb
     */
    protected function deleteLine($lineNb) {
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::' . __METHOD__ . " " . $lineNb);
        $model = $this->model->getLinelist();
        for ($i = $lineNb + 1; $i < count($model); $i++) {
            $model[$i - 1] = $model[$i];
        }
        unset($model[count($model) - 1]);
        $this->model->setLinelist($model);
        wfDebugLog('p2p', $this->clock . ' - line suppressed ' . $lineNb . "/->" . $this->size());
    }

    /**
     * adapted binary search
     * -> an array with both positions in the array surrounding $position
     * @param <Object> $position LogootPosition
     * @return <array or Integer>
     */
    protected function dichoSearch(LogootPosition $position) {
        wfDebugLog('p2p', $this->clock . ' - function logootEngine::dichoSearch ');
        $arr = $this->model->getPositionlist();
        //avec les fausses lignes de début et de fin, on est certain
        //qu'il y a au moins deux lignes dans le tableau !
        $gauche = 0;
        $droite = count($arr) - 1;
        $val = NULL; // pas trouvée !
        if (count($arr) > 2) {
            $centre = round(($droite + $gauche) / 2);
            while ($centre != $droite && $centre != $gauche && (!isset($val))) {
                if ($position->compareTo($arr[$centre]) == -1) {
                    $droite = $centre;
                    $centre = floor(($droite + $gauche) / 2);
                }
                if ($position->compareTo($arr[$centre]) == 1) {
                    $gauche = $centre;
                    $centre = round(($droite + $gauche) / 2);
                }
                if ($position->compareTo($arr[$centre]) == 0) {
                    $val = $centre;
                }
            }
        } else {/* with an array=2 */
            if ($position->compareTo($arr[$gauche]) == 0)
                $val = $gauche;
            elseif ($position->compareTo($arr[$droite]) == 0)
                $val = $droite;
        }

        return array(0 => $gauche, 1 => $val, 2 => $droite);
    }

    /**
     * Size of the logootPosition array
     * @return <Integer>
     */
    private function size() {
        return count($this->model->getPositionlist());
    }

    /**
     *
     * @return <Object> model
     */
    public function getModel() {
        return $this->model;
    }

}

?>
