<?php

/**
 * Returns an array of page titles received via the request
 *
 * @global <String> $wgServerName
 * @global <String> $wgScriptPath
 * @param <String> $request
 * @return <array>
 */
function getRequestedPages($request) {
    global $wgServerName, $wgScriptPath;
    $req = utils::encodeRequest($request);
    $url1 = 'http://'.$wgServerName.$wgScriptPath."/index.php/Special:Ask/".$req."/format=csv/sep=,/limit=100";
    $string = file_get_contents($url1);
    $res = explode("\n", $string);
    foreach ($res as $key=>$page) {
        if($page=="") {
            unset ($res[$key]);
        }else {
            $res[$key] = str_replace("\"", "", $page);
            $res[$key] = str_replace(',', '', $page);
            $pos = strpos($page, ':');
            $count = 1;
            if($pos==0) $res[$key] = str_replace(':', '', $page, $count);
        }
    }

    return $res;
}

/**
 *Gets the semantic request stored in the PushFeed page
 *
 * @global <String> $wgServerName
 * @global <String> $wgScriptPath
 * @param <String> $pfName pushfeed name
 * @return <String>
 */
function getPushFeedRequest($pfName) {
    global $wgServerName, $wgScriptPath;
    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
    $req = '[['.$pfName.']]';//'[[PushFeed:'.$pfName.']]'
    $req = utils::encodeRequest($req);
    $url = $url."/Special:Ask/".$req."/-3FhasSemanticQuery/headers=hide/format=csv/sep=,/limit=100";
    $string = file_get_contents($url);
    if ($string=="") return false;
    $res = explode(",", $string);
    $res = utils::decodeRequest($res[1]);
    return $res;
}

/**
 *Gets the previous changeSet ID (in the push action sequence)
 * @global <String> $wgServerName
 * @global <String> $wgScriptPath
 * @param <String> $pfName PushFeed name
 * @return <String> previous changeSet ID
 */
//function getPreviousCSID($pfName) {
//    global $wgServerName, $wgScriptPath;
//    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
//    $req = '[[ChangeSet:+]] [[inPushFeed::'.$pfName.']]';
//    $req = utils::encodeRequest($req);
//    $url = $url."/Special:Ask/".$req."/-3FchangeSetID/headers=hide/order=desc/format=csv/limit=1";
//    $string = file_get_contents($url);
//    if ($string=="") return false;
//    $string = explode(",", $string);
//    $string = $string[0];
//    $string = str_replace(',', '', $string);
//    $string = str_replace("\"", "", $string);
//    return $string;
//}

/**
 * Gets the published patches
 *
 * @global <String> $wgServerName
 * @global <String> $wgScriptPath
 * @param <String> $pfname PushFeed name
 * @return <array> array of the published patches' name
 */
function getPublishedPatches($pfname) {
    global $wgServerName, $wgScriptPath;
    wfDebugLog('p2p',' - getPublishedPatches params pfName '.$pfname);
    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
    $req = '[[ChangeSet:+]] [[inPushFeed::'.$pfname.']]';
    $req = utils::encodeRequest($req);
    $url = $url."/Special:Ask/".$req."/-3FhasPatch/headers=hide/format=csv/sep=,/limit=100";
    wfDebugLog('p2p','  -> url : '.$url);
    $string = file_get_contents($url);
    if ($string=="") return array();//false;
    $string = str_replace("\n", ",", $string);
    $string = str_replace("\"", "", $string);
    $res = explode(",", $string);

    foreach ($res as $key=>$resultLine) {
        if(strpos($resultLine, 'ChangeSet:')!==false || $resultLine=="") {
            unset($res[$key]);
        }
        wfDebugLog('p2p','  -> res : '.$resultLine);
    }
    $res = array_unique($res);

    return $res;//published patch tab
}

/**
 *In a pushfeed page, the value of [[hasPushHead::]] has to be updated with the
 *ChangeSetId of the last generated ChangeSet
 *
 * @param <String> $name Pushfeed name
 * @param <String> $CSID ChangeSetID
 * @return <boolean> returns true if the update is successful
 */

function updatePushFeed($name, $CSID) {
//split NS and name
    preg_match( "/^(.+?)_*:_*(.*)$/S", $name, $m );
    $articleName = $m[2];

    //get PushFeed by name
    $title = Title::newFromText($articleName, PUSHFEED);
    $dbr = wfGetDB( DB_SLAVE );
    $revision = Revision::loadFromTitle($dbr, $title);
    $pageContent = $revision->getText();

    //get hasPushHead Value if exists
    $start = "[[hasPushHead::";
    $val1 = strpos( $pageContent, $start );
    if ($val1!==false) {//if there is an occurence of [[hasPushHead::
        $startVal = $val1 + strlen( $start );
        $end = "]]";
        $endVal = strpos( $pageContent, $end, $startVal );
        $value = substr( $pageContent, $startVal, $endVal - $startVal );

        //update hasPushHead Value
        $result = str_replace($value, $CSID, $pageContent);
        $pageContent = $result;
        if($result=="")return false;
    }else {//no occurence of [[hasPushHead:: , we add
        $pageContent.= ' hasPushHead: [[hasPushHead::'.$CSID.']]';
    }
    //save update
    $article = new Article($title);
    $article->doEdit($pageContent, $summary="");

    return true;
}

/**
 * Gets the last changeset(haspullhead) linked with the given pullfeed
 *
 * @global <String> $wgServerName
 * @global <String> $wgScriptPath
 * @param <String> $pfName pullfeed name
 * @return <String or bool> false if no pullhead
 */
function getHasPullHead($pfName) {//pullfeed name with ns
    global $wgServerName, $wgScriptPath;
    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
    $req = '[[PullFeed:+]] [[name::'.$pfName.']]';
    $req = utils::encodeRequest($req);
    $url = $url."/Special:Ask/".$req."/-3FhasPullHead/headers=hide/order=desc/format=csv/limit=1";
    $string = file_get_contents($url);
    if ($string=="") return false;
    $string = str_replace("\n", ",", $string);
    $string = str_replace("\"", "", $string);
    $res = explode(",", $string);

    foreach ($res as $key=>$resultLine) {
        if(strpos($resultLine, 'PullFeed:')!==false || $resultLine=="") {
            unset($res[$key]);
        }
    }
    if (empty ($res)) return false;
    else return $res[1];
}

/**
 * Gets the last changeset(haspushhead) linked with the given pushfeed
 *
 *
 * @global <String> $wgServerName
 * @global <String> $wgScriptPath
 * @param <String> $pfName pushfeed name
 * @return <String or bool> false if no pushhead
 */
function getHasPushHead($pfName) {//pushfeed name with ns
    global $wgServerName, $wgScriptPath;
    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
    $req = '[[PushFeed:+]] [[name::'.$pfName.']]';
    $req = utils::encodeRequest($req);
    $url = $url."/Special:Ask/".$req."/-3FhasPushHead/headers=hide/order=desc/format=csv/limit=1";
    $string = file_get_contents($url);
    if ($string=="") return false;
    $string = str_replace("\n", ",", $string);
    $string = str_replace("\"", "", $string);
    $res = explode(",", $string);

    foreach ($res as $key=>$resultLine) {
        if(strpos($resultLine, 'PushFeed:')!==false || $resultLine=="") {
            unset($res[$key]);
        }
    }
    if (empty ($res)) return false;
    else return $res[1];
}

/**
 *Gest the name of the push where the pullfeed has subscribed
 *
 * @global <type> $wgServerName
 * @global <type> $wgScriptPath
 * @param <type> $name pullfeed name
 * @return <type> pushfeed name
 */
function getPushName($name) {//pullfeed name with NS
    global $wgServerName, $wgScriptPath;
    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
    $req = '[[PullFeed:+]] [[name::'.$name.']]';
    $req = utils::encodeRequest($req);
    $url = $url."/Special:Ask/".$req."/-3FpushFeedName/headers=hide/order=desc/format=csv/limit=1";
    $string = file_get_contents($url);
    if ($string=="") return false;
    $string = str_replace("\n", ",", $string);
    $string = str_replace("\"", "", $string);
    $res = explode(",", $string);

    foreach ($res as $key=>$resultLine) {
        if(strpos($resultLine, 'PullFeed:')!==false || $resultLine=="") {
            unset($res[$key]);
        }
    }
    if (empty ($res)) return false;
    else return $res[1];
}

/**
 * Gets the URL of the pushfeed where the pullfeed has subscribed
 *
 * @global <String> $wgServerName
 * @global <String> $wgScriptPath
 * @param <String> $name pullfeed name
 * @return <String> Pushfeed Url
 */
function getPushURL($name) {//pullfeed name with NS
    global $wgServerName, $wgScriptPath;
    wfDebugLog('p2p',' - function getPushURL');
    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
    $req = '[[PullFeed:+]] [[name::'.$name.']]';
    wfDebugLog('p2p','  -> request : '.$req);
    $req = utils::encodeRequest($req);
    $url = $url."/Special:Ask/".$req."/-3FpushFeedServer/headers=hide/order=desc/format=csv/limit=1";
    wfDebugLog('p2p','  -> url request : '.$url);
    $string = file_get_contents($url);
    wfDebugLog('p2p','  -> csv result : '.$string);
    if ($string=="") return false;
    $string = str_replace("\n", ",", $string);
    $string = str_replace("\"", "", $string);
    $res = explode(",", $string);

    foreach ($res as $key=>$resultLine) {
        if(strpos($resultLine, 'PullFeed:')!==false || $resultLine=="") {
            unset($res[$key]);
        }
    }
    wfDebugLog('p2p','  -> result '.$res[1].')');
    if (empty ($res)) return false;
    else return $res[1];
}
/**
 *In a pullfeed page, the value of [[hasPullHead::]] has to be updated with the
 *ChangeSetId of the last pulled ChangeSet
 *
 * @param <String> $name Pullfeed name (with namespace)
 * @param <String> $CSID ChangeSetID (without namespace)
 * @return <boolean> returns true if the update is successful
 */
function updatePullFeed($name, $CSID) {
//split NS and name
    preg_match( "/^(.+?)_*:_*(.*)$/S", $name, $m );
    $articleName = $m[2];

    //get PushFeed by name
    $title = Title::newFromText($articleName, PULLFEED);
    $dbr = wfGetDB( DB_SLAVE );
    $revision = Revision::loadFromTitle($dbr, $title);
    $pageContent = $revision->getText();

    //get hasPushHead Value if exists
    $start = "[[hasPullHead::";
    $val1 = strpos( $pageContent, $start );
    if ($val1!==false) {//if there is an occurence of [[hasPushHead::
        $startVal = $val1 + strlen( $start );
        $end = "]]";
        $endVal = strpos( $pageContent, $end, $startVal );
        $value = substr( $pageContent, $startVal, $endVal - $startVal );

        //update hasPullHead Value
        $result = str_replace($value, $CSID, $pageContent);
        $pageContent = $result;
        if($result=="")return false;
    }else {//no occurence of [[hasPushHead:: , we add
        $pageContent.= ' hasPullHead: [[hasPullHead::'.$CSID.']]';
    }
    //save update
    $article = new Article($title);
    $article->doEdit($pageContent, $summary="");

    return true;
}

?>
