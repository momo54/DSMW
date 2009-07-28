<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of manager
 *
 * @author CUCUTEANU
 */
class manager {
    
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
            throw new MWException( __METHOD__.' db access problems' );
        }

    }

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
