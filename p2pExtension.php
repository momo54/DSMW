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
$wgAutoloadClasses['utils'] = "$wgP2PExtensionIP/files/utils.php";

//global $wgAPIMetaModules;
$wgApiQueryMetaModules = array('patch' => 'ApiQueryPatch');

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

    $script=javascript($_SERVER['HTTP_REFERER']);
    $wgOut->addHeadItem('script', $script);
    wfDebugLog('p2p','onUnknowaction');

    //////////pull form page////////Request:    <br>{{#input:type=textarea|cols=30 | style=width:auto |rows=2|name=keyword}}<br>
    if($_GET['action']=='addpullpage') {

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
    elseif($_GET['action']=='addpushpage') {
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
    elseif($_GET['action']=='pushpage') {
    //$url = $_POST['url'];//pas url mais changesetId
        $name = $_POST['name'];
        $request = $_POST['keyword'];
        $stringReq = utils::encodeRequest($request);//avoid "semantic injection" :))
        //addPushSite($url, $name, $request);


        $newtext = "PushFeed:
Name: [[name::PushFeed:".$name."]]
hasSemanticQuery: [[hasSemanticQuery::".$stringReq."]]
Pages concerned:
{{#ask: ".$request."}}
";


        $title = Title::newFromText($_POST['name'], PUSHFEED);

        $article = new Article($title);
        $article->doEdit($newtext, $summary="");
        $article->doRedirect();


        return false;
    }
///////ChangeSet page////////
    elseif($_POST['action']=='onpush'){
        $patches = array();
        $tmpPatches = array();
        $name = $_POST['push'];
        if(count($name)>1) {
            $outtext='<p><b>Select only one pushfeed!</b></p> <a href="'.$_SERVER['HTTP_REFERER'].'?back=true">back</a>';
            $wgOut->addHTML($outtext);
            return false;
        }elseif($name=="") {
            $outtext='<p><b>No pushfeed selected!</b></p> <a href="'.$_SERVER['HTTP_REFERER'].'?back=true">back</a>';
            $wgOut->addHTML($outtext);
            return false;
        }
        
        $name = $name[0];
        wfDebugLog( 'p2p', 'name push : '.$name);

        // $name = $_GET['name'];//PushFeed name
        $request = getPushFeedRequest($name);
        $previousCSID = getPreviousCSID($name);
        if($previousCSID==false) {
            $previousCSID = "none";
            //$CSID = $name."_0";
          }//else{
//            $count = explode(" ", $previousCSID);
//            $cnt = $count[1] + 1;
//            $CSID = $name."_".$cnt;
//        }
        $CSID = utils::generateID();//changesetID
        if($request==false) {
            $outtext='<p><b>No semantic request found!</b></p> <a href="'.$_SERVER['HTTP_REFERER'].'">back</a>';
            $wgOut->addHTML($outtext);
            return false;
        }
        
        $pages = getRequestedPages($request);//ce sont des pages et non des patches
        foreach ($pages as $page) {
           // wfDebugLog( 'p2p', 'page found '.$age);
            $request1 = '[[Patch:+]][[onPage::'.$page.']]';
            $tmpPatches = getRequestedPages($request1);
            $patches = array_merge($patches, $tmpPatches);
        }
        $published = getPublishedPatches($name);
        $unpublished = array_diff($patches, $published);/*unpublished = patches-published*/
        if(empty ($unpublished)) return false; //If there is no unpublished patch
        $pos = strrpos($CSID, ":");//NS removing
            if ($pos === false) {
                // not found...
                $articleName = $CSID;
            }else{
                $articleName = substr($CSID, 0,$pos+1);
                $CSID = "ChangeSet:".$articleName;
            }
        $newtext = "ChangeSet:
changeSetID: [[changeSetID::".$CSID."]]
inPushFeed: [[inPushFeed::".$name."]]
previousChangetSet: [[previousChangeSet::".$previousCSID."]]
";
        foreach ($unpublished as $patch) {
            $newtext.=" hasPatch: [[hasPatch::".$patch."]]";
        }


            $update = updatePushFeed($name, $CSID);
            if($update==true){// update the "hasPushHead" value successful
            $title = Title::newFromText($articleName, CHANGESET);
            $article = new Article($title);
            $article->doEdit($newtext, $summary="");
            $article->doRedirect();
            }
            else{
                $outtext='<p><b>PushFeed has not been updated!</b></p>';
                $wgOut->addHTML($outtext);
            }

        return false;
    }


    //////////PullFeed page////////
    elseif($_GET['action']=='pullpage') {
        $url = $_POST['url'];
        $pushname = $_POST['pushname'];
        $pullname = $_POST['pullname'];
        
        $newtext = "PullFeed:

name: [[name::PullFeed:".$pullname."]]
pushFeedServer: [[pushFeedServer::".$url."]]
pushFeedName: [[pushFeedName::PushFeed:".$pushname."]]
";

        $title = Title::newFromText($pullname, PULLFEED);
        $article = new Article($title);
        $article->doEdit($newtext, $summary="");
        $article->doRedirect();


        return false;
    }

    //////////OnPull/////////////
    elseif($_GET['action']=='onpull') {
        //onpull test
        $pullFeed = $_POST['name'];
        $CSPush = $_POST['CSPush'];
        $wikiPush = $_POST['wikiPush'];

        $contentCS = file_get_contents($wikiPush.'/'.$CSPush);

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
function getRequestedPages($request){
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
function getPushFeedRequest($pfName){
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
 *Gets the previous changeSet ID
 * @global <String> $wgServerName
 * @global <String> $wgScriptPath
 * @param <String> $pfName PushFeed name
 * @return <String> previous changeSet ID
 */
function getPreviousCSID($pfName){
    global $wgServerName, $wgScriptPath;
    $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php';
    $req = '[[ChangeSet:+]] [[inPushFeed::'.$pfName.']]';
    $req = utils::encodeRequest($req);
    $url = $url."/Special:Ask/".$req."/-3FchangeSetID/headers=hide/order=desc/format=csv/limit=1";
    $string = file_get_contents($url);
    if ($string=="") return false;
    $string = explode(",", $string);
    $string = $string[0];
    $string = str_replace(',', '', $string);
    $string = str_replace("\"", "", $string);
    return $string;
}

/**
 * Gets the published patches
 *
 * @global <String> $wgServerName
 * @global <String> $wgScriptPath
 * @param <String> $pfname PushFeed name
 * @return <array> array of the published patches' name
 */
function getPublishedPatches($pfname){
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

    foreach ($res as $key=>$resultLine){
        if(strpos($resultLine, 'ChangeSet:')!==false || $resultLine==""){
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
function updatePushFeed($name, $CSID){
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
    if ($val1!==false){//if there is an occurence of [[hasPushHead::
    $startVal = $val1 + strlen( $start );
    $end = "]]";
    $endVal = strpos( $pageContent, $end, $startVal );
    $value = substr( $pageContent, $startVal, $endVal - $startVal );

    //update hasPushHead Value
    $result = str_replace($value, "ChangeSet:".$CSID, $pageContent);
    $pageContent = $result;
        if($result=="")return false;
    }else{//no occurence of [[hasPushHead:: , we add
        $pageContent.= ' hasPushHead: [[hasPushHead::ChangeSet:'.$CSID.']]';
    }
    //save update
    $article = new Article($title);
    $article->doEdit($pageContent, $summary="");

    return true;
}

/**
 *
 * @param <String> $changeSetId
 */
function integrate($changeSetId){
    //recup liste des patchesID de ce CS (le tout sera deja en local)
    //foreach patch recuperer les operations
      //foreach operation
      //les transformer en objet LogootOp
      //logootIntegrate(op, titre article recuperé du patch)
      //end foreach operation
    //end foreach patch
}

function getPatchIdList($changeSetId){
    /*todo
     *
     */
    return $patchIdList;
}

function getOperations($patchId){
    /*todo
     *
     */
    return $operations;
}

function operationToLogootOp($op){
    /*todo
     *
     */
    return $logootOp;
}

/**
 *Integrates the operation(LogootOp) into the article via the logoot algorithm
 * 
 * @param <Object> $operation
 * @param <String or Object> $article 
 */
function logootIntegrate($operation, $article){

        if(is_string($article)){
            $db = wfGetDB( DB_SLAVE );
            $pageid = $this->getPageIdWithTitle($article);
            $lastRev = Revision::loadFromPageId($db, $pageid);
            $rev_id = $lastRev->getId();

            $title = Title::newFromText($article);
            $article = new Article($title);
        }
        else{
        $rev_id = $article->getRevIdFetched();
        }
        $blobInfo = BlobInfo::loadBlobInfo($rev_id);

        $blobInfo->integrateBlob($operation);

        $revId = $blobInfo->getNewArticleRevId();
        $blobInfo->integrate($revId, $sessionId=session_id(), $blobCB=0);


        $article->doEdit($blobInfo->getTextImage(), $summary="");
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
        $patch->store();//stores the patch in the DB
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
        $patch->store();//stores the patch in the DB
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
