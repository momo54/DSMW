<?php

/**
 * Static utility methods used in the whole DSMW extension
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe - hantz
 */
class utils {

/**
 * generates IDs ==> SiteURL.SiteName.localclock   (ChangeSetID,patchID,OperationID)
 * Locally unique
 */
    static function generateID() {
        //global $serverId;
        $serverId = DSMWSiteId::getInstance();
        
        $pc = new persistentClock();
        $pc->load();
        $pc->incrementClock();
        $id = /*$wgServerName.$wgScriptPath*/$serverId->getSiteId().$pc->getValue();
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
//        global $wgServerName, $wgScriptPath, $wgScriptExtension;
//        $url = 'http://'.$wgServerName.$wgScriptPath;
//        $rev = utils::file_get_contents_curl(strtolower($url)."/api{$wgScriptExtension}?action=query&prop=info&titles=".$pageName.'&format=php');
//        wfDebugLog('p2p','  -> result page exist : '.$rev);
//        $rev =  unserialize($rev);
//
////        /*If false, the server is not reachable via the url
////             * Then we try to reach it with the .php5 extension
////             */
////            if($rev===false){
////                $rev = utils::file_get_contents_curl(strtolower($url)."/api.php5?action=query&prop=info&titles=".$pageName.'&format=php');
////                wfDebugLog('p2p','  -> result page exist : '.$rev);
////                $rev =  unserialize($rev);
////            }
//
//        wfDebugLog('p2p','  -> count : '.count($rev['query']['pages'][-1]));
//        return count($rev['query']['pages'][-1])==0;
//    //PHPUnit_Framework_Assert::assertFalse(count($rev['query']['pages'][-1])>0);
//

    //split NS and name
    preg_match( "/^(.+?)_*:_*(.*)$/S", $pageName, $m );
    $nameWithoutNS = $m[2];
    $title = Title::newFromText($nameWithoutNS, PATCH);
    $article = new Article($title);
    if($article->exists()) return true;
    else return false;

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
        $newtext = 'ChangeSet:
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
        global $wgUser;
        $newtext = '
[[Special:ArticleAdminPage|DSMW Admin functions]]

==Features==
[[changeSetID::'.$CSID.']]

\'\'\'Date:\'\'\' '.date(DATE_RFC822).'

\'\'\'User:\'\'\' '.$wgUser->getName().'

This ChangeSet is in : [[inPullFeed::'.$inPullFeed.']]<br>
==Pulled patches==

{| class="wikitable" border="1" style="text-align:left; width:30%;"
|-
!bgcolor=#c0e8f0 scope=col | Patch
|-
';
        foreach ($listPatch as $patch) {
            $newtext .="|[[hasPatch::".$patch."]]
|-
";
        }
        $newtext.="
|}";
        $newtext.="
==Previous ChangeSet==
[[previousChangeSet::".$previousCS."]]
";
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
        $text = '
[[Special:ArticleAdminPage|DSMW Admin functions]]

==Features==
[[patchID::'.$patchId.'| ]]

\'\'\'Remote Patch\'\'\'

\'\'\'Integration Date:\'\'\' '.date(DATE_RFC822).'

This is a patch of the article: [[onPage::'.$onPage.']]
==Operations of the patch==

{| class="wikitable" border="1" style="text-align:left; width:80%;"
|-
!bgcolor=#c0e8f0 scope=col | Type
!bgcolor=#c0e8f0 scope=col | Content
|-
';
        foreach ($operations as $op) {
            $opArr = explode(";", $op);
            $text .= '|[[hasOperation::'.$op.'| ]]'.$opArr[1].'
|<nowiki>'.utils::contentDecoding($opArr[3]).'</nowiki>
|-
';
        }
        if (is_array($previousPatch)) {
           $text.='|}';
           $text.='
==Previous patch(es)==
[[previous::';
            foreach ($previousPatch as $prev) {
                $text.=$prev.';';
            }
            $text.=']]';
        }
        else {
            $text.='
==Previous patch(es)==
[[previous::'.$previousPatch.']]';
        }
        
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
     * @return <array or String> the last patch id
     * or an array of the last patches id
     */
    static function getLastPatchId($pageName, $url='') {
        //global $wgScriptExtension;
        if($url!=''){//case of tests
        $req = '[[Patch:+]] [[onPage::'.$pageName.']]';
        $req = utils::encodeRequest($req);
        $url1 = $url."/index.php/Special:Ask/".$req."/-3FpatchID/headers=hide/sep=!/format=csv/limit=100";
        $results = utils::file_get_contents_curl($url1);//patches list
        $results = str_replace('"', '', $results);
        if ($results=="") return false;
        $results = explode("\n", $results);
        foreach ($results as $key=>$str1) {
            if ($str1=="") unset ($results[$key]);
            $pos = strpos($str1, '!');
            if($pos !== false) $results[$key] = /*'patch:'.*/substr($str1, $pos+1);
        //else $string[$key] = 'Patch:'.$str1;
        }
        }else{

        $results = array();
        $res = utils::getSemanticQuery('[[Patch:+]] [[onPage::'.$pageName.']]', '?patchID');
        if($res===false)return false;
        $count = $res->getCount();
        for($i=0; $i<$count; $i++) {

            $row = $res->getNext();
            if ($row===false) break;
            $row = $row[1];

            $col = $row->getContent();//SMWResultArray object
            foreach($col as $object) {//SMWDataValue object
                $wikiValue = $object->getWikiValue();
                $results[] = $wikiValue;
            }
        }
        }

/*$string is the list of the patches */
        
        if($url!=''){
        $url2 = $url."/index.php/Special:Ask/".$req."/-3Fprevious/headers=hide/sep=!/format=csv/limit=100";
        $results1 = utils::file_get_contents_curl($url2);//previous list
        //$string1 = str_replace("patch:", "", $string1);
        if ($results1=="") return false;
        $results1 = explode("\n", $results1);
        foreach ($results1 as $key=>$str) {
            $pos = strpos($str, '!');
            if($pos !== false) $results1[$key] = substr($str, $pos+1);
            //            $pos2 = strpos($string1[$key], 'patch:');
            //            if($pos2 !== false) $string1[$key] = substr($string1[$key], $pos2+strlen('patch:'));
            if ($results1[$key]=="") unset ($results1[$key]);
            $pos1 = strpos($results1[$key], ';');
            if($pos1 !== false) {
                $res = explode(';', $results1[$key]);
                $results1 = array_merge($results1, $res);
            }
        }
        }else{

        $results1 = array();
        $res1 = utils::getSemanticQuery('[[Patch:+]] [[onPage::'.$pageName.']]', '?previous');//PullFeed:PullBureau
        if($res1===false)return false;
        $count1 = $res1->getCount();
        for($j=0; $j<$count1; $j++) {

            $row1 = $res1->getNext();
            if ($row1===false) break;
            $row1 = $row1[1];

            $col1 = $row1->getContent();//SMWResultArray object
            foreach($col1 as $object1) {//SMWDataValue object
                $wikiValue1 = $object1->getWikiValue();
                $results1[] = $wikiValue1;
            }
        }
        }

    /*$string1 is the list of the patches witch are previouses */

        $result = array_diff($results, $results1);
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
        $serverId = DSMWSiteId::getInstance();
        return strpos(strtolower($patchId), strtolower($serverId->getSiteId()));
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
        global $wgScriptExtension;
        wfDebugLog('p2p','- function getSemanticRequest');
        $request = utils::encodeRequest($request);
        $param = utils::encodeRequest($param);
        $url = $server."/index{$wgScriptExtension}/Special:Ask/".$request.'/'.$param.'/headers=hide/format=csv/sep='.$sep.'/limit=100';
        wfDebugLog('p2p','  -> request url : '.$url);
        $php = utils::file_get_contents_curl($server."/index{$wgScriptExtension}/Special:Ask/".$request.'/'.$param.'/headers=hide/format=csv/sep='.$sep.'/limit=100'/*, 0, $ctx*/);
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
        global $wgServerName, $wgScriptPath, $wgScriptExtension;
        $urlServer = 'http://'.$wgServerName.$wgScriptPath."/index{$wgScriptExtension}";
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
       
        $results = array();
        $res = utils::getSemanticQuery('[[ChangeSet:+]] [[inPullFeed::'.$pfname.']]', '?hasPatch');
        $count = $res->getCount();
        for($i=0; $i<$count; $i++) {

            $row = $res->getNext();
            if ($row===false) break;
            $row = $row[1];

            $col = $row->getContent();//SMWResultArray object
            foreach($col as $object) {//SMWDataValue object
                $wikiValue = $object->getWikiValue();
                $results[] = $wikiValue;
            }
        }
        $results = array_unique($results);
        return $results;
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
        
        $firstPatch = array();
        $res = utils::getSemanticQuery('[[Patch:+]][[onPage::'.$title.']][[previous::'.$previousPatch.']]', '?patchID');
        $count = $res->getCount();
        for($i=0; $i<$count; $i++) {

            $row = $res->getNext();
            if ($row===false) break;
            $row = $row[1];

            $col = $row->getContent();//SMWResultArray object
            foreach($col as $object) {//SMWDataValue object
                $wikiValue = $object->getWikiValue();
                $firstPatch[] = $wikiValue;
            }
        }
       
        $patchs = array();
        while($firstPatch) {
            
            $patchFound = array();
            $res = utils::getSemanticQuery('[[Patch:+]][[onPage::'.$title.']][[previous::'.$firstPatch[0].']]', '?patchID');
            $count = $res->getCount();
            for($i=0; $i<$count; $i++) {

                $row = $res->getNext();
                if ($row===false) break;
                $row = $row[1];

                $col = $row->getContent();//SMWResultArray object
                foreach($col as $object) {//SMWDataValue object
                    $wikiValue = $object->getWikiValue();
                    $patchFound[] = $wikiValue;
                }
            }

            foreach ($patchFound as $p) {
                $firstPatch[] = $p;
            }
            $patchs[] = array_shift($firstPatch);
        }
        return $patchs;
    }

    static function getPageConcernedByPull($pfname) {

        $patchs = utils::getPulledPatches($pfname);
        $tabPage = array();
        foreach ($patchs as $patch) {
            
                        $onPage = array();
            $res = utils::getSemanticQuery('[[Patch:+]][[patchID::'.$patch.']]','?onPage');
            if($res===false) return false;
            $count = $res->getCount();
            for($i=0; $i<$count; $i++) {

                $row = $res->getNext();
                if ($row===false) break;
                $row = $row[1];

                $col = $row->getContent();//SMWResultArray object
                foreach($col as $object) {//SMWDataValue object
                    $wikiValue = $object->getWikiValue();
                    $onPage[] = $wikiValue;
                }
            }
            if(!empty ($onPage)) {
                $tabPage[$onPage[0]] = 0;
            }
        }
        return $tabPage;
    }

    static function getPublishedPatchs($server,$pushName,$title=null) {
        //global $wgScriptExtension;
        $published = array();
        $pushName = str_replace(' ', '_', $pushName);
        $title = str_replace(' ', '_', $title);
        if(isset ($title)) {
            $patchXML = utils::file_get_contents_curl(strtolower($server)."/api.php?action=query&meta=patchPushed&pppushName=".
                $pushName.'&pppageName='.$title.'&format=xml'/*,0, $ctx*/);
            /*test if it is a xml file. If not, the server is not reachable via the url
             * Then we try to reach it with the .php5 extension
             */
            if(strpos($patchXML, "<?xml version=\"1.0\"?>")===false){
                $patchXML = utils::file_get_contents_curl(strtolower($server)."/api.php5?action=query&meta=patchPushed&pppushName=".
                $pushName.'&pppageName='.$title.'&format=xml'/*,0, $ctx*/);
            }
            if(strpos($patchXML, "<?xml version=\"1.0\"?>")===false) $patchXML=false;
        }else {
            $patchXML = utils::file_get_contents_curl(strtolower($server)."/api.php?action=query&meta=patchPushed&pppushName=".
                $pushName.'&format=xml'/*,0, $ctx*/);
            /*test if it is a xml file. If not, the server is not reachable via the url
             * Then we try to reach it with the .php5 extension
             */
            if(strpos($patchXML, "<?xml version=\"1.0\"?>")===false){
                $patchXML = utils::file_get_contents_curl(strtolower($server)."/api.php5?action=query&meta=patchPushed&pppushName=".
                $pushName.'&format=xml'/*,0, $ctx*/);
            }
            if(strpos($patchXML, "<?xml version=\"1.0\"?>")===false) $patchXML=false;
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

    /**
 *
 * @param <String> $query (e.g. [[ChangeSet:+]][[inPullFeed::Pullfeed:xxxxx]])
 * @param <String> $paramstring Printout parameters (e.g. ?hasPatch?changeSetID)
 * @return <Object> SMWQueryResult object
 */

    static public function getSemanticQuery($query, $paramstring='') {
        $printouts = array();
        $rawparams = array();
        $params = array('format'=>' ', 'sort'=>' ', 'offset'=>0);
        $rawparams[] = $query;
        if ($paramstring != '') {
            $ps = explode("\n", $paramstring);
            foreach ($ps as $param) {
                $param = trim($param);
                if ( ($param != '') && ($param{0} != '?') ) {
                    $param = '?' . $param;
                }
                $rawparams[] = $param;
            }
        }

        SMWQueryProcessor::processFunctionParams($rawparams, $query,$params,$printouts);

        $queryobj = SMWQueryProcessor::createQuery($query, $params, SMWQueryProcessor::SPECIAL_PAGE , '', $printouts);
        $queryobj->setLimit(5000);
        $res = smwfGetStore()->getQueryResult($queryobj);

        if(!($res instanceof SMWQueryResult))return false;
        return $res;
    }

/**
 * 
 *
 * @param <String> $url
 * @return <Array> array(0=>pushName, 1=>pushUrl)
 */
    static function parsePushURL($url) {
        $res = array();
        $pos = strpos($url, 'PushFeed:');
        if ($pos===false) return false;
        $pushName = substr($url, $pos+strlen('PushFeed:'));

        $tmpUrl = substr($url, 0, $pos);
        $pos1=strpos($tmpUrl, '/index.php');
        if($pos1!=false) $pushUrl = substr($tmpUrl,0,$pos1);
        else $pushUrl = $tmpUrl;

        $pushUrl = rtrim($pushUrl, "/");

        $res[0] = $pushName;
        $res[1] = $pushUrl;
        return $res;
    }

/**
 * file_get_Contents
 * @param <String> $url
 * @return <String>
 */
    static function file_get_contents_curl($url) {
    if(extension_loaded('curl')){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);
    curl_close($ch);
    }else{// if curl is not loaded
        if(ini_get('allow_url_fopen')==='1'){
        $data = file_get_contents($url);
        }else{// curl not loaded and allow_url_fopen=>Off
            throw new MWException( __METHOD__.': DSMW needs either curl extension
to be loaded else "allow_url_fopen" set to "On"' );
        }
    }
    return $data;
}

}
?>
