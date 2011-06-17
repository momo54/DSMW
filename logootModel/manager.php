<?php

/**
 * Used to seperated the data access layer
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author CUCUTEANU
 */
 
if (!defined('LOGOOTMODE')) {
    //define('LOGOOTMODE', 'STD');
    define('LOGOOTMODE', 'PLS');
}

class manager {

    static function getNewEngine(boModel $model, $session = 0){//, $clock = null) {
        $le = null;
        switch (LOGOOTMODE) {
        	case 'PLS' : $le = new logootPlusEngine($model, $session, utils::getNextClock()); break;
        	case 'STD' : $le = new logootEngine($model, $session, utils::getNextClock()); break;
        }
        return $le;
    }

	static function getNewBoModel() {
        $le = null;
        switch (LOGOOTMODE) {
        	case 'PLS' : $le = new boModelPlus(); break;
        	case 'STD' : $le = new boModel(); break;
        }
        return $le;
	}

	static function getNewLogootIns($logootPos, $line) {
        $le = null;
        switch (LOGOOTMODE) {
        	case 'PLS' : $le = new LogootPlusIns($logootPos, $line); break;
        	case 'STD' : $le = new LogootIns($logootPos, $line); break;
        }
        return $le;
	}

	static function getNewLogootDel($logootPos, $line) {
        $le = null;
        switch (LOGOOTMODE) {
        	case 'PLS' : $le = new LogootPlusDel($logootPos, $line); break;
        	case 'STD' : $le = new LogootDel($logootPos, $line); break;
        }
        return $le;
	}
	
	/**
     *
     * @param <String> $rev_id Revision id
     * @return boModel
     */
    static function loadModel($rev_id) {
        try {
            if($rev_id!=0){
                $dao = new dao();
                return $dao->loadModel($rev_id);
            }
            else{
                return manager::getNewBoModel();
            }
        } catch (Exception $e) {
            throw new MWException( __METHOD__.' db access problems,
if this page existed before the DSMW installation,
maybe it has not been processed by DSMW' );
        }
    }

    /**
     *
     * @param <String> $rev_id
     * @param <String> $sessionId
     * @param <Object> $model boModel
     * @param <Object> $blobCB=0 (should have been a causal barrier object but
     * not used yet)
     */
    static function storeModel($rev_id, $sessionId, $model, $blobCB = 0){
        wfDebugLog('p2p',' -> store model into revid : '.$rev_id.' sessionid : '.$sessionId.' model : '.$model->getText());
        try {
            $dao = new dao();
            $dao->storeModel($rev_id, $sessionId, $model, $blobCB);
        } catch (Exception $e) {
            throw new MWException( __METHOD__.' db access problems' );
        }
    }
}
?>
