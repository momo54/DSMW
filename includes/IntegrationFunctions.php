<?php
/**
 * @author jean-philippe muller
 * @copyright INRIA-LORIA-ECOO project
 */

/**
 * A ChangeSet has patches which has operations
 * this function is used to integrate these operations
 * It's a local changeSet (downloaded from a remote site)
 * @param <String> $changeSetId with NS
 */
function integrate($changeSetId,$patchIdList,$relatedPushServer) {
//global $wgScriptExtension;
// $patchIdList = getPatchIdList($changeSetId);
//  $lastPatch = utils::getLastPatchId($pageName);
    wfDebugLog('p2p',' - function integrate : '.$changeSetId);
    foreach ($patchIdList as $patchId) {
        wfDebugLog('p2p','  -> patchId : '.$patchId);
        if(!utils::pageExist($patchId)) {//if this patch exists already, don't apply it
            wfDebugLog('p2p','      -> patch unexist');
            $url = utils::lcfirst($relatedPushServer)."/api.php?action=query&meta=patch&papatchId="./*substr(*/$patchId/*,strlen('patch:'))*/.'&format=xml';
            wfDebugLog('p2p','      -> getPatch request url '.$url);
            $patch = utils::file_get_contents_curl($url);

            /*test if it is a xml file. If not, the server is not reachable via the url
             * Then we try to reach it with the .php5 extension
             */
            if(strpos($patch, "<?xml version=\"1.0\"?>")===false){
                $url = utils::lcfirst($relatedPushServer)."/api.php5?action=query&meta=patch&papatchId="./*substr(*/$patchId/*,strlen('patch:'))*/.'&format=xml';
                wfDebugLog('p2p','      -> getPatch request url '.$url);
                $patch = utils::file_get_contents_curl($url);
            }
            if(strpos($patch, "<?xml version=\"1.0\"?>")===false) $patch=false;

            if($patch===false) throw new MWException( __METHOD__.': Cannot connect to Push Server (Patch API)' );
            $patch = trim($patch);
            wfDebugLog('p2p','      -> patch content :'.$patch);
            $dom = new DOMDocument();
            $dom->loadXML($patch);

            $patchs = $dom->getElementsByTagName('patch');

            //when the patch is not found, mostly when the id passed
            //through the url is wrong
            if(empty ($patchs) || is_null($patchs))
            throw new MWException( __METHOD__.': Error: Patch not found!' );

            //        $patchID = null;
            foreach($patchs as $p) {
                if ($p->hasAttribute("onPage")) {
                    $onPage = $p->getAttribute('onPage');
                }
                if ($p->hasAttribute("previous")) {
                    $previousPatch = $p->getAttribute('previous');
                }
                if ($p->hasAttribute("siteID")) {
                    $siteID = $p->getAttribute('siteID');
                }
            }

            $operations = null;
            $op = $dom->getElementsByTagName('operation');
            foreach($op as $o)
                $operations[] = $o->firstChild->nodeValue;
            $lastPatch = utils::getLastPatchId($onPage);
            if ($lastPatch==false) $lastPatch='none';



            //            foreach ($operations as $operation) {
            //                $operation = operationToLogootOp($operation);
            //                if ($operation!=false && is_object($operation)) {
            //                    logootIntegrate($operation, $onPage);
            //                }
            //            }
            if(logootIntegrate($operations, $onPage)===true)
            {
             utils::createPatch($patchId, $onPage, $lastPatch, $operations, $siteID);
            }
            else{
                throw new MWException( __METHOD__.': article not saved!');
            }
    }//end if pageExists
    }
}

/**
 *transforms a string operation from a patch page into a logoot operation
 * insertion or deletion
 * returns false if there is a problem with the type of the operation
 *
 * @param <String> $operation
 * @return <Object> logootOp
 */
function operationToLogootOp($operation) {
    wfDebugLog('p2p',' - function operationToLogootOp : '.$operation);
    $arr = array();
    $res = explode(';', $operation);
    foreach ($res as $key=>$attr) {
        $res[$key] = trim($attr, " ");
    }

    $position = $res[2];
    $position = str_ireplace('(', '', $position);
    $position = str_ireplace(')', '', $position);
    $res1 = explode(' ', $position);
    foreach ($res1 as $id) {
        $id1 = explode(':', $id);
        $idArrray = new LogootId($id1[0], $id1[1]);
        $arr[] = $idArrray;
    }
    $logootPos = new LogootPosition($arr);

    //    if(strpos($res[3], '-5B-5B')!==false || strpos($res[3], '-5D-5D')!==false) {
    //        $res[3] = utils::decodeRequest($res[3]);
    //    }
    $res[3] = utils::contentDecoding($res[3]);
    //    if($res[3]=="") $res[3]="\r\n";

    if($res[1]=="Insert") {
        $logootOp = new LogootIns($logootPos, $res[3]);
    }
    elseif($res[1]=="Delete") {
        $logootOp = new LogootDel($logootPos, $res[3]);
    }
    else {
        $logootOp = false;
    }
    return $logootOp;
}

/**
 *Integrates the operation(LogootOp) into the article via the logoot algorithm
 *
 * @param <Object> $operation
 * @param <String or Object> $article
 */
function logootIntegrate($operations, $article) {
   global $wgCanonicalNamespaceNames;
   $indexNS=0;
   wfDebugLog('p2p',' - function logootIntegrate : '.$article);

    if(is_string($article)) {
        $dbr = wfGetDB( DB_SLAVE );
        //if there is a space in the title, repalce by '_'
        $article = str_replace(" ", "_", $article);

        if(strpos($article, ":")===false){
        $pageid = $dbr->selectField('page','page_id', array(
            'page_title'=>$article/*WithoutNS*/));
        }else{//if there is a namespace
        preg_match( "/^(.+?)_*:_*(.*)$/S", $article, $tmp );
        $articleWithoutNS = $tmp[2];
        $NS = $tmp[1];
        if(in_array($NS, $wgCanonicalNamespaceNames)){
            foreach ($wgCanonicalNamespaceNames as $key=>$value){
                if($NS==$value) $indexNS=$key;
            }
        }
        $pageid = $dbr->selectField('page','page_id', array(
            'page_title'=>$articleWithoutNS, 'page_namespace'=>$indexNS));
        }
        // get the page namespace
        $pageNameSpace = $dbr->selectField('page','page_namespace', array(
            'page_id'=>$pageid));

        /*the ns must not be a pullfeed, pushfeed, changeset or patch namespace.
         If the page name is the same in different ns we can get the wrong
         * page id
         */
        if($pageNameSpace==PULLFEED || $pageNameSpace==PUSHFEED ||
            $pageNameSpace==PATCH || $pageNameSpace==CHANGESET
        ) $pageid=0;


        $lastRev = Revision::loadFromPageId($dbr, $pageid);
        if(is_null($lastRev)) $rev_id = 0;
        else $rev_id = $lastRev->getId();
        wfDebugLog('p2p','      -> pageId : '.$pageid);
        wfDebugLog('p2p','      -> rev_id : '.$rev_id);
        $title = Title::newFromText($article);
        $article = new Article($title);
    }
    else {
        $rev_id = $article->getRevIdFetched();
    }
    $listOp = array();
    //$blobInfo = BlobInfo::loadBlobInfo($rev_id);
    $model = manager::loadModel($rev_id);
    if(($model instanceof boModel)==false)
    throw new MWException( __METHOD__.': model loading problem!');
    $logoot = new logootEngine($model);

    foreach ($operations as $operation) {
        wfDebugLog('p2p',' - operation : '.$operation);
        wfDebugLog('testlog',' - operation : '.$operation);
        $operation = operationToLogootOp($operation);

        if ($operation!=false && is_object($operation)) {
            $listOp[]=$operation;
                    wfDebugLog('testlog',' -> Operation: '.$operation->getLogootPosition()->toString());
        //$blobInfo->integrateBlob($operation);
    }//end if
    //    else {
    //        throw new MWException( __METHOD__.': operation problem '.$operation );
    //        wfDebugLog('p2p',' - operation problem : '.$operation);
    //    }
    }//end foreach operations
    $modelAfterIntegrate = $logoot->integrate($listOp);
    $revId = utils::getNewArticleRevId();
    manager::storeModel($revId, $sessionId=session_id(), $modelAfterIntegrate, $blobCB=0);
    $status = $article->doEdit($modelAfterIntegrate->getText(), $summary="");
    //sleep(4);
    if(is_bool($status)) return $status;
    else return $status->isGood();
}
?>
