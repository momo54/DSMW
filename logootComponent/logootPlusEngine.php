<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of logootPlusEngine
 *
 * @author emmanuel Desmontils
 */
class logootPlusEngine extends logootEngine implements logootPlus {

    public function __construct($model, $session, $clock = 0) {
        if (isset($model))
            parent::__construct($model, $session, $clock);
        else
            parent::__construct(new boModelPlus(), $session, $clock);
        wfDebugLog('p2p', $this->clock . ' - function logootPlusEngine::__construct ');
    }

    /**
     *
     * @param <array or object> $opList operation list or operation object
     */
    public function deliver(LogootOperation $operation) {
        $id = $operation->getLogootPosition();
        wfDebugLog('p2p', $this->clock . ' - function logootPlusEngine::deliver '
                . get_class($operation)."(".$id .")");
        $id = $operation->getLogootPosition();
        list($min, $val, $max) = $this->dichoSearch($id);
        if (($operation->type() == LogootOperation::INSERT) && ($val == NULL)) {
            $dvis = $this->model->getCemetery($id) + 1;
            if ($dvis == 1) {
                $this->addPosition($max, $id);
                $this->addLine($max, $operation->getLineContent());
            } else
                $this->model->setCemetery($id, $dvis);
        } elseif ($operation->type() == LogootOperation::DELETE) {
            if (isset($val)) {
                $this->deletePosition($val);
                $this->deleteLine($val);
                $dvis = 0;
            } else
                $dvis = $this->model->getCemetery($id) - 1;
            $this->model->setCemetery($id, $dvis);
        } else {
            wfDebugLog('p2p', $this->clock . '- '.__METHOD__.' - '.__CLASS__.'- unkown operation !!!');
            exit();
        }
    }

    public function generate($oldText, $newText) {
        $patch = parent::generate($oldText, $newText);
        $opList = array();
        foreach ($patch as $op) {
            if ($op instanceof LogootIns)
                $opList[] = LogootPlusIns::plus($op);
            elseif ($op instanceof LogootDel)
                $opList[] = LogootPlusDel::plus($op);
        }
        $p = new LogootPatch($patch->getId(), $opList);
        $p->applied();
        return $p;
    }

    // annule les opérations d'un patch et construit un patch spécifique.
    public function undoPatch(LogootPatch $patch) {
        $this->clock = utils::getNextClock();
        wfDebugLog('p2p', $this->clock . ' - function logootPlusEngine::undoPatch ');
        $opList = array();
        foreach ($patch as $op) {
            $opList[] = LogootPlusOperation::inv($op);
        }
        $undoPatch = new LogootPatch($this->sessionid . $this->clock, $opList);
        $undoPatch->setRefPatch($patch->getId());
        return $undoPatch;
    }
}

?>
