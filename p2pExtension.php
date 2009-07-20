<?php

if ( !defined( 'MEDIAWIKI' ) ) {
    exit;
}
require_once "$IP/includes/GlobalFunctions.php";

$wgP2PExtensionIP = dirname( __FILE__ );


$wgHooks['UnknownAction'][] = 'onUnknownAction';
//$wgHooks['MediaWikiPerformAction'][] = 'performAction';

$wgHooks['EditPage::attemptSave'][] = 'attemptSave';
$wgHooks['EditPageBeforeConflictDiff'][] = 'conflict';

$wgAutoloadClasses['BlobInfo'] = "$wgP2PExtensionIP/logootEngine/BlobInfo.php";
$wgAutoloadClasses['LogootId'] = "$wgP2PExtensionIP/logootEngine/LogootId.php";
$wgAutoloadClasses['LogootPosition'] =
    "$wgP2PExtensionIP/logootEngine/LogootPosition.php";
$wgAutoloadClasses['Diff1']
    = $wgAutoloadClasses['_DiffEngine1']
    = $wgAutoloadClasses['_DiffOp1']
    = $wgAutoloadClasses['_DiffOp_Add1']
    = $wgAutoloadClasses['_DiffOp_Change1']
    = $wgAutoloadClasses['_DiffOp_Copy1']
    = $wgAutoloadClasses['_DiffOp_Delete1']
    = "$wgP2PExtensionIP/differenceEngine/DiffEngine.php";

$wgAutoloadClasses['LogootOp'] = "$wgP2PExtensionIP/logootop/LogootOp.php";
$wgAutoloadClasses['LogootIns'] = "$wgP2PExtensionIP/logootop/LogootIns.php";
$wgAutoloadClasses['LogootDel'] = "$wgP2PExtensionIP/logootop/LogootDel.php";
$wgAutoloadClasses['Patch'] = "$wgP2PExtensionIP/patch/Patch.php";
$wgAutoloadClasses['persistentClock'] = "$wgP2PExtensionIP/clockEngine/persistentClock.php";
$wgAutoloadClasses['ApiQueryPatch'] = "$wgP2PExtensionIP/api/ApiQueryPatch.php";
$wgAutoloadClasses['ApiQueryChangeSet'] = "$wgP2PExtensionIP/api/ApiQueryChangeSet.php";
$wgAutoloadClasses['utils'] = "$wgP2PExtensionIP/files/utils.php";

global $wgVersion;
if(compareMWVersion($wgVersion)==-1) {
    $wgApiQueryMetaModules = array('patch' => 'ApiQueryPatch','changeSet' => 'ApiQueryChangeSet');
}else {
//global $wgAPIMetaModules;
    $wgAPIMetaModules = array('patch' => 'ApiQueryPatch','changeSet' => 'ApiQueryChangeSet');
}
define ('INT_MAX', "1000000000000000000000");//22
define ('INT_MIN', "0");


function conflict(&$editor, &$out) {

    $conctext = $editor->textbox1;
    $actualtext = $editor->textbox2;
    $initialtext = $editor->getBaseRevision()->mText;
    $editor->mArticle->updateArticle( $actualtext, $editor->summary, $editor->minoredit,
        $editor->watchthis, $bot=false, $sectionanchor='' );

    return true;
}

//function performAction($output, $article, $title, $user, $request, $wiki) {
//    if($wiki->params['action']!='view') return true;
//    return true;
//}

/**
 *MW Hook used to redirect to page creation (pushfeed, pullfeed, changeset),
 * to forms or to push/pull action testing the action param
 *
 *
 * @global <Object> $wgOut
 * @param <Object> $action
 * @param <Object> $article
 * @return <boolean>
 */
function onUnknownAction($action, $article) {
    global $wgOut;

    //    $script=javascript($_SERVER['HTTP_REFERER']);
    //    $wgOut->addHeadItem('script', $script);

    //////////pull form page////////Request:    <br>{{#input:type=textarea|cols=30 | style=width:auto |rows=2|name=keyword}}<br>
    if(isset ($_GET['action']) && $_GET['action']=='addpullpage') {
        wfDebugLog('p2p','addPullPage ');
        $newtext = "Add a new site:

{{#form:action=".dirname($_SERVER['HTTP_REFERER'])."?action=pullpage|method=POST|
Server Url:<br>        {{#input:type=text|name=url}}<br>
PushFeed Name:<br>        {{#input:type=text|name=pushname}}<br>
PullFeed Name:   <br>    {{#input:type=text|name=pullname}}<br>
{{#input:type=submit|value=ADD}}
}}";
        //if article doesn't exist insertNewArticle
        if($article->mTitle->exists()) {
            $article->updateArticle($newtext, $summary="", false, false);
        }else {
            $article->insertNewArticle($newtext, $summary="", false, false);
        }
        $article->doRedirect();

        return false;
    }


    /////////push form page////////ChangeSet Url:<br>        {{#input:type=text|name=url}}<br>
    elseif(isset ($_GET['action']) && $_GET['action']=='addpushpage') {
        wfDebugLog('p2p','addPushPage');
        $newtext = "Add a new pushfeed:

{{#form:action=".dirname($_SERVER['HTTP_REFERER'])."?action=pushpage|method=POST|
PushFeed Name:   <br>    {{#input:type=text|name=name}}<br>
Request:    <br>{{#input:type=textarea|cols=30 | style=width:auto |rows=2|name=keyword}}<br>
{{#input:type=submit|value=ADD}}
}}";

        $article->doEdit($newtext, $summary="");
        $article->doRedirect();
        return false;
    }


    ///////PushFeed page////////
    elseif(isset ($_GET['action']) && $_GET['action']=='pushpage') {
    //$url = $_POST['url'];//pas url mais changesetId
        wfDebugLog('p2p','Create new push '.$_POST['name'].' with '.$_POST['keyword']);
        $name = $_POST['name'];
        $request = $_POST['keyword'];
        $stringReq = utils::encodeRequest($request);//avoid "semantic injection" :))
        //addPushSite($url, $name, $request);


        $newtext = "PushFeed:
Name: [[name::PushFeed:".$name."]]
hasSemanticQuery: [[hasSemanticQuery::".$stringReq."]]
Pages concerned:
{{#ask: ".$request."}}
[[deleted::false| ]]
";
$newtext.="----
[[Special:ArticleAdminPage]]";
        wfDebugLog('p2p','  -> push page contains : '.$newtext);
        $title = Title::newFromText($_POST['name'], PUSHFEED);

        $article = new Article($title);
        $edit = $article->doEdit($newtext, $summary="");
        $article->doRedirect();
        return false;
    }
    ///////ChangeSet page////////
    elseif(isset ($_POST['action']) && $_POST['action']=='onpush') {
        wfDebugLog('p2p','push on '.$_POST['push']);
        wfDebugLog('p2p',implode($_POST['push']));
        $patches = array();
        $tmpPatches = array();
        if(isset ($_POST['push'])) {
            $name1 = $_POST['push'];
            foreach ($name1 as $push) {
                wfDebugLog('p2p',' - '.$push);
            }
        }
        else $name1="";
        //else throw new MWException( __METHOD__.': no Pushfeed selected' );
        //        if(count($name)>1) {
        //            $outtext='<p><b>Select only one pushfeed!</b></p> <a href="'.$_SERVER['HTTP_REFERER'].'?back=true">back</a>';
        //            $wgOut->addHTML($outtext);
        //            return false;
       /* }else*/if($name1=="") {
            $outtext='<p><b>No pushfeed selected!</b></p> <a href="'.$_SERVER['HTTP_REFERER'].'?back=true">back</a>';
            $wgOut->addHTML($outtext);
            return false;
        }

        //$name = $name1[0];
        foreach ($name1 as $name) {// for each pushfeed name==> push
            wfDebugLog('p2p','  -> pushname '.$name);
            // $name = $_GET['name'];//PushFeed name
            $request = getPushFeedRequest($name);
            //        $previousCSID = getPreviousCSID($name);
            $previousCSID = getHasPushHead($name);
            if($previousCSID==false) {
                $previousCSID = "none";
            //$CSID = $name."_0";
            }//else{
            //            $count = explode(" ", $previousCSID);
            //            $cnt = $count[1] + 1;
            //            $CSID = $name."_".$cnt;
            //        }
            wfDebugLog('p2p','  ->pushrequest '.$request);
            wfDebugLog('p2p','  ->pushHead : '.$previousCSID);
            $CSID = utils::generateID();//changesetID
            if($request==false) {
                $outtext='<p><b>No semantic request found!</b></p> <a href="'.$_SERVER['HTTP_REFERER'].'">back</a>';
                $wgOut->addHTML($outtext);
                return false;
            }

            $pages = getRequestedPages($request);//ce sont des pages et non des patches
            foreach ($pages as $page) {
            // wfDebugLog( 'p2p', 'page found '.$age);
                wfDebugLog('p2p','  ->requested page '.$page);
                $request1 = '[[Patch:+]][[onPage::'.$page.']]';
                $tmpPatches = getRequestedPages($request1);
                $patches = array_merge($patches, $tmpPatches);
            }
            $published = getPublishedPatches($name);
            $unpublished = array_diff($patches, $published);/*unpublished = patches-published*/
            if(empty ($unpublished)) {
                $title = Title::newFromText('Special:ArticleAdminPage');
                $article = new Article($title);
                wfDebugLog('p2p','  -> no unpublished patch');
                $article->doRedirect();
                return false; //If there is no unpublished patch
            }
            $pos = strrpos($CSID, ":");//NS removing
            if ($pos === false) {
            // not found...
                $articleName = $CSID;
            }else {
                $articleName = substr($CSID, 0,$pos+1);
                $CSID = "ChangeSet:".$articleName;
            }
            $newtext = "ChangeSet:
changeSetID: [[changeSetID::".$CSID."]]
inPushFeed: [[inPushFeed::".$name."]]
previousChangeSet: [[previousChangeSet::".$previousCSID."]]
";
            //wfDebugLog('p2p','  -> count unpublished patch '.count($unpublished));
            foreach ($unpublished as $patch) {
                wfDebugLog('p2p','  -> unpublished patch '.$patch);
                $newtext.=" hasPatch: [[hasPatch::".$patch."]]";
            }

$newtext.="
----
[[Special:ArticleAdminPage]]";

            $update = updatePushFeed($name, $CSID);
            if($update==true) {// update the "hasPushHead" value successful
                $title = Title::newFromText($articleName, CHANGESET);
                $article = new Article($title);
                $article->doEdit($newtext, $summary="");
                $article->doRedirect();
            }
            else {
                $outtext='<p><b>PushFeed has not been updated!</b></p>';
                $wgOut->addHTML($outtext);
            }
        }//end foreach pushfeed list
        return false;
    }


    //////////PullFeed page////////
    elseif(isset ($_GET['action']) && $_GET['action']=='pullpage') {
        wfDebugLog('p2p','Create pull '.$_POST['pullname'].' with pushName '.$_POST['pushname'].' on '.$_POST['url']);
        $url = rtrim($_POST['url'], "/"); //removes the final "/" if there is one
        if(utils::isValidURL($url)==false)
        throw new MWException( __METHOD__.': '.$url.' seems not to be an url' );//throws an exception if $url is invalid
        $pushname = $_POST['pushname'];//with ns
        $pullname = $_POST['pullname'];

        $newtext = "PullFeed:

name: [[name::PullFeed:".$pullname."]]
pushFeedServer: [[pushFeedServer::".$url."]]
pushFeedName: [[pushFeedName::PushFeed:".$pushname."]]
[[deleted::false| ]]
";
$newtext.="----
[[Special:ArticleAdminPage]]";
        $title = Title::newFromText($pullname, PULLFEED);
        $article = new Article($title);
        $article->doEdit($newtext, $summary="");
        $article->doRedirect();


        return false;
    }

    //////////OnPull/////////////
    elseif(isset ($_POST['action']) && $_POST['action']=='onpull') {

        if(isset ($_POST['pull'])) {
            $name1 = $_POST['pull'];
            wfDebugLog('p2p','pull on ');
            foreach ($_POST['pull'] as $pull) {
                wfDebugLog('p2p',' - '.$pull);
            }
        }
        else $name1="";//throw new MWException( __METHOD__.': no PullName' );
        /*if(count($name)>1) {
            $outtext='<p><b>Select only one pullfeed!</b></p> <a href="'.$_SERVER['HTTP_REFERER'].'?back=true">back</a>';
            $wgOut->addHTML($outtext);
            return false;
        }else*/if($name1=="") {
            $outtext='<p><b>No pullfeed selected!</b></p> <a href="'.$_SERVER['HTTP_REFERER'].'?back=true">back</a>';
            $wgOut->addHTML($outtext);
            return false;
        }

        //$name = $name1[0];//with NS
        foreach ($name1 as $name){// for each pullfeed name==> pull
        wfDebugLog('p2p','      -> pull : '.$name);

        //        $previousCSID = getPreviousPulledCSID($name);
        //        if($previousCSID==false) {
        //            $previousCSID = "none";
        //        }
        $previousCSID = getHasPullHead($name);
        if($previousCSID==false) {
            $previousCSID = "none";
        }
        wfDebugLog('p2p','      -> pullHead : '.$previousCSID);
        $relatedPushServer = getPushURL($name);
        if(is_null($relatedPushServer))throw new MWException( __METHOD__.': no relatedPushServer url' );
        $namePush = getPushName($name);
        wfDebugLog('p2p','      -> pushServer : '.$relatedPushServer);
        wfDebugLog('p2p','      -> pushName : '.$namePush);
        if(is_null($namePush))throw new MWException( __METHOD__.': no PushName' );
        //split NS and name
        preg_match( "/^(.+?)_*:_*(.*)$/S", $namePush, $m );
        $nameWithoutNS = $m[2];


        //$url = $relatedPushServer.'/api.php?action=query&meta=changeSet&cspushName='.$nameWithoutNS.'&cschangeSet='.$previousCSID.'&format=xml';
        $url = $relatedPushServer.'/api.php?action=query&meta=changeSet&cspushName='.$nameWithoutNS.'&cschangeSet='.$previousCSID.'&format=xml';
        wfDebugLog('p2p','      -> request ChangeSet : '.$relatedPushServer.'/api.php?action=query&meta=changeSet&cspushName='.$nameWithoutNS.'&cschangeSet='.$previousCSID.'&format=xml');
        $cs = file_get_contents($relatedPushServer.'/api.php?action=query&meta=changeSet&cspushName='.$nameWithoutNS.'&cschangeSet='.$previousCSID.'&format=xml');
        if($cs===false) throw new MWException( __METHOD__.': Cannot connect to Push Sever (ChangeSet API)' );
        $dom = new DOMDocument();
        $dom->loadXML($cs);

        $changeSet = $dom->getElementsByTagName('changeSet');
        $CSID = null;
        foreach($changeSet as $cs) {
            if ($cs->hasAttribute("id")) {
                $CSID = $cs->getAttribute('id');
                $csName = $CSID;
            }
        }
        wfDebugLog('p2p','     -> changeSet found '.$CSID);
        while($CSID!=null) {
        //if(!utils::pageExist($CSID)) {
            $listPatch = null;
            $patchs = $dom->getElementsByTagName('patch');
            foreach($patchs as $p) {
                wfDebugLog('p2p','          -> patch '.$p->firstChild->nodeValue);
                $listPatch[] = $p->firstChild->nodeValue;
            }
            // $CSID = substr($CSID,strlen('changeSet:'));
            utils::createChangeSetPull($CSID, $name, $previousCSID, $listPatch);

            integrate($CSID, $listPatch,$relatedPushServer);
            updatePullFeed($name, $CSID);

            // }

            $previousCSID = $CSID;
            $cs = file_get_contents($relatedPushServer.'/api.php?action=query&meta=changeSet&cspushName='.$nameWithoutNS.'&cschangeSet='.$previousCSID.'&format=xml');
            $dom = new DOMDocument();
            $dom->loadXML($cs);

            $changeSet = $dom->getElementsByTagName('changeSet');
            $CSID = null;
            foreach($changeSet as $cs) {
                if ($cs->hasAttribute("id")) {
                    $CSID = $cs->getAttribute('id');
                }
            }
            wfDebugLog('p2p','     -> changeSet found '.$CSID);
        }

        if(is_null($csName)) {
            wfDebugLog('p2p','  - redirect to Special:ArticleAdminPage');
            $title = Title::newFromText('Special:ArticleAdminPage');
            $article = new Article($title);
            $article->doRedirect();
        }
        else {
            wfDebugLog('p2p','  - redirect to ChangeSet:'.$csName);
            $title = Title::newFromText($csName, CHANGESET);
            $article = new Article($title);
            $article->doRedirect();
        }
        }//end foreach list pullfeed
        return false;
    }


    //    elseif($action == "admin") {
    //
    //        if(isset($_POST['wiki'])&& isset ($_POST['title'])&& isset ($_POST['id'])) {
    //
    //            $patchArray = $this->getPatches($_POST['id'], $_POST['title'], $_POST['wiki']);
    //            foreach ($patchArray as $patch) {
    //                $this->integratePatch($patch, $article);
    //            }$style = ' style="border-bottom: 2px solid #000;"';
    //            $tableStyle = ' style="float: left; margin-left: 40px;"';
    //            $output = "";
    //
    //            $tables = array("site");
    //            $columns = array("site_id", "site_url", "site_name");
    //            $conditions = '';
    //            $fname = "Database::select";
    //            $options = array(
    //                "ORDER BY" => "site_id",
    //            );
    //            if ($page_limit > 0) {
    //                $options["LIMIT"] = $page_limit;
    //            }
    //            if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
    //                $output .= '<p>Error accessing list.</p>';
    //            } else if($db->numRows($result) == 0) {
    //                    $output .= '<p>No remote site.</p>';
    //                } else {
    //                    $output .= '
    //<FORM METHOD="POST" ACTION="">
    //<table'.$tableStyle.' border>
    //  <tr>
    //    <th colspan="5"'.$style.'>'.$db->numRows($result).' Remote Sites</th>
    //  </tr>
    //  <tr>
    //    <th colspan="2" >Site</th>
    //
    //    <th><input type="submit" value="Push"></th>
    //    <th><input type="submit" value="Pull"></th>
    //    <th><input type="submit" value="Remove"></th>
    //    <input type="hidden" name="ppc" value="true">
    //  </tr>';
    //                    while ($row = $db->fetchRow($result)) {
    //                        $i = $i + 1;
    //                        $output .= '
    //  <tr>
    //    <td>'.$row["site_id"].'</td>
    //    <td title="'.$row["site_url"].'">'.$row["site_name"].'</td>
    //    <td colspan="3" align="center"><input type="checkbox" name="push['.$i.']"/></td>
    //  </tr>';
    //                    }
    //                    $output .= '
    //
    //
    //</table>$id
    //</FORM>';
    //                }
    //
    //        }
    //
    //
    //        $page_title=$_GET['title'];
    //
    //
    //        $wgOut->setPagetitle($page_title.": Administration page");
    //
    //        //adding javascript to page header
    //        $file = dirname($_SERVER['PHP_SELF']).'/extensions/p2pExtension/specialPage/SPFunctions.js';
    //        $wgOut->addScriptFile($file);
    //
    //        $db = &wfGetDB(DB_SLAVE);
    //        $tables = array("site", "site_cnt", "page");
    //        $conditions = array("site.site_id = site_cnt.site_id", "site_cnt.page_title = page.page_title",
    //            "page.page_title='".$_GET['title']."'");
    //        $fname = "Database::select";
    //        $columns = array("site.site_id","site_url","site_name","counter","page.page_title");
    //        $options = array("ORDER BY site.site_id");
    //
    //        $output = "";
    //        if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
    //            $output .= '<p>Error accessing database.</p>';
    //        } else if($db->numRows($result) == 0) {
    //                $output .= '<p>This page is up to date.</p>';
    //            } else {
    //                $style = ' style="border-bottom:2px solid #000; text-align:left;"';
    //                $output .= '<table border cellspacing="0" cellpadding="5"><tr>';
    //
    //
    //
    //                $output .= '<th'.$style.'>Remote site</th><th'.$style.'>Info</th><th'.$style.'>Action</th>';
    //
    //
    //                $output .= '</tr>';
    //
    //                //Display the data--display some data differently than others.
    //                while ($row = $db->fetchRow($result)) {
    //                    $output .= '<tr>';
    //
    //                    $output .= "<td title='yop'>";
    //                    $output .= htmlspecialchars($row['site_name']).'&nbsp;';
    //                    $output .= "</td>";
    //                    $output .= "<td>";
    //                    $output .= htmlspecialchars($row['counter']).'&nbsp;';
    //                    $output .= "</td>";
    //                    $output .= "<td>";
    //                    //                                        $output .= "<button type='button' onclick=\"document.location='".$_SERVER["PHP_SELF"]."?title="
    //                    //                                        .$row['page_title']."&action=admin&wiki=".$row['site_url']."&id=".$row['counter']."'\">PULL</button>".'&nbsp;';
    //                    $output .= "<button type='button' onclick=\"document.location='javascript:process(\'".$row['counter']."\', \'".$row['page_title']."\', \'".$row['site_url']."\')'\">PULL</button>".'&nbsp;';
    //                    $output .= "</td>";
    //                    $output .= '</tr>';
    //                }
    //
    //                $output .= '</table>';
    //            }
    //
    //
    //        $wgOut->addHTML($output);
    //
    //        return false;
    // }
    else {
        return true;
    }


}


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
    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
    $req = '[[ChangeSet:+]] [[inPushFeed::'.$pfname.']]';
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
        $result = str_replace($value, "ChangeSet:".$CSID, $pageContent);
        $pageContent = $result;
        if($result=="")return false;
    }else {//no occurence of [[hasPushHead:: , we add
        $pageContent.= ' hasPushHead: [[hasPushHead::ChangeSet:'.$CSID.']]';
    }
    //save update
    $article = new Article($title);
    $article->doEdit($pageContent, $summary="");

    return true;
}

/**
 * A ChangeSet has patches which has operations
 * this function is used to integrate these operations
 * It's a local changeSet (downloaded from a remote site)
 * @param <String> $changeSetId with NS
 */
function integrate($changeSetId,$patchIdList,$relatedPushServer) {
// $patchIdList = getPatchIdList($changeSetId);
//  $lastPatch = utils::getLastPatchId($pageName);
    wfDebugLog('p2p',' - function integrate : '.$changeSetId);
    foreach ($patchIdList as $patchId) {
        wfDebugLog('p2p','  -> patchId : '.$patchId);
        if(!utils::pageExist($patchId)) {//if this patch exists already, don't apply it
            wfDebugLog('p2p','      -> patch unexist');
            $url = $relatedPushServer.'/api.php?action=query&meta=patch&papatchId='./*substr(*/$patchId/*,strlen('patch:'))*/.'&format=xml';
            wfDebugLog('p2p','      -> getPatch request url '.$url);
            $patch = file_get_contents($url);
            if($patch===false) throw new MWException( __METHOD__.': Cannot connect to Push Sever (Patch API)' );
            wfDebugLog('p2p','      -> patch content :'.$patch);
            $dom = new DOMDocument();
            $dom->loadXML($patch);

            $patchs = $dom->getElementsByTagName('patch');
            //        $patchID = null;
            foreach($patchs as $p) {
                if ($p->hasAttribute("onPage")) {
                    $onPage = $p->getAttribute('onPage');
                }
                if ($p->hasAttribute("previous")) {
                    $previousPatch = $p->getAttribute('previous');
                }
            }

            $operations = null;
            $op = $dom->getElementsByTagName('operation');
            foreach($op as $o)
                $operations[] = $o->firstChild->nodeValue;
            $lastPatch = utils::getLastPatchId($onPage);
            if ($lastPatch==false) $lastPatch='none';

            utils::createPatch($patchId, $onPage, $lastPatch, $operations);

            //            foreach ($operations as $operation) {
            //                $operation = operationToLogootOp($operation);
            //                if ($operation!=false && is_object($operation)) {
            //                    logootIntegrate($operation, $onPage);
            //                }
            //            }
            logootIntegrate($operations, $onPage);
    }//end if pageExists
    }
}

/**
 * used to get an patchId list contained in the changeSet that have the id:
 * $changeSetId
 *
 * @global <Object> $wgServerName
 * @global <Object> $wgScriptPath
 * @param <String> $changeSetId with NS
 * @return <array> a PatchId list
 *
 function getPatchIdList($changeSetId) {
 global $wgServerName, $wgScriptPath;
 $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
 $req = '[[changeSetID::'.$changeSetId.']]';
 $req = utils::encodeRequest($req);
 $url = $url."/Special:Ask/".$req."/-3FhasPatch/headers=hide/format=csv/sep=,/limit=100";
 $string = file_get_contents($url);
 if ($string=="") return false;
 $string = str_replace("\n", ",", $string);
 $string = str_replace("\"", "", $string);
 $res = explode(",", $string);

 foreach ($res as $key=>$resultLine) {
 if(strpos($resultLine, 'ChangeSet:')!==false || $resultLine=="") {
 unset($res[$key]);
 }
 }
 $patchIdList = array_unique($res);
 return $patchIdList;
 }
 *
 /**
 *
 * @global <Object> $wgServerName
 * @global <Object> $wgScriptPath
 * @param <String> $patchId
 * @return <array> an operations list
 *
 function getOperations($patchId) {
 global $wgServerName, $wgScriptPath;
 $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
 $req = '[[patchID::'.$patchId.']]';
 $req = utils::encodeRequest($req);
 $url = $url."/Special:Ask/".$req."/-3FhasOperation/headers=hide/format=csv/sep=,/limit=100";
 $string = file_get_contents($url);
 if ($string=="") return false;
 $string = str_replace("\n", ",", $string);
 $string = str_replace("\"", "", $string);
 $res = explode(",", $string);

 foreach ($res as $key=>$resultLine) {
 if(strpos($resultLine, 'Patch:')!==false || $resultLine=="") {
 unset($res[$key]);
 }
 }
 $operations = array_unique($res);
 return $operations;
 }
 *
 

 /**
 *
 * @global <Object> $wgServerName
 * @global <Object> $wgScriptPath
 * @param <String> $patchId
 * @return <String> article title;
 *
 function getArticleTitleFromPatch($patchId) {
 global $wgServerName, $wgScriptPath;
 $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
 $req = '[[patchID::'.$patchId.']]';
 $req = utils::encodeRequest($req);
 $url = $url."/Special:Ask/".$req."/-3FonPage/headers=hide/format=csv/sep=,/limit=100";
 $string = file_get_contents($url);
 if ($string=="") return false;
 $string = str_replace("\n", ",", $string);
 $string = str_replace("\"", "", $string);
 $res = explode(",", $string);

 foreach ($res as $key=>$resultLine) {
 if(strpos($resultLine, 'Patch:')!==false || $resultLine=="") {
 unset($res[$key]);
 }
 }
 $article = array_unique($res);
 return $article;
 }
 */
/**
 *transforms a string operation from a patch page into a logoot operation
 * insertion or deletion
 * returns false if there is a problem with the type of the operation
 *
 * @param <String> $operation
 * @return <Object> logootOp
 */
function operationToLogootOp($operation) {

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
    }
    $logootPos = new LogootPosition(array($idArrray));

    //    if(strpos($res[3], '-5B-5B')!==false || strpos($res[3], '-5D-5D')!==false) {
    //        $res[3] = utils::decodeRequest($res[3]);
    //    }
    $res[3] = utils::contentDecoding($res[3]);
    //    if($res[3]=="") $res[3]="\r\n";

    if($res[1]=="Insert") {
        $logootOp = new LogootIns('', $logootPos, $res[3]);
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
    if(is_string($article)) {
    //$db = wfGetDB( DB_SLAVE );

        $dbr = wfGetDB( DB_SLAVE );
        $pageid = $dbr->selectField('page','page_id', array(
            'page_title'=>$article));

        $lastRev = Revision::loadFromPageId($dbr, $pageid);
        if(is_null($lastRev)) $rev_id = 0;
        else $rev_id = $lastRev->getId();

        $title = Title::newFromText($article);
        $article = new Article($title);
    }
    else {
        $rev_id = $article->getRevIdFetched();
    }
    $blobInfo = BlobInfo::loadBlobInfo($rev_id);
    foreach ($operations as $operation) {
        $operation = operationToLogootOp($operation);

        if ($operation!=false && is_object($operation)) {

            $blobInfo->integrateBlob($operation);
    }//end if
    }//end foreach operations
    $revId = $blobInfo->getNewArticleRevId();
    $blobInfo->integrate($revId, $sessionId=session_id(), $blobCB=0);
    $status = $article->doEdit($blobInfo->getTextImage(), $summary="");
}

/**
 *
 *
 * @global <Object> $wgServerName
 * @global <Object> $wgScriptPath
 * @param <String> $pfName pullfeed name
 * @return <String> ChanSetId of the last changeset pulled
 */
//function getPreviousPulledCSID($pfName) {
//    global $wgServerName, $wgScriptPath;
//    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
//    $req = '[[ChangeSet:+]] [[inPullFeed::'.$pfName.']]';
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
        $result = str_replace($value, "ChangeSet:".$CSID, $pageContent);
        $pageContent = $result;
        if($result=="")return false;
    }else {//no occurence of [[hasPushHead:: , we add
        $pageContent.= ' hasPullHead: [[hasPullHead::ChangeSet:'.$CSID.']]';
    }
    //save update
    $article = new Article($title);
    $article->doEdit($pageContent, $summary="");

    return true;
}

/**
 *
 * @param <String> $version1
 * @param <String> $version2='1.14.0'
 * @return <integer>
 */
function compareMWVersion($version1, $version2='1.14.0') {
    $version1 = explode(".", $version1);
    $version2 = explode(".", $version2);

    if($version1[0]>$version2[0]) return 1;
    elseif($version1[0]<$version2[0]) return -1;
    elseif($version1[1]>$version2[1]) return 1;
    elseif($version1[1]<$version2[1]) return -1;
    elseif($version1[2]>$version2[2]) return 1;
    elseif($version1[2]<$version2[2]) return -1;
    else return 0;
}

/******************************************************************************/
/*
                V0 : initial revision
               /  \
              /
          P1 /      \P2
            /
           /          \
          V1          V2:2nd edit of the same article
        1st Edit
*/
/******************************************************************************/
function attemptSave($editpage) {
    $ns = $editpage->mTitle->getNamespace();
    if( ($ns == PATCH) || ($ns == PUSHFEED) || ($ns == PULLFEED) || ($ns == CHANGESET))return true;

    //    $pc = new persistentClock();
    //    $pc->load();


    $firstRev = 0;
    $actualtext = $editpage->textbox1;//V2

    $dbr = wfGetDB( DB_SLAVE );
    $lastRevision = Revision::loadFromTitle($dbr, $editpage->mTitle);
    if(is_null($lastRevision)) {
        $conctext = "";
        $rev_id = 0;
        $firstRev = 1;
    }
    else {
        $conctext= $lastRevision->getText();//V1 conc
        $rev_id = $lastRevision->getId();
    }

    $blobInfo = BlobInfo::loadBlobInfo($rev_id);//V1
    $blobInfo->setTextImage($conctext);



    //get the revision with the edittime==>V0
    $rev = Revision::loadFromTimestamp($dbr, $editpage->mTitle, $editpage->edittime);
    if(is_null($rev)) {
        $text = "";
        $rev_id1=0;
        $firstRev = 1;
    }
    else {
        $text = $rev->getText();//VO
        $rev_id1 = $rev->getId();
    }


    if($conctext!=$text) {//if last revision is not V0, there is editing conflict

        $blobInfo1 = BlobInfo::loadBlobInfo($rev_id1);
        $listPos = $blobInfo1->handleDiff($text/*V0*/, $actualtext/*V2*/, $firstRev/*, $pc*/);

        //creation Patch P2
        $tmp = serialize($listPos);
        $patchid = sha1($tmp);
        $patch = new Patch($patchid, $listPos, $blobInfo->getNewArticleRevId(), $editpage->mArticle->getId());
        //$patch->store();//stores the patch in the DB
        $patch->storePage($editpage->mTitle->getText());//stores the patch in a wikipage

        //integration: diffs between VO and V2 into V1
        foreach ($listPos as $operation) {
            $blobInfo->integrateBlob($operation/*, $pc*/);
        }
    }else {//no edition conflict
        $diffs = $blobInfo->handleDiff($conctext, $actualtext, $firstRev/*, $pc*/);
        $tmp = serialize($diffs);
        $patchid = sha1($tmp);
        $patch = new Patch($patchid, $diffs, $blobInfo->getNewArticleRevId(), $editpage->mArticle->getId());
        //$patch->store();//stores the patch in the DB
        $patch->storePage($editpage->mTitle->getText());//stores the patch in a wikipage

    }
    $revId = $blobInfo->getNewArticleRevId();

    //before integration into DB, we must update the "haslastPatch" property
    //$blobInfo->lastPatchPropertyUpdate();

    $blobInfo->integrate($revId, $sessionId=session_id(), $blobCB=0);


    //    $pc->store();
    //    unset($pc);
    $editpage->textbox1 = $blobInfo->getTextImage();
    return true;
}

function javascript($url) {
    $output = '
<SCRIPT language="Javascript">
function processAdd ('.$url.'){
		var xhr_object = null;
	   if(window.XMLHttpRequest) // Firefox
	      xhr_object = new XMLHttpRequest();
	   else if(window.ActiveXObject) // Internet Explorer
	      xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
	   else {
	      alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	      return;
	   }
	   xhr_object.open("POST", '.$url.'+"?site="+document.formAdd.site.value, true);
	   xhr_object.onreadystatechange = function() {
	      if(xhr_object.readyState == 4) {
//alert(xhr_object.responseText);
            //document.location.reload();
	         eval(xhr_object.responseText);
		  }
	   }
	   xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	   var data = "url="+document.formAdd.url.value+"&keyword="+document.formAdd.keyword.value+"&name="+document.formAdd.name.value;
	   xhr_object.send(data);
       document.formAdd.url.value="";
       document.formAdd.name.value="";
       document.formAdd.keyword.value="";
}

</SCRIPT>';
    return $output;
}
