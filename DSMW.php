<?php

/**
 * @copyright 2009 INRIA-LORIA-ECOO project
 * @author jean-philippe muller
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    exit;
}
require_once "$IP/includes/GlobalFunctions.php";

$wgDSMWIP = dirname( __FILE__ );

require_once 'includes/SemanticFunctions.php';
require_once 'includes/IntegrationFunctions.php';
define('DSMW_VERSION', '0.5');
$wgSpecialPageGroups['ArticleAdminPage'] = 'dsmw_group';
$wgSpecialPageGroups['DSMWAdmin'] = 'dsmw_group';
$wgExtensionMessagesFiles['DSMW'] = $wgDSMWIP . '/languages/DSMW_Messages.php';

$wgHooks['UnknownAction'][] = 'onUnknownAction';
//$wgHooks['MediaWikiPerformAction'][] = 'performAction';

$wgHooks['EditPage::attemptSave'][] = 'attemptSave';
$wgHooks['EditPageBeforeConflictDiff'][] = 'conflict';


$wgAutoloadClasses['logootEngine'] = "$wgDSMWIP/logootComponent/logootEngine.php";
$wgAutoloadClasses['logoot'] = "$wgDSMWIP/logootComponent/logoot.php";
$wgAutoloadClasses['LogootId'] = "$wgDSMWIP/logootComponent/LogootId.php";
$wgAutoloadClasses['LogootPosition'] =
    "$wgDSMWIP/logootComponent/LogootPosition.php";
$wgAutoloadClasses['Diff1']
    = $wgAutoloadClasses['_DiffEngine1']
    = $wgAutoloadClasses['_DiffOp1']
    = $wgAutoloadClasses['_DiffOp_Add1']
    = $wgAutoloadClasses['_DiffOp_Change1']
    = $wgAutoloadClasses['_DiffOp_Copy1']
    = $wgAutoloadClasses['_DiffOp_Delete1']
    = "$wgDSMWIP/logootComponent/DiffEngine.php";

$wgAutoloadClasses['LogootIns'] = "$wgDSMWIP/logootComponent/LogootIns.php";
$wgAutoloadClasses['LogootDel'] = "$wgDSMWIP/logootComponent/LogootDel.php";
$wgAutoloadClasses['boModel'] = "$wgDSMWIP/logootModel/boModel.php";
$wgAutoloadClasses['dao'] = "$wgDSMWIP/logootModel/dao.php";
$wgAutoloadClasses['manager'] = "$wgDSMWIP/logootModel/manager.php";

$wgAutoloadClasses['Patch'] = "$wgDSMWIP/patch/Patch.php";
$wgAutoloadClasses['persistentClock'] = "$wgDSMWIP/clockEngine/persistentClock.php";
$wgAutoloadClasses['ApiQueryPatch'] = "$wgDSMWIP/api/ApiQueryPatch.php";
$wgAutoloadClasses['ApiQueryChangeSet'] = "$wgDSMWIP/api/ApiQueryChangeSet.php";
$wgAutoloadClasses['ApiPatchPush'] = "$wgDSMWIP/api/ApiPatchPush.php";
$wgAutoloadClasses['utils'] = "$wgDSMWIP/files/utils.php";
$wgAutoloadClasses['Math_BigInteger'] = "$wgDSMWIP/logootComponent/Math/BigInteger.php";

///// Register Jobs
$wgJobClasses['DSMWUpdateJob']                  = 'DSMWUpdateJob';
$wgAutoloadClasses['DSMWUpdateJob']             = "$wgDSMWIP/jobs/DSMWUpdateJob.php";

///// credits (see "Special:Version") /////
	$wgExtensionCredits['parserhook'][]= array(
		'path' => __FILE__,
		'name' => 'Distributed&nbsp;Semantic&nbsp;MediaWiki',
		'version' => DSMW_VERSION,
		'author'=> "[http://www.loria.fr/~mullejea Jean&ndash;Philippe&nbsp;Muller], [http://www.loria.fr/~molli Pascal&nbsp;Molli], [http://www.loria.fr/~skaf Hala&nbsp;Skaf&ndash;Molli],
            [http://www.loria.fr/~canals Gérôme&nbsp;Canals], [http://www.loria.fr/~rahalcha Charbel&nbsp;Rahal], [http://www.loria.fr/~weiss Stéphane&nbsp;Weiss], and [http://m3p.gforge.inria.fr/pmwiki/pmwiki.php?n=Site.Team others].",
		'url' => 'http://www.dsmw.org',
		'description' => 'Allows to create a network of Semantic MediaWiki servers that share common semantic wiki pages. ([http://www.dsmw.org www.dsmw.org])',
	);

global $wgVersion;
if(compareMWVersion($wgVersion)==-1) {
    $wgApiQueryMetaModules = array('patch' => 'ApiQueryPatch','changeSet' => 'ApiQueryChangeSet',
        'patchPushed' => 'ApiPatchPush');
}else {
//global $wgAPIMetaModules;
    $wgAPIMetaModules = array('patch' => 'ApiQueryPatch','changeSet' => 'ApiQueryChangeSet',
        'patchPushed' => 'ApiPatchPush');
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
//     $dbr = wfGetDB( DB_SLAVE );
//    $lastRevision = Revision::loadFromTitle($dbr, $title);
//    $rawtext = $lastRevision->getRawText();
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
    global $wgOut, $wgServerName, $wgScriptPath, $wgUser;
    $urlServer = 'http://'.$wgServerName.$wgScriptPath.'/index.php';

    //////////pull form page////////
    if(isset ($_GET['action']) && $_GET['action']=='addpullpage') {
        wfDebugLog('p2p','addPullPage ');
        $newtext = "Add a new site:

{{#form:action=".$urlServer."?action=pullpage|method=POST|
PushServer Url: {{#input:type=button|value=Url test|onClick=
var url = document.getElementsByName('url')[0].value;
var v = new RegExp();
    v.compile('^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$');
if(!v.test(url)){
alert ('You must supply a valid URL.');
        document.getElementsByName('url')[0].focus();}
else{
var xhr_object = null;

	   if(window.XMLHttpRequest) // Firefox
	      xhr_object = new XMLHttpRequest();
	   else if(window.ActiveXObject) // Internet Explorer
	      xhr_object = new ActiveXObject('Microsoft.XMLHTTP');
	   else {
	      alert('Votre navigateur ne supporte pas les objets XMLHTTPRequest...');
	      return;
	   }

	  try{ xhr_object.open('GET', url+'/api.php?action=query&meta=patch&papatchId=1&format=xml', true);}
          catch(e){
                    alert('There is no DSMW Server responding at this URL');
                  }
           xhr_object.onreadystatechange = function() {

if(xhr_object.readyState == 4) {
            if(xhr_object.status==200)
                alert('URL valid, there is a DSMW Server responding');
		  }
	   }

	   xhr_object.send(null);
}
}}<br>        {{#input:type=text|name=url}} <b>e.g. http://server/path</b><br>
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
        $specialAsk = $urlServer.'/Special:Ask';
        $newtext = "Add a new pushfeed:

{{#form:action=".$urlServer."?action=pushpage|method=POST|
PushFeed Name:   <br>    {{#input:type=text|name=name}}<br>
Request: {{#input:type=button|value=Test your query|title=click here to test your query results|onClick=
var query = document.getElementsByName('keyword')[0].value;
var query1 = encodeURI(query);
window.open('".$specialAsk."?q='+query1+'&eq=yes','querywindow','menubar=no, status=no, scrollbars=yes, menubar=no, width=700, height=400');}}
  <br>{{#input:type=textarea|cols=30 | style=width:auto |rows=2|name=keyword}} <b>e.g. [[Category:city]][[locatedIn::France]]</b><br>
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


        $newtext = "
[[Special:ArticleAdminPage|DSMW Admin functions]]

==Features==
[[name::PushFeed:".$name."| ]]
'''Semantic query:''' [[hasSemanticQuery::".$stringReq."| ]]<nowiki>".$request."</nowiki>

'''Pages concerned:'''
{{#ask: ".$request."}}
[[deleted::false| ]]

==Actions==
{{#form:action=".$urlServer."?action=onpush|method=POST|
{{#input:type=hidden|name=push|value=PushFeed:".$name."}}<br>
{{#input:type=hidden|name=action|value=onpush}}<br>
{{#input:type=submit|value=PUSH}}
}}
The \"PUSH\" action publishes the (unpublished) modifications of the articles listed above.

";

        wfDebugLog('p2p','  -> push page contains : '.$newtext);
        $title = Title::newFromText($_POST['name'], PUSHFEED);

        $article = new Article($title);
        $edit = $article->doEdit($newtext, $summary="");
        $article->doRedirect();
        return false;
    }
    ///////ChangeSet page////////
    elseif(isset ($_POST['action']) && $_POST['action']=='onpush') {

        /*In case we push directly from an article page*/
        if(isset ($_POST['page']) && isset ($_POST['request'])) {
            $articlename = Title::newFromText($_POST['push']);

            if(!$articlename->exists()) {
                $result = utils::createPushFeed($_POST['push'], $_POST['request']);

                if ($result==false) {throw new MWException(
                    __METHOD__.': no Pushfeed created in utils:: createPushFeed:
                        name: '.$_POST['push'].' request'. $_POST['request'] );
                }
            }
        }

        wfDebugLog('p2p','push on ');
        $patches = array();
        $tmpPatches = array();
        if(isset ($_POST['push'])) {
            $name1 = $_POST['push'];
            if(!is_array($name1)) $name1 = array ($name1);
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
                wfDebugLog('p2p','  ->requested page '.$page);
                $page = str_replace('"', '', $page);
                $request1 = '[[Patch:+]][[onPage::'.$page.']]';
                $tmpPatches = utils::orderPatchByPrevious($page);
                if(!is_array($tmpPatches))
                throw new MWException( __METHOD__.': $tmpPatches is not an array' );
                $patches = array_merge($patches, $tmpPatches);
                 wfDebugLog('p2p','  -> '.count($tmpPatches).'patchs were found for the page '.$page);
            }
            wfDebugLog('p2p','  -> '.count($patches).' patchs were found for the pushfeed '.$name);
            $published = getPublishedPatches($name);
            $unpublished = array_diff($patches, $published);/*unpublished = patches-published*/
            wfDebugLog('p2p','  -> '.count($published).' patchs were published for the pushfeed '.$name.' and '.count($unpublished).' unpublished patchs');
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
                $CSID = "ChangeSet:".$articleName;
            }else {
                $articleName = substr($CSID, 0,$pos+1);
                $CSID = "ChangeSet:".$articleName;
            }
            $newtext = "
[[Special:ArticleAdminPage|DSMW Admin functions]]

==Features==
[[changeSetID::".$CSID."| ]]

'''Date:''' ".date(DATE_RFC822)."

'''User:''' ".$wgUser->getName()."

This ChangeSet is in : [[inPushFeed::".$name."]]<br>
==Published patches==

{| class=\"wikitable\" border=\"1\" style=\"text-align:left; width:30%;\"
|-
!bgcolor=#c0e8f0 scope=col | Patch
|-
";
            //wfDebugLog('p2p','  -> count unpublished patch '.count($unpublished));
            foreach ($unpublished as $patch) {
                wfDebugLog('p2p','  -> unpublished patch '.$patch);
                $newtext.="|[[hasPatch::".$patch."]]
|-
";
            }
            $newtext.="
|}";
$newtext.="
==Previous ChangeSet==
[[previousChangeSet::".$previousCSID."]]
";

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

        $newtext = "
[[Special:ArticleAdminPage|DSMW Admin functions]]

==Features==

[[name::PullFeed:".$pullname."| ]]
'''URL of the DSMW PushServer:''' [[pushFeedServer::".$url."]]<br>
'''PushFeed name:''' [[pushFeedName::PushFeed:".$pushname."]]
[[deleted::false| ]]

==Actions==
{{#form:action=".$urlServer."?action=onpull|method=POST|
{{#input:type=hidden|name=pull|value=PullFeed:".$pullname."}}<br>
{{#input:type=hidden|name=action|value=onpull}}<br>
{{#input:type=submit|value=PULL}}
}}
The \"PULL\" action gets the modifications published in the PushFeed of the PushFeedServer above.
";

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
            if(!is_array($name1)) $name1 = array ($name1);
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
        foreach ($name1 as $name) {// for each pullfeed name==> pull
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
            $namePush = str_replace(' ', '_', $namePush);
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
            if($cs===false) throw new MWException( __METHOD__.': Cannot connect to Push Server (ChangeSet API)' );
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
                wfDebugLog('p2p','      -> request ChangeSet : '.$relatedPushServer.'/api.php?action=query&meta=changeSet&cspushName='.$nameWithoutNS.'&cschangeSet='.$previousCSID.'&format=xml');
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
    else {
        return true;
    }


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

    $actualtext = $editpage->textbox1;//V2

    $dbr = wfGetDB( DB_SLAVE );
    $lastRevision = Revision::loadFromTitle($dbr, $editpage->mTitle);
    if(is_null($lastRevision)) {
        $conctext = "";
        $rev_id = 0;
    }
    else {
        $conctext= $lastRevision->getText();//V1 conc
        $rev_id = $lastRevision->getId();
    }

    //if there is no modification on the text
    if($actualtext==$conctext) return true;

    $model = manager::loadModel($rev_id);
    $logoot = new logootEngine($model);

    //get the revision with the edittime==>V0
    $rev = Revision::loadFromTimestamp($dbr, $editpage->mTitle, $editpage->edittime);
    if(is_null($rev)) {
        $text = "";
        $rev_id1=0;
    }
    else {
        $text = $rev->getText();//VO
        $rev_id1 = $rev->getId();
    }

    if($conctext!=$text) {//if last revision is not V0, there is editing conflict

        $model1 = manager::loadModel($rev_id1);
        $logoot1 = new logootEngine($model1);
        $listOp1 = $logoot1->generate($text, $actualtext);
        //creation Patch P2
        $tmp = serialize($listOp1);
        $patchid = sha1($tmp);
        $patch = new Patch($patchid, $listOp1, utils::getNewArticleRevId(), $editpage->mArticle->getId());
        if ($editpage->mTitle->getNamespace()==0) $title = $editpage->mTitle->getText();
        else $title = $editpage->mTitle->getNsText().':'.$editpage->mTitle->getText();
        $patch->storePage($title);//stores the patch in a wikipage

        //integration: diffs between VO and V2 into V1

        $modelAfterIntegrate = $logoot->integrate($listOp1);

    }else {//no edition conflict
        $listOp = $logoot->generate($conctext, $actualtext);
        $modelAfterIntegrate = $logoot->getModel();
        $tmp = serialize($listOp);
        $patchid = sha1($tmp);
        $patch = new Patch($patchid, $listOp, utils::getNewArticleRevId(), $editpage->mArticle->getId());        
        if ($editpage->mTitle->getNamespace()==0) $title = $editpage->mTitle->getText();
        else $title = $editpage->mTitle->getNsText().':'.$editpage->mTitle->getText();
        $patch->storePage($title);//stores the patch in a wikipage

    }
    $revId = utils::getNewArticleRevId();
    wfDebugLog('p2p',' -> store model rev : '.$revId.' session '.session_id().' model '.$modelAfterIntegrate->getText());
    manager::storeModel($revId, $sessionId=session_id(), $modelAfterIntegrate, $blobCB=0);

    $editpage->textbox1 = $modelAfterIntegrate->getText();
    return true;
}
