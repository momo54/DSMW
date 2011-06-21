<?php

/**
 * DSMW Special page
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author  Hantz Marlene - jean-Philippe Muller
 */
require_once "$IP/includes/SpecialPage.php";
require_once "$wgDSMWIP/files/utils.php";

/* Extension variables */
$wgExtensionFunctions[] = "wfSetupAdminPage";

class ArticleAdminPage extends SpecialPage {
// Constructor
    function ArticleAdminPage() {
        global $wgHooks, $wgSpecialPages, $wgWatchingMessages;
        # Add all our needed hooks
        $wgHooks["UnknownAction"][] = $this;
        $wgHooks["SkinTemplateTabs"][] = $this;
        SpecialPage::SpecialPage('ArticleAdminPage'/*, "block"*/);// avec block => pasges speciales restreintes
        wfLoadExtensionMessages('DSMW');
    }
    
    function getDescription() {
        return "DSMW Admin functions";
    }

    /**
     * Executed the user opens the DSMW administration special page
     * Calculates the PushFeed list and the pullfeed list (and everything that
     * is displayed on the psecial page
     *
     * @global <Object> $wgOut Output page instance
     * @global <String> $wgServerName
     * @global <String> $wgScriptPath
     * @return <bool>
     */
    function execute() {
        global $wgOut, $wgServerName, $wgScriptPath, $wgScriptExtension;/*, $wgSitename, $wgCachePages, $wgUser, $wgTitle, $wgDenyAccessMessage, $wgAllowAnonUsers, $wgRequest, $wgMessageCache, $wgWatchingMessages, $wgDBtype, $namespace_titles;*/

        $url = 'http://'.$wgServerName.$wgScriptPath."/index{$wgScriptExtension}";
        $urlServer = 'http://'.$wgServerName.$wgScriptPath;
        //$wgOut->addHeadItem('script', ArticleAdminPage::javascript());


        $script1 = '<SCRIPT type="text/javascript"> function pushFeedDel(){
  for (var i=0; i < document.getElementsByName("push[]").length; i++)
     {
     if (document.getElementsByName("push[]")[i].checked)
        {
            //alert(document.getElementsByName("push[]")[i].value);
            var feed = document.getElementsByName("push[]")[i].value;
            window.location.href = "'.$url.'/Special:ArticleAdminPage?FeedDel=true&feed="+feed;
        }
     }
}

function pullFeedDel(){
  for (var i=0; i < document.getElementsByName("pull[]").length; i++)
     {
     if (document.getElementsByName("pull[]")[i].checked)
        {
           // alert(document.getElementsByName("pull[]")[i].value);
            var feed = document.getElementsByName("pull[]")[i].value;
            window.location.href = "'.$url.'/Special:ArticleAdminPage?FeedDel=true&feed="+feed;
        }
     }
}
function displayRemotePatch(test){
//alert(test);
if(test==true){
  window.location.href = "'.$url.'/Special:ArticleAdminPage?display=true";
}else{
  window.location.href = "'.$url.'/Special:ArticleAdminPage";
}
}
</SCRIPT>';
        $wgOut->addScript($script1);

        ///Special:ArticleAdminPage?FeedDel=true&feed=document.getElementsByName("push[]")[i].value
        if(isset($_GET['FeedDel'])) $this->deleteFeed($_GET['feed']);

        $wgOut->setPagetitle("DSMW Administration");


        //Set the limit of rows returned
        $page_limit = 30;
        $i = 0;
        $db   = &wfGetDB(DB_SLAVE);

        $style = ' style="border-bottom: 2px solid #000;"';
        $tableStyle = ' style=" clear:both; float: left; margin-left: 40px; margin-top: 20px"';
        $output = "";



        /////////////PULLFEEDS TABLE//////////////////////////
        $i=0;
        $req = "[[PullFeed:+]][[deleted::false]]";


        $pullFeeds = $this->getRequestedPages($req);


        $output .= '
<FORM name="formPull">

<table'.$tableStyle.' >
  <a href="javascript:displayRemotePatch(true);">[Display]</a><a href="javascript:displayRemotePatch(false);">[Hide]</a> remote patches
  <tr bgcolor=#c0e8f0>';
if(isset ($_GET['display'])){$output .= '    <th colspan="5"'.$style.'>PULL:
  <a href='./*dirname($_SERVER['HTTP_REFERER'])*/$url.'?title=administration_pull_site_addition&action=addpullpage>[Add]</a>';
}else{
    $output .= '    <th colspan="4"'.$style.'>PULL:
  <a href='./*dirname($_SERVER['HTTP_REFERER'])*/$url.'?title=administration_pull_site_addition&action=addpullpage>[Add]</a>';
}
        if ($pullFeeds!=false) {
            $output .='<a href="javascript:pullFeedDel();">[Remove]</a>
  <input type="button" value="PULL" onClick="allpull(\''.$url.'\');"></input></th>
  </tr>
  <tr>
    <th colspan="2" >Site</th>
    <th >Pages</th>';
if(isset ($_GET['display'])){$output .='    <th>Remote <br>Patches</th>';}
$output .='    <th >Local <br>Patches</th>


  </tr>
  ';
            foreach ($pullFeeds as $pullFeed) {
                $i = $i + 1;
                $pullFeed = str_replace(' ', '_', $pullFeed);

                //count the number of local page concerned by the current pullFeed
                $tabPage = utils::getPageConcernedByPull($pullFeed);

                //if connection failed
                if($tabPage===false)$pageConcerned="-";
                else $pageConcerned = count($tabPage);

                if(isset ($_GET['display'])){
                //count the number of remote patch concerned by the current pullFeed
                $pushServer = getPushURL($pullFeed);
                $pushName = getPushName($pullFeed);

                $published = utils::getPublishedPatchs($pushServer, $pushName);
                //if connection failed
                if($published===false) $countRemotePatch="-";
                else $countRemotePatch = count($published);
                }

                //count the number of local patch concerned by the current pullFeed
                
                $results = array();
                $pulledCS = utils::getSemanticQuery('[[ChangeSet:+]][[inPullFeed::'.$pullFeed.']]', '?hasPatch');
                if($pulledCS===false) $countPulledPatch="-";
                else {
                    $count = $pulledCS->getCount();
                    for($i=0; $i<$count; $i++) {

                        $row = $pulledCS->getNext();
                        if ($row===false) break;
                        $row = $row[1];

                        $col = $row->getContent();//SMWResultArray object
                        foreach($col as $object) {//SMWDataValue object
                            $wikiValue = $object->getWikiValue();
                            $results[] = $wikiValue;
                        }
                    }
                    $countPulledPatch = count($results);
                }
               
               $pullFeedLink = str_replace(' ', '_', $pullFeed);
                    $output .= '
  <tr>
    <td align="center"><input type="checkbox" id="'.$i.'" name="pull[]" value="'.$pullFeed.'"  /></td>
    <td ><a href='.$url.'?title='.$pullFeedLink.'>'.$pullFeed.'</a></td>
    <td align="center" title="Number of locally concerned pages">['.$pageConcerned.']</td>';
               if(isset ($_GET['display'])){ $output .= '
    <td align="center" title="Published patches">['. $countRemotePatch.']</td>';}
$output .= '    <td align="center" title="Local patches">['.$countPulledPatch.']</td>
  </tr>';
            }
        }


        $output .= '

</table>
</FORM>';



        /////////////PUSHFEEDS TABLE//////////////////////////

        $i=0;
        $req = "[[PushFeed:+]][[deleted::false]]";

        $pushFeeds = $this->getRequestedPages($req);

        $output .= '
<FORM name="formPush">
<table'.$tableStyle.'  >
  <tr bgcolor=#c0e8f0>
    <th colspan="6"'.$style.'>PUSH:
  <a href='./*dirname($_SERVER['HTTP_REFERER'])*/$url.'?title=administration_push_site_addition&action=addpushpage>[Add]</a>';
        if ($pushFeeds!=false) {


            $output .= ' <a href="javascript:pushFeedDel();">[Remove]</a>
  <input type="button" value="PUSH" onClick="allpush(\''.$url.'\');"></input></th>
  </tr>
  <tr>
    <th colspan="2" >Site</th>
    <th >Pages</th>
    <th>All patches</th>
    <th>Published <br>Patches</th>
    <th >Unpublished <br>Patches</th>


  </tr>
  ';
            foreach ($pushFeeds as $pushFeed) {
                $i = $i + 1;

                $pushName = str_replace(' ', '_', $pushFeed);
                //count the number of page concerned by the current pushFeed
                $request = getPushFeedRequest($pushName);


                
                $results1 = array();
                $tabPage = utils::getSemanticQuery($request);
                //if connection failed
                if($tabPage===false){
                    $countConcernedPage="-";
                    $countPatchs="-";
                }
                else {

                $count = $tabPage->getCount();
                    for($i=0; $i<$count; $i++) {

                        $row = $tabPage->getNext();
                        if ($row===false) break;
                        $row = $row[0];

                        $col = $row->getContent();//SMWResultArray object
                        foreach($col as $object) {//SMWDataValue object
                            $wikiValue = $object->getWikiValue();
                            $results1[] = $wikiValue;
                        }
                    }
                $countConcernedPage = count($results1);

                //count the number of patchs from the page concerned
                $countPatchs = 0;
                foreach ($results1 as $page) {
                    $results = array();
                    $patchs = utils::getSemanticQuery('[[Patch:+]][[onPage::'.$page.']]','?patchID');

                    $count = $patchs->getCount();
                    for($i=0; $i<$count; $i++) {
                        
                        $row = $patchs->getNext();
                        if ($row===false) break;
                        $row = $row[0];
                        
                        $col = $row->getContent();//SMWResultArray object
                        foreach($col as $object) {//SMWDataValue object
                            $wikiValue = $object->getWikiValue();
                            $results[] = $wikiValue;
                        }
                    }
                    
                    $countPatchs += count($results);
                }
                }

                //count the number of patchs published by the current pushFeed
                $published = utils::getPublishedPatchs($urlServer, $pushName);

                if($published===false) $countPublished="-";
                else $countPublished = count($published);

                //count the number of patchs unpublished
                if($results1===false || $published===false) $countUnpublished="-";
                else $countUnpublished = $countPatchs - $countPublished;

                $pushFeedLink = str_replace(' ', '_', $pushFeed);

                $output .= '
  <tr>
    <td align="center"><input type="checkbox" id="'.$i.'" name="push[]" value="'.$pushFeed.'" /></td>
    <td ><a href='.$url.'?title='.$pushFeedLink.'>'.$pushFeed.'</a></td>
    <td align="center" title="Number of concerned pages">['.$countConcernedPage.']</td>
    <td align="center" title="Sum of all the patches">['.$countPatchs.']</td>
    <td align="center" title="Published patches">['.$countPublished.']</td>
    <td align="center" title="Unpublished patches">['.$countUnpublished.']</td>
  </tr>';
            }
        }
        $output .= '

</table>
</FORM>
<div id="pullstatus" style="display: none; width: 100%; clear: both;" >
<a name="PULL_Progress_:" id="PULL_Progress_:"></a><h2> <span class="mw-headline"> PULL Progress&nbsp;: </span></h2>
<div id="statepull" ></div><br />
</div>
<div id="pushstatus" style="display: none; width: 100%; clear: both;" >
<a name="PUSH_Progress_:" id="PUSH_Progress_:"></a><h2> <span class="mw-headline"> PUSH Progress&nbsp;: </span></h2>
<div id="statepush" ></div><br />
</div>
';

//        if (!$this->getArticle('Property:ChangeSetID')->exists()) {
//            $output .='
//<FORM METHOD="POST" ACTION="'.$urlServer.'/extensions/DSMW/bot/DSMWBot.php" name="scriptExec">
//<table'.$tableStyle.'><td><button type="submit"><b>[UPDATE PROPERTY TYPE]</b></button>
//</td></table>
//<input type="hidden" name="server" value="'.$urlServer.'">
//</form>';
//        }

        $wgOut->addHTML($output);
        return false;
    }

    /**
     * $action=admin is generated when the administration tab is clicked
     * Calculates every that is displayed on this page (cf user manual)
     *
     * @global <Object> $wgOut output page instance
     * @global <Object> $wgCachePages
     * @global <String> $wgServerName
     * @global <String> $wgScriptPath
     * @param <String> $action
     * @param <Object> $article
     * @return <bool>
     */
    function onUnknownAction($action, $article) {
        global $wgOut, $wgCachePages, $wgServerName, $wgScriptPath, $wgScriptExtension;

        $urlServer = 'http://'.$wgServerName.$wgScriptPath;

        $wgCachePages = false;
        //Verify that the action coming in is "admin"
        if($action == "admin") {
            wfDebugLog('p2p', 'Admin page');
            if($article->mTitle->getNamespace()==0) $title=$article->mTitle->getText();
            else $title = $article->mTitle->getNsText().':'.$article->mTitle->getText();
            wfDebugLog('p2p', ' -> title : '.$title);
            $wgOut->setPagetitle('DSMW on '.$title);

            //part list of patch
            $patchs = utils::orderPatchByPrevious($title);
$wgOut->addWikiText('[[Special:ArticleAdminPage|DSMW Admin functions]]

==Features==');
            $output = '<div style="width:60%;height:40%;overflow:auto;">
<table style="border-bottom: 2px solid #000;">
<caption><b>List of patchs</b></caption>';

            /////////////////////////BEN/////////////////////////
            $cptID = 0;//used to create the checkboxs
            $countPatchs = 0;//we need to remenber the number of patchs
            /////////////////////////BEN/////////////////////////
            
            //color the remote patch of the current page
            foreach ($patchs as $patch) {
                wfDebugLog('p2p','  -> patchId : '.$patch);
                if(!utils::isRemote($patch)) {
                    wfDebugLog('p2p','      -> remote patch');
                    $output .= '<tr BGCOLOR="#CCCCCC"><td>'.wfTimestamp(TS_RFC2822, $article->getTimestamp()).' : </td>';
                // $output .= '<tr><td BGCOLOR="#33CC00"><a href="'.$_SERVER['PHP_SELF'].'?title='.$patch.'">'.$patch.'</a></td>';
                }else {
                    wfDebugLog('p2p','      -> local patch');
                    $output .= '<tr><td>'.wfTimestamp(TS_RFC2822, $article->getTimestamp()).' : </td>';
                //$output .= '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?title='.$patch.'">'.$patch.'</a></td>';

                }

                //count the number of delete and insert operations into the patch

               
                $results = array();
                $op = utils::getSemanticQuery('[[Patch:+]][[patchID::'.$patch.']]','?hasOperation');
                $count = $op->getCount();
                for($i=0; $i<$count; $i++) {

                    $row = $op->getNext();
                    if ($row===false) break;
                    $row = $row[1];

                    $col = $row->getContent();//SMWResultArray object
                    foreach($col as $object) {//SMWDataValue object
                        $wikiValue = $object->getWikiValue();
                        $results[] = $wikiValue;
                    }
                }


                $countOp = utils::countOperation($results);//old code passed $op parameter
                $output .= '<td>'.$countOp['insert'].'  insert, '.$countOp['delete'].' delete</td>';

                /////////////////////////BEN/////////////////////////
                $output .= '<td>(<a href="'.$_SERVER['PHP_SELF'].'?title='.$patch.'">'.$patch.'</a>)</td>';
                $output .= '<td><input type="checkbox" id="check'.$cptID.'" value="'.$patch.'"/></td></tr>';
                $cptID += 1;
                $countPatchs += 1;
                /////////////////////////BEN/////////////////////////
            }
            $output .= '</table></div>';

            //list of push

            
            $pushs = array();
            $res = utils::getSemanticQuery('[[ChangeSet:+]][[hasPatch::'.$patchs[0].']][[inPushFeed::+]]', '?inPushFeed');
            $count = $res->getCount();
            for($i=0; $i<$count; $i++) {

                $row = $res->getNext();
                if ($row===false) break;
                $row = $row[1];

                $col = $row->getContent();//SMWResultArray object
                foreach($col as $object) {//SMWDataValue object
                    $wikiValue = $object->getWikiValue();
                    $pushs[] = $wikiValue;
                }
            }


            if(!empty ($pushs)) {

                $output .= '<br><div style="width:60%;height:40%;overflow:auto;"><table style="border-bottom: 2px solid #000;"><caption><b>List of pushs</b></caption>';
                foreach ($pushs as $push) {
                    
                      $pushName = $push;

                    $output .= '<tr><td align="right" width="50%"><a href="'.$_SERVER['PHP_SELF'].'?title='.$pushName.'">'.$pushName.'</a> : </td>';

                    //count the number of published patchs by the current pushFeed for the current page
                    $published = utils::getPublishedPatchs($urlServer, $pushName, $title);

                    //$publishedInPush = utils::getSemanticRequest('http://'.$wgServerName.$wgScriptPath, '', $param);
                    //count the number of unpublished patchs
                    $unpublished = array_diff($patchs, $published);
                    if(!is_null($unpublished) && count($unpublished)>0) {
                        $output .= '<td align="left" width="50%">'.count($unpublished).' unpublished patchs on '.count($patchs).' </td></tr>';
                    }else {
                        $output .= '<td align="left" width="50%"> all '.$title."'".'patchs are pushed </td></tr>';
                    }
                }
                $output .= '</table></div>';

            }//end if empty $pushs

            //part list of pull

            $pulls = array();
            $res = utils::getSemanticQuery('[[ChangeSet:+]][[hasPatch::'.$patchs[0].']][[inPullFeed::+]]', '?inPullFeed');
            $count = $res->getCount();
            for($i=0; $i<$count; $i++) {

                $row = $res->getNext();
                if ($row===false) break;
                $row = $row[1];

                $col = $row->getContent();//SMWResultArray object
                foreach($col as $object) {//SMWDataValue object
                    $wikiValue = $object->getWikiValue();
                    $pulls[] = $wikiValue;
                }
            }


            if(!empty ($pulls)) {

                $output .= '<br><div style="width:60%;height:40%;overflow:auto;"><table style="border-bottom: 2px solid #000;"><caption><b>List of pull</b></caption>';
                foreach ($pulls as $pull) {

                     
                    $pullName = $pull;                   
                    $pushServer = getPushURL($pullName);
                    $pushName = getPushName($pullName);
                    $output .= '<tr><td align="right" width="50%"><a href="'.$_SERVER['PHP_SELF'].'?title='.$pullName.'">'.$pullName.'</a> : </td>';

                    $pulledPatch = utils::getPulledPatches($pullName);
                    $patchs = array();
                    foreach ($pulledPatch as $patch) {
                        
                        $onPage = array();
                        $res = utils::getSemanticQuery('[[Patch:+]][[patchID::'.$patch.']]','?onPage');
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
                        if($onPage[0]==$title){
                            $patchs[] = $patch;
                        }
                    }

                    $published = utils::getPublishedPatchs($pushServer, $pushName, $title);

                    if(!is_null($published)) {
                        $unpublished = array_diff($patchs, $published);
                        $t = count($published);
                        $t = count($patchs);
                        $count = count($published) - count($patchs);
                        $output .= '<td align="left" width="50%"> '.$count.' unpulled patchs </td></tr>';
                    }else {
                        $output .= '<td align="left" width="50%"> up to date </td></tr>';
                    }
                }
                $output .= '</table></div>';

            }//end if empty $pulls

            //part push page
            $url = "http://".$wgServerName.$wgScriptPath."/index{$wgScriptExtension}";
            $output .= '
<h2>Actions</h2>
<div><FORM  name="formPush">
<table >
<!--/////////////////////////BEN/////////////////////////-->
    <tr>
        <td> <input type="button" value="PUSH" onClick="pushpage(\''.$url.'\',\''.$title.'\');"></input></td>
        <td>This [Push page : "'.$title.'"] action will create a PushFeed and publish the modifications of the "'.$title.'" article</td>
    </tr>
    <tr>
        <td> <input type="button" value="UNDO" onClick="undopatchs(\''.$url.'\',\''.$title.'\','.$countPatchs.');"></input></td>
        <td>This action will undo the selected patchs.</td>
    </tr>
</table>
</form>
</div>

<div id="pushstatus" style="display: none; width: 100%; clear: both;" >
<a name="PUSH_Progress_:" id="PUSH_Progress_:"></a><h2> <span class="mw-headline"> PUSH Progress&nbsp;: </span></h2>
<div id="statepush" ></div><br />
</div>
<div id="undostatus" style="display: none; width: 100%; clear: both;" >
<a name="UNDO_Progress_:" id="UNDO_Progress_:"></a><h2> <span class="mw-headline"> UNDO Progress&nbsp;: </span></h2>
<div id="stateundo" ></div><br />
</div>
<!--/////////////////////////BEN/////////////////////////-->
';


            $wgOut->addHTML($output);
            return false;
        }
        else {
            return true;
        }
    }
    /**
     * Defines the "Article Admin tab"
     *
     * @global <type> $wgRequest
     * @global <type> $wgServerName
     * @global <type> $wgScriptPath
     * @param <type> $skin
     * @param <type> $content_actions
     * @return <type>
     */
    function onSkinTemplateTabs($skin, $content_actions) {
        global $wgRequest, $wgServerName, $wgScriptPath;
        $urlServer = 'http://'.$wgServerName.$wgScriptPath;

        $action = $wgRequest->getText("action");
        $db = &wfGetDB(DB_SLAVE);

        $patchCount = 0;
        if($skin->mTitle->getNamespace()==0) $title = $skin->mTitle->getText();
        else $title = $skin->mTitle->getNsText().':'.$skin->mTitle->getText();

        $patchList = array();
        $res = utils::getSemanticQuery('[[Patch:+]][[onPage::'.$title.']]', '?patchID');
        $count = $res->getCount();
        for($i=0; $i<$count; $i++) {

            $row = $res->getNext();
            if ($row===false) break;
            $row = $row[1];

            $col = $row->getContent();//SMWResultArray object
            foreach($col as $object) {//SMWDataValue object
                $wikiValue = $object->getWikiValue();
                $patchList[] = $wikiValue;
            }
        }

        $patchCount = count($patchList);
        if($skin->mTitle->mNamespace == PATCH
            || $skin->mTitle->mNamespace == PULLFEED
            || $skin->mTitle->mNamespace == PUSHFEED
            || $skin->mTitle->mNamespace == CHANGESET
        ) {
        }else {

            $content_actions["admin"] = array(
                "class" => ($action == "admin") ? "selected" : false,
                "text" => "DSMW (".$patchCount." patches)",
                "href" => $skin->mTitle->getLocalURL("action=admin")
            );
        }
        return false;
    }


    /**
     *replaces the deleted semantic attribute in the feed page (pullfeed:.... or
     * pushfeed:....)
     * This aims to "virtualy" delete the article, it will no longer appear in the
     * special page (Special:ArticleAdminPage)
     *
     * @param <String> $feed
     * @return <boolean>
     */
    function deleteFeed($feed) {
    //if the browser page is refreshed, feed keeps the same value
    //but [[deleted::false| ]] isn't found and nothing is done
        preg_match( "/^(.+?)_*:_*(.*)$/S", $feed, $m );
        $articleName = $m[2];
        if($m[1]=="PullFeed") $title = Title::newFromText($articleName, PULLFEED);
        elseif($m[1]=="PushFeed") $title = Title::newFromText($articleName, PUSHFEED);
        else throw new MWException( __METHOD__.': no valid namespace detected' );
        //get PushFeed by name

        $dbr = wfGetDB( DB_SLAVE );
        $revision = Revision::loadFromTitle($dbr, $title);
        $pageContent = $revision->getText();

        $dbr = wfGetDB( DB_SLAVE );
        $revision = Revision::loadFromTitle($dbr, $title);
        $pageContent = $revision->getText();

        //update deleted Value
        $result = str_replace("[[deleted::false| ]]", "[[deleted::true| ]]", $pageContent);
        if($result=="") return true;
        $pageContent = $result;

        //save update
        $article = new Article($title);
        $article->doEdit($pageContent, $summary="");

        return true;
    }

    /**
     * @param <String> $title
     * @return <String>
     */
    function getPageIdWithTitle($title) {
        $dbr = wfGetDB( DB_SLAVE );
        $id = $dbr->selectField('page','page_id', array(
            'page_title'=>$title));
        return $id;
    }

    /**
     * returns an array of page titles received via the request
     */
    function getRequestedPages($request) {

        $results = array();
        $res = utils::getSemanticQuery($request);
        $count = $res->getCount();
        for($i=0; $i<$count; $i++) {

            $row = $res->getNext();
            if ($row===false) break;
            $row = $row[0];

            $col = $row->getContent();//SMWResultArray object
            foreach($col as $object) {//SMWDataValue object
                $wikiValue = $object->getWikiValue();
                $results[] = $wikiValue;
            }
        }

        return $results;
    }

    function getArticle( $article_title ) {
        $title = Title::newFromText( $article_title );

        // Can't load page if title is invalid.
        if ($title == null)     return null;
        $article = new Article($title);

        return $article;
    }


} //end class

/* Global function */
# Called from $wgExtensionFunctions array when initialising extensions
function wfSetupAdminPage() {
    global $wgUser;
    SpecialPage::addPage( new ArticleAdminPage );
    if ($wgUser->isAllowed("ArticleAdminPage")) {
        global $wgArticleAdminPage;
        $wgArticleAdminPage = new ArticleAdminPage();
    }
}


 ?>
