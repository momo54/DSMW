<?php

//////////////////BEN//////////////////
//REALISED USING IntegrationFunctions.php by working from its code
//WORK IN PROGRESS

/**
 * MODIFY:
 * A ChangeSet has patches which has operations
 * this function is used to integrate these operations
 * It's a local changeSet (downloaded from a remote site)
 * @param <String> $changeSetId with NS
 */
function integrateUndo($patchIdList,$relatedPushServer/*, $csName*/) {
//global $wgScriptExtension;
// $patchIdList = getPatchIdList($changeSetId);
//  $lastPatch = utils::getLastPatchId($pageName);
    global $wgServerName,$wgScriptPath,$wgScriptExtension,$wgOut;
    //$urlServer = 'http://'.$wgServerName.$wgScriptPath."/index.php/$csName";
    //wfDebugLog('p2p', '@@@@@@@@@@@@@@@@@@@ - function integrateUndo : ' . $csName);
    $i = 1;
    $j = count($patchIdList);
    $pages = array();
    
    foreach ($patchIdList as $patchId) {
        $name = 'patch';
//        $sub = substr($patchId, 6, 3);
        wfDebugLog('p2p','  -> patchId : '.$patchId);
        
        if(utils::pageExist($patchId)) {//if this patch exists we can operate
            wfDebugLog('p2p','      -> patch exist');
            $url = utils::lcfirst($relatedPushServer)."/api.php?action=query&meta=patch&papatchId=".$patchId.'&format=xml';
            wfDebugLog('p2p','      -> getPatch request url '.$url);
            $patch = utils::file_get_contents_curl($url);

            /*test if it is a xml file. If not, the server is not reachable via the url
             * Then we try to reach it with the .php5 extension
            */
            if(strpos($patch, "<?xml version=\"1.0\"?>")===false) {
                $url = utils::lcfirst($relatedPushServer)."/api.php5?action=query&meta=patch&papatchId=".$patchId.'&format=xml';
                wfDebugLog('p2p','      -> getPatch request url '.$url);
                $patch = utils::file_get_contents_curl($url);
            }
            if(strpos($patch, "<?xml version=\"1.0\"?>")===false) $patch=false;

            if($patch===false) throw new MWException( __METHOD__.': Cannot connect to Server (Patch API)' );
            $patch = trim($patch);
            wfDebugLog('p2p','      -> patch content :'.$patch);
            $dom = new DOMDocument();
            $dom->loadXML($patch);

            $patchs = $dom->getElementsByTagName($name);

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
                if ($p->hasAttribute("mime")) {
                    $Mime = $p->getAttribute('mime');
                }
                if ($p->hasAttribute("size")) {
                    $Size = $p->getAttribute('size');
                }
                if ($p->hasAttribute("url")) {
                    $Url = $p->getAttribute('url');
                }
                if ($p->hasAttribute("DateAtt")) {
                    $Date = $p->getAttribute('DateAtt');
                }
                if ($p->hasAttribute("siteUrl")) {
                    $SiteUrl = $p->getAttribute('siteUrl');
                }
                if ($p->hasAttribute("causal")) {
                    $causal = $p->getAttribute('causal');
                }
            }

            $operations = null;
            $op = $dom->getElementsByTagName('operation');
            foreach($op as $o)
                $operations[] = $o->firstChild->nodeValue;

            $lastPatch = utils::getLastPatchId($onPage);
            if ($lastPatch==false) $lastPatch='none';

            if (!in_array($onPage, $pages)) {
                $onPage1 = str_replace(array(' '), array('_'), $onPage);
                utils::writeAndFlush("<span style=\"margin-left:60px;\">Page: <A HREF=" . 'http://' . $wgServerName . $wgScriptPath . "/index.php/$onPage1>" . $onPage . "</A></span><br/>");
                $pages[] = $onPage;
            }

            $newPatchID =   "Patch:".utils::generateID();//we create the Undo Patch ID /*changeToUndo?*/

            //we will supose that the attachment files are still available on the local server
            //and that re-adding a link into the page using an undo won't lead to obtaining a broken link
            /*
            if ($sub === 'ATT') {
                touch(utils::prepareString($Mime,$Size,$Url));

                $DateLastPatch = utils::getLastAttPatchTimestamp($onPage);
                //$DateOtherPatch = utils::getOtherAttPatchTimestamp($patchIdList);
                
                
                if ($DateLastPatch == null) {
                    downloadFile($Url);
                    $edit = true;
                    utils::writeAndFlush("<span style=\"margin-left:98px;\">download attachment (".round($Size/1000000,2)."Mo)</span><br/>");
                } elseif ($DateLastPatch < $Date) {
                    downloadFile($Url);
                    $edit = true;
                    utils::writeAndFlush("<span style=\"margin-left:98px;\">download attachment (" . round($Size / 1000000, 2) . "Mo)</span><br/>");
                } else {
                    newRev($onPage);
                    $edit = false;
                }
                unlink(utils::prepareString($Mime,$Size,$Url));
                
            }
            
            */
            utils::writeAndFlush("<span style=\"margin-left:80px;\">" . $i . "/" . $j . ": Integration of Patch: <A HREF=" . 'http://' . $wgServerName . $wgScriptPath . "/index.php/$newPatchID>" . $newPatchID . "</A></span><br/>");
/*
            if ($sub === 'ATT') {
                $rev = logootIntegrateAtt($onPage, $edit);
                
                if ($rev>0) {
                	$lop = array();
                    foreach($operations as $o) {
                    	if ($o instanceof LogootOperation) $lop[] = $o;
                		else $lop[] = operationToLogootOp($o);
                    }
                    $patch = new Patch(true, true, new LogootPatch($patchId,$lop), $SiteUrl, $causal, $patchId, $lastPatch, $siteID, $Mime, $Size, $Url, $Date);
                    $patch->storePage($onPage,$rev);
                } else {
                    throw new MWException(__METHOD__ . ': article not saved!');
                }
            }

            else {
*/
                list($rev,$operations) = logootIntegrateUndo($operations, $onPage);
                if ($rev>0) {
                    $patch = new Patch(true, false, $operations, $SiteUrl, $causal, $newPatchID, $lastPatch, $siteID, null, null, null, null);//use of the mechanism for remote patchs, using the standard one seems unable to calculate the causal link attribute correctly
                    $patch->storePage($onPage,$rev);
                }
                else {
                    throw new MWException( __METHOD__.': article not saved!');
                }
            /*}*/
        }//end if pageExists
        $i++;
    }
//    utils::writeAndFlush("<span style=\"margin-left:30px;\">Go to <A HREF=".$urlServer.">ChangeSet</A></span> <br/>");
}

/**
 *Integrates the operation(LogootOp) into the article via the logoot algorithm
 *
 * @param <Object> $operation
 * @param <String or Object> $article
 */
function logootIntegrateUndo($operations, $article) {
    global $wgCanonicalNamespaceNames;
    $indexNS = 0;
    wfDebugLog('p2p', '@@@@@@@@@@@@@@@@@@@@@@@ - function logootIntegrateUndo : ' . $article);
    $dbr = wfGetDB(DB_SLAVE);
    $dbr->immediateBegin();

    if (is_string($article)) {
        //if there is a space in the title, repalce by '_'
        $article = str_replace(" ", "_", $article);
        
        if(strpos($article, ":")===false) {
            $pageid = $dbr->selectField('page','page_id', array(
                    'page_title'=>$article/*WithoutNS*/));
        }
        else {//if there is a namespace
            preg_match( "/^(.+?)_*:_*(.*)$/S", $article, $tmp );
            $articleWithoutNS = $tmp[2];
            $NS = $tmp[1];
            if(in_array($NS, $wgCanonicalNamespaceNames)) {
                foreach ($wgCanonicalNamespaceNames as $key=>$value) {
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
        if(is_null($lastRev)) {
            $rev_id = 0;
        }
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

    $logoot = manager::getNewEngine($model,DSMWSiteId::getInstance()->getSiteId());// new logootEngine($model);

    foreach ($operations as $operation) {
        wfDebugLog('p2p',' - operation : '.$operation);
        wfDebugLog('testlog',' - operation : '.$operation);
        
        if (!($operation instanceof LogootOperation)) 
        	$operation = operationToLogootOp($operation);

        if ($operation!=false && is_object($operation)) {
            $listOp[]=$operation;
            wfDebugLog('testlog',' -> Operation: '.$operation->getLogootPosition()->toString());
            //$blobInfo->integrateBlob($operation);
        }        
    }//end foreach operations
    $p = new LogootPatch($rev_id,$listOp);

    $p = $logoot->undoPatch($p);
    
    $logoot->integrate($p);
    $modelAfterIntegrate = $logoot->getModel();
    //$revId = utils::getNewArticleRevId();
    $status = $article->doEdit($modelAfterIntegrate->getText(), $summary="");
    $revId = $status->value['revision']->getId();
    manager::storeModel($revId, $sessionId=session_id(), $modelAfterIntegrate, $blobCB=0);
    return array($revId,$p);
}
//////////////////BEN//////////////////

?>
