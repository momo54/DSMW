<?php

/**
 * DAO used to load and store the boModel
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author Jean-Philippe Muller
 */
class dao {
    
    /**
 * To get the model of the given revision
 * --> A model is the logootPosition array corresponding to this revision
 * @param <Integer> $rev_id
 * @return <Object> model object
 */
    function loadModel($rev_id){
        wfProfileIn( __METHOD__ );
        $dbr = wfGetDB( DB_SLAVE );
        $model1 = $dbr->selectField('model','blob_info', array(
        'rev_id'=>$rev_id), __METHOD__);
        if ($model1===false)
            throw new MWException( __METHOD__.': This page has not been processed by DSMW' );
        wfProfileOut( __METHOD__ );
        $model = unserialize($model1);
        return $model;
    }

/**
     * integrate model to DB
     * @param <Integer> $rev_id
     * @param <String> $sessionId
     * @param <object> $model
     * @param <Object> $blobCB (should have been a causal barrier object but
     * not used yet)
     */
    function storeModel($rev_id, $sessionId, $model, $blobCB){

        $model1 = serialize($model);

        wfProfileIn( __METHOD__ );
        $dbw = wfGetDB( DB_MASTER );
        $dbw->insert( 'model', array(
            'rev_id'        => $rev_id,
            'session_id'    => $sessionId,
            'blob_info'     => $model1,
            'causal_barrier'  => $blobCB,
            ), __METHOD__ );


        wfProfileOut( __METHOD__ );
    }

}
?>
