<?php

/**
 * Static utility methods used in the whole DSMW extension
 *
 * @author muller jean-philippe - hantz
 */
class utils {

/**
 * generates IDs ==> SiteURL.SiteName.localclock   (ChangeSetID,patchID,OperationID)
 * Locally unique
 */
    static function generateID() {
        global $serverId;//$wgServerName, $wgScriptPath;
        $pc = new persistentClock();
        $pc->load();
        $pc->incrementClock();
        $id = /*$wgServerName.$wgScriptPath*/$serverId.$pc->getValue();
        $pc->store();
        unset ($pc);
        return $id;
    }

    /**
     * String encoding
     * @param <String> $request
     * @return <String>
     */
    static function encodeRequest($request) {
        $req = str_replace(
            array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}', ':'),
            array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D', '-3A'), $request);
        return $req;
    }

    /**
     * String decoding
     * @param <String> $req
     * @return <String>
     */
    static function decodeRequest($req) {
        $request = str_replace(
            array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D', '-3A'),
            array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}', ':'), $req);
        return $request;
    }

    /**
     *
     * @param <String> $content
     * @return <String> encoded content
     */
    static function contentEncoding($content) {
        $res = base64_encode($content);
        return $res;
    }

    /**
     *
     * @param <String> $content
     * @return <String> decoded content
     */
    static function contentDecoding($content) {
        $res = base64_decode($content);
        return $res;
    }

    /**
     * Checks if page exists
     *
     * @global <String> $wgServerName
     * @global <String> $wgScriptPath
     * @param <String> $pageName
     * @return <bool>
     */
    static function pageExist($pageName) {
        global $wgServerName, $wgScriptPath;
        $url = 'http://'.$wgServerName.$wgScriptPath;
        $rev = file_get_contents($url.'/api.php?action=query&prop=info&titles='.$pageName.'&format=php');
        wfDebugLog('p2p','  -> result page exist : '.$rev);
        $rev =  unserialize($rev);
        wfDebugLog('p2p','  -> count : '.count($rev['query']['pages'][-1]));
        return count($rev['query']['pages'][-1])==0;
    //PHPUnit_Framework_Assert::assertFalse(count($rev['query']['pages'][-1])>0);
    }


    /**
     *Creates a new ChangeSet linked with a pushfeed (page)
     *
     * @param <String> $CSID
     * @param <String> $inPushFeed
     * @param <String> $previousCS
     * @param <array> $listPatch
     */
    static function createChangeSetPush($CSID,$inPushFeed,$previousCS,$listPatch) {
        $newtest = 'ChangeSet:
changeSetID: [[changeSetID::'.$CSID.']]
inPushFeed: [[inPushFeed::'.$inPushFeed.']]
previousChangeSet: [[previousChangeSet::'.$previousCS.']]
';
        foreach ($listPatch as $patch) {
            $newtext.=" hasPatch: [[hasPatch::".$patch."]]";
        }
        $newtext.="
----
[[Special:ArticleAdminPage]]";

        $title = Title::newFromText($CSID, CHANGESET);
        $article = new Article($title);
        $article->doEdit($newtext, $summary="");
    }

    /**
     *
     * Creates a new ChangeSet linked with a pullfeed (page)
     * @param <String> $CSID
     * @param <String> $inPullFeed
     * @param <String> $previousCS
     * @param <array> $listPatch
     */
    static function createChangeSetPull($CSID,$inPullFeed,$previousCS,$listPatch) {
        $newtext = 'ChangeSet:
changeSetID: [[changeSetID::'.$CSID.']]
inPullFeed: [[inPullFeed::'.$inPullFeed.']]
previousChangeSet: [[previousChangeSet::'.$previousCS.']]
';
        foreach ($listPatch as $patch) {
            $newtext .=" hasPatch: [[hasPatch::".$patch."]]";
        }
        $newtext.="
----
[[Special:ArticleAdminPage]]";
        $title = Title::newFromText($CSID, CHANGESET);
        $article = new Article($title);
        $article->doEdit($newtext, $summary="");
    }


    /**
     * create a new patch (page)
     *
     * @param <String> $patchId
     * @param <String> $onPage
     * @param <String> $previousPatch
     * @param <array> $operations
     */
    static function createPatch($patchId, $onPage, $previousPatch, $operations) {
        $text = 'Patch: patchID: [[patchID::'.$patchId.']]
 onPage: [[onPage::'.$onPage.']] ';
        foreach ($operations as $op) {
            $text .= 'hasOperation [[hasOperation::'.$op.']] ';
        }
        if (is_array($previousPatch)) {
            $text.=' previous: [[previous::';
            foreach ($previousPatch as $prev) {
                $text.=$prev.';';
            }
            $text.=']]';
        }
        else {
            $text.=' previous: [[previous::'.$previousPatch.']]';
        }
        $text.="
----
[[Special:ArticleAdminPage]]";
        $title = Title::newFromText($patchId, PATCH);
        $article = new Article($title);
        $article->doEdit($text, $summary="");
    }

    //    static function createPushFeed($name, $request){
    //        $stringReq = utils::encodeRequest($request);//avoid "semantic injection" :-)
    //
    //        $newtext = "PushFeed:
    //Name: [[name::PushFeed:".$name."]]
    //hasSemanticQuery: [[hasSemanticQuery::".$stringReq."]]
    //Pages concerned:
    //{{#ask: ".$request."}}
    //[[deleted::false| ]]
    //";
    //$newtext.="----
    //[[Special:ArticleAdminPage]]";
    //        wfDebugLog('p2p','  -> push page contains : '.$newtext);
    //        $title = Title::newFromText($name, PUSHFEED);
    //
    //        $article = new Article($title);
    //        $edit = $article->doEdit($newtext, $summary="");
    //    }


    /**
     *Used to get the id of the last patch(es) of the given article
     *
     * @global <String> $wgServerName
     * @global <String> $wgScriptPath
     * @param <String> $pageName
     * @param <String> $url
     * @return <array or String> the la patch id
     * or an array of the last patches id
     */
    static function getLastPatchId($pageName, $url='') {
        global $wgServerName, $wgScriptPath;
        $req = '[[Patch:+]] [[onPage::'.$pageName.']]';
        $req = utils::encodeRequest($req);
        if($url=='')    $url = 'http://'.$wgServerName.$wgScriptPath;
        $url1 = $url."/index.php/Special:Ask/".$req."/-3FpatchID/headers=hide/sep=!/format=csv/limit=100";
        $string = file_get_contents($url1);//patches list
        $string = str_replace('"', '', $string);
        $string = $string;
        if ($string=="") return false;
        $string = explode("\n", $string);
        foreach ($string as $key=>$str1) {
            if ($str1=="") unset ($string[$key]);
            $pos = strpos($str1, '!');
            if($pos !== false) $string[$key] = /*'patch:'.*/substr($str1, $pos+1);
        //else $string[$key] = 'Patch:'.$str1;
        }
/*$string is the list of the patches */

        $url2 = $url."/index.php/Special:Ask/".$req."/-3Fprevious/headers=hide/sep=!/format=csv/limit=100";
        $string1 = file_get_contents($url2);//previous list
        $string1 = $string1;
        //$string1 = str_replace("patch:", "", $string1);
        if ($string1=="") return false;
        $string1 = explode("\n", $string1);
        foreach ($string1 as $key=>$str) {
            $pos = strpos($str, '!');
            if($pos !== false) $string1[$key] = substr($str, $pos+1);
            //            $pos2 = strpos($string1[$key], 'patch:');
            //            if($pos2 !== false) $string1[$key] = substr($string1[$key], $pos2+strlen('patch:'));
            if ($string1[$key]=="") unset ($string1[$key]);
            $pos1 = strpos($string1[$key], ';');
            if($pos1 !== false) {
                $res = explode(';', $string1[$key]);
                $string1 = array_merge($string1, $res);
            }
        }
    /*$string1 is the list of the patches witch are previouses */

        $result = array_diff($string, $string1);
        if (count($result)>1) return $result;
        else return array_shift($result);
    }

    /**
     * verifies if the url has a protocol (exp: http) and a host name
     *
     * @param <String> $url
     * @return <bool>
     */
    static function isValidURL($url) {
        $arr = parse_url($url);
        if(!isset ($arr['scheme']) || !isset ($arr['host']))
            return false;
        else return true;
    }

    /**
     *
     * @param <String> $patchId
     * @return <int or bool> false if no occurence
     */
    static function isRemote($patchId) {
        return strpos(strtolower($patchId), strtolower(getServerId()));
    }

    /**
     *
     * @param <type> $stringOpInPatch
     * @return <type> array of nb insert and delete operation
     */
    static function countOperation($opInPatch) {
        $res['insert'] = 0;
        $res['delete'] = 0;
        foreach ($opInPatch as $op) {
            $op = strtolower($op);
            $res['insert'] += substr_count($op, 'insert');
            $res['delete'] += substr_count($op, 'delete');
        }
        return $res;
    }

    /**
     * Used to execute a semantic request on a DSMW server
     *
     * @param <String> $server
     * @param <String> $request semantic query
     * @param <String> $param parameters to display
     * @param <String> $sep separator
     * @return <array>
     */
    static function getSemanticRequest($server,$request,$param,$sep='!') {
//        $ctx = stream_context_create(array(
//    'http' => array(
//        'timeout' => 10
//        )
//    )
//);
        wfDebugLog('p2p','- function getSemanticRequest');
        $request = utils::encodeRequest($request);
        $param = utils::encodeRequest($param);
        $url = $server.'/index.php/Special:Ask/'.$request.'/'.$param.'/headers=hide/format=csv/sep='.$sep.'/limit=100';
        wfDebugLog('p2p','  -> request url : '.$url);
        $php = file_get_contents($server.'/index.php/Special:Ask/'.$request.'/'.$param.'/headers=hide/format=csv/sep='.$sep.'/limit=100'/*, 0, $ctx*/);
        if($php == "") {
            return array();
        }
        elseif($php===false)return false;
        $res = explode("\n", $php);
        $array = explode($sep, $php);
        foreach ($res as $key=>$page) {
            if($page=="") {
                unset ($res[$key]);
            }else {
                $res[$key] = str_replace("\"", "", $page);
            }
        }
        return $res;
    }

    /**
     * Creates a pushfeed
     *
     * @global <String> $wgServerName
     * @global <String> $wgScriptPath
     * @param <String> $name pushfeed name
     * @param <String> $request
     * @return <bool> true if creation successful, false if not
     */
    static function createPushFeed($name, $request) {
        global $wgServerName, $wgScriptPath;
        $urlServer = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
        $stringReq = utils::encodeRequest($request);//avoid "semantic injection"
        $newtext = "
{{#form:action=".$urlServer."?action=onpush|method=POST|
{{#input:type=hidden|name=push|value=".$name."}}<br>
{{#input:type=hidden|name=action|value=onpush}}<br>
{{#input:type=submit|value=PUSH}}
}}
----
[[Special:ArticleAdminPage]]
----
PushFeed:
Name: [[name::".$name."]]
hasSemanticQuery: [[hasSemanticQuery::".$stringReq."]]
Pages concerned:
{{#ask: ".$request."}}
[[deleted::false| ]]
";

        wfDebugLog('p2p','  -> push page contains : '.$newtext);
        $title = Title::newFromText($name, PUSHFEED);
        $article = new Article($title);
        $status = $article->doEdit($newtext, $summary="");
        if((is_bool($status) && $status) || (is_object($status)&&$status->isGood())) return true;
        else return false;
    }

    /**
     * Our model is stored in the DB just before Mediawiki creates
     * the new revision that's why we have to get the last existing revision ID
     * and the new will be lastId+1 ...
     * @return <Integer> last revision id + 1
     */
    static function getNewArticleRevId() {
        wfProfileIn( __METHOD__ );
        $dbr = wfGetDB( DB_SLAVE );
        $lastid = $dbr->selectField('revision','MAX(rev_id)');
        wfProfileOut( __METHOD__ );
        return $lastid + 1;
    }

    /**
     *Gets the pulled patches for a given pullfeed
     *
     * @global <String> $wgServerName
     * @global <String> $wgScriptPath
     * @param <String> $pfname pullfeed name
     * @return <array> pulled patches
     */
    static function getPulledPatches($pfname) {
        global $wgServerName, $wgScriptPath;
        $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
        $req = '[[ChangeSet:+]] [[inPullFeed::'.$pfname.']]';
        $req = utils::encodeRequest($req);
        $url = $url."/Special:Ask/".$req."/-3FhasPatch/headers=hide/format=csv/sep=,/limit=100";
        $string = file_get_contents($url);
        if ($string=="") return array();//false;
        $string = str_replace("\n", ",", $string);
        $string = str_replace("\"", "", $string);
        $res = explode(",", $string);

        foreach ($res as $key=>$resultLine) {
            if(strpos($resultLine, 'ChangeSet:')!==false || $resultLine=="") {
                unset($res[$key]);
            }
        }
        $res = array_unique($res);

        return $res;//published patch tab
    }

    /**
     * returns an array of patches ordered by previous
     *
     * @global <String> $wgServerName
     * @global <String> $wgScriptPath
     * @param <String> $title of an article page
     * @param <String> $previousPatch
     * @return <array> patch list
     */
    static function orderPatchByPrevious($title,$previousPatch='none') {
        global $wgServerName, $wgScriptPath;
        $firstPatch = utils::getSemanticRequest('http://'.$wgServerName.$wgScriptPath, '[[Patch:+]][[onPage::'.$title.']][[previous::'.$previousPatch.']]', '-3FpatchID');

        /*while($firstPatch) {
            $p = split(',',$firstPatch[0]);
            $firstPatch[0] = $p[1];
            $patchFound = $this->getRequestedPages('[[Patch:+]][[onPage::'.$title.']][[previous::'.$firstPatch[0].']]','?patchID');
            foreach ($patchFound as $p) {
                $firstPatch[] = $p;
            }

            $newPatch = array_shift($firstPatch);
            if(!$marque[$newPatch]) {
                $marque[$newPatch] = 1;
                $patchs[] = $newPatch;
            }*/
        $patchs = array();
        while($firstPatch) {
            /*$p = split(',',$firstPatch[0]);
            $firstPatch[0] = $p[1];*/
            $patchFound = utils::getSemanticRequest('http://'.$wgServerName.$wgScriptPath, '[[Patch:+]][[onPage::'.$title.']][[previous::'.$firstPatch[0].']]', '-3FpatchID');
            foreach ($patchFound as $p) {
                $firstPatch[] = $p;
            }
            $patchs[] = array_shift($firstPatch);
        }
        return $patchs;
    }

    static function getPageConcernedByPull($pfname) {
        global $wgServerName, $wgScriptPath;
        $patchs = utils::getPulledPatches($pfname);
        $tabPage = array();
        foreach ($patchs as $patch) {
            $onPage = utils::getSemanticRequest('http://'.$wgServerName.$wgScriptPath,'[[Patch:+]][[patchID::'.$patch.']]','?onPage');
            if($onPage===false)return false;
            $onPage = explode('!', $onPage[0]);
            $tabPage[$onPage[1]] = 0;
        }
        return $tabPage;
    }

    static function getPublishedPatchs($server,$pushName,$title=null) {
//        $ctx = stream_context_create(array(
//    'http' => array(
//        'timeout' => 1
//        )
//    )
//);
        $published = array();
        $pushName = str_replace(' ', '_', $pushName);
        if(isset ($title)) {
            $patchXML = file_get_contents($server.'/api.php?action=query&meta=patchPushed&pppushName='.
                $pushName.'&pppageName='.$title.'&format=xml'/*,0, $ctx*/);
        }else {
            $patchXML = file_get_contents($server.'/api.php?action=query&meta=patchPushed&pppushName='.
                $pushName.'&format=xml'/*,0, $ctx*/);
        }
        if($patchXML===false)return false;
        $dom = new DOMDocument();
        $dom->loadXML($patchXML);
        $patchPublished = $dom->getElementsByTagName('patch');
        $published = array();
        foreach($patchPublished as $p) {
            $published[] = $p->firstChild->nodeValue;
        }
        return $published;
    }

}
?>
