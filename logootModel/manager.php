<?php

/**
 * Used to seperated the data access layer
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author CUCUTEANU
 */
class manager {

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
                return new boModel();
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
    static function storeModel($rev_id, $sessionId, $model, $blobCB){
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
