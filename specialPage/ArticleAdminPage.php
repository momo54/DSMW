<?php


require_once "$IP/includes/SpecialPage.php";
require_once "$wgP2PExtensionIP/files/utils.php";

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
    }

    function getDescription() {
        return "Article Administration Page";
    }


    function execute() {
        global $wgOut, $wgServerName, $wgScriptPath;/*, $wgSitename, $wgCachePages, $wgUser, $wgTitle, $wgDenyAccessMessage, $wgAllowAnonUsers, $wgRequest, $wgMessageCache, $wgWatchingMessages, $wgDBtype, $namespace_titles;*/

        $url = 'http://'.$wgServerName.$wgScriptPath."/index.php";
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

</SCRIPT>';
        $wgOut->addScript($script1);

        ///Special:ArticleAdminPage?FeedDel=true&feed=document.getElementsByName("push[]")[i].value
        if(isset($_GET['FeedDel'])) $this->deleteFeed($_GET['feed']);

        $wgOut->setPagetitle("P2P Administration");


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
<FORM METHOD="POST" ACTION="'./*dirname($_SERVER['HTTP_REFERER'])*/$url.'" name="formPull">
<table'.$tableStyle.' >
  <tr>
    <th colspan="5"'.$style.'>PULL:
  <a href='./*dirname($_SERVER['HTTP_REFERER'])*/$url.'?title=administration_pull_site_addition&action=addpullpage>[Add]</a>';

        if ($pullFeeds!=false) {
            $output .='<a href="javascript:pullFeedDel();">[Remove]</a>
  <button type="submit">[Pull]</button></th>
  </tr>
  <tr>
    <th colspan="2" >Site</th>
    <th >Pages</th>
    <th>Remote <br>Patchs</th>
    <th >Local <br>Patchs</th>


  </tr>
  ';
            foreach ($pullFeeds as $pullFeed) {
                $i = $i + 1;
                $data = //$this->getAwarenessData($row["site_url"]);
                    $output .= '
  <tr>
    <td align="center"><input type="checkbox" id="'.$i.'" name="pull[]" value="'.$pullFeed.'"  /></td>
    <td >'.$pullFeed.'</td>
    <td align="center">[]</td>
    <td align="center">[]</td>
    <td align="center">[]</td>
  </tr>';
            }
        }


        $output .= '

<input type="hidden" name="action" value="onpull">
</table>
</FORM>';



        /////////////PUSHFEEDS TABLE//////////////////////////

        $i=0;
        $req = "[[PushFeed:+]][[deleted::false]]";

        $pushFeeds = $this->getRequestedPages($req);

        $output .= '
<FORM METHOD="POST" ACTION="'./*dirname($_SERVER['HTTP_REFERER'])*/$url.'" name="formPush">
<table'.$tableStyle.' >
  <tr>
    <th colspan="5"'.$style.'>PUSH:
  <a href='./*dirname($_SERVER['HTTP_REFERER'])*/$url.'?title=administration_push_site_addition&action=addpushpage>[Add]</a>';
        if ($pushFeeds!=false) {


            $output .= ' <a href="javascript:pushFeedDel();">[Remove]</a>
  <button type="submit">[Push]</button></th>
  </tr>
  <tr>
    <th colspan="2" >Site</th>
    <th >Pages</th>
    <th>Remote <br>Patchs</th>
    <th >Local <br>Patchs</th>


  </tr>
  ';
            foreach ($pushFeeds as $pushFeed) {
                $i = $i + 1;
                //$this->getAwarenessData($row["site_url"]);
                $output .= '
  <tr>
    <td align="center"><input type="checkbox" id="'.$i.'" name="push[]" value="'.$pushFeed.'" /></td>
    <td >'.$pushFeed.'</td>
    <td align="center">[]</td>
    <td align="center">[]</td>
    <td align="center">[]</td>
  </tr>';
            }
        }
        $output .= '

<input type="hidden" name="action" value="onpush">
</table>
</FORM>';

        if (!$this->getArticle('Property:ChangeSetID')->exists()) {
            $output .='
<FORM METHOD="POST" ACTION="'.$urlServer.'/extensions/p2pExtension/bot/DSMWBot.php" name="scriptExec">
<table'.$tableStyle.'><td><button type="submit"><b>[UPDATE PROPERTY TYPE]</b></button>
</td></table>
<input type="hidden" name="server" value="'.$urlServer.'">
</form>';
        }

        $wgOut->addHTML($output);
        return false;
    }


    function onUnknownAction($action, $article) {
        global $wgOut, $wgSitename, $wgCachePages, $wgLang, $wgUser, $wgTitle,
        $wgDenyAccessMessage, $wgAllowAnonUsers, $wgRequest,$wgMessageCache,
        $wgWatchingMessages, $namespace_titles, $wgSitename,$wgServerName, $wgScriptPath;

        $urlServer = 'http://'.$wgServerName.$wgScriptPath;

        $wgCachePages = false;
        //Verify that the action coming in is "admin"
        if($action == "admin") {
            wfDebugLog('p2p', 'Admin page');
            $title = $article->mTitle->getText();
            wfDebugLog('p2p', ' -> title : '.$title);
            $wgOut->setPagetitle('DSMW on '.$title);

            //part list of patch
            $patchs = utils::orderPatchByPrevious($title);

            $output = '<div><table><caption>List of patchs</caption>';
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

                $op = utils::getSemanticRequest($urlServer,'[[Patch:+]][[patchID::'.$patch.']]','?hasOperation');
                $countOp = utils::countOperation($op);
                $output .= '<td>'.$countOp['insert'].'  insert, '.$countOp['delete'].' delete</td>';
                $output .= '<td>(<a href="'.$_SERVER['PHP_SELF'].'?title='.$patch.'">'.$patch.'</a>)</td></tr>';
                /*$titlePatch = Title::newFromText( $patch,PATCH );
                $article = new Article( $title );*/
            }
            $output .= '</table></div>';

            //part list of push
            $pushs = utils::getSemanticRequest($urlServer,'[[ChangeSet:+]][[hasPatch::'.$patchs[0].']][[inPushFeed::+]]','?inPushFeed');
            $output .= '<div><table><caption>List of pushs</caption>';
            foreach ($pushs as $push) {
                $pushName = explode('!',$push);
                $pushName = $pushName[1];
                $pushName = str_replace(' ', '_', $pushName);
                //all published patch in pushName
               /* $publishedInPush = getPublishedPatches($pushName);
                $pushName = str_replace(' ', '_', $pushName);
                $output .= '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?title='.$pushName.'">'.$pushName.'</a> : </td>';
                $published = null;

                //filtered on published patch on page title
                foreach ($publishedInPush as $patch) {
                    if(count(utils::getSemanticRequest('http://'.$wgServerName.$wgScriptPath,'[[Patch:+]][[patchID::'.$patch.']][[onPage::'.$title.']]',''))) {
                        $published[] = $patch;
                    }
                }*/
                $url = $urlServer.'/api.php?action=query&meta=patchPushed&pppushName='.
                    $pushName.'&pppageName='.$title.'&format=xml';
                $patchXML = file_get_contents($urlServer.'/api.php?action=query&meta=patchPushed&pppushName='.
                    $pushName.'&pppageName='.$title.'&format=xml');
                
                $pushName = str_replace(' ', '_', $pushName);
                $output .= '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?title='.$pushName.'">'.$pushName.'</a> : </td>';
                $dom = new DOMDocument();
                $dom->loadXML($patchXML);
                $patchPublished = $dom->getElementsByTagName('patch');
                $published = null;
                foreach($patchPublished as $p) {
                    $published[] = $p->firstChild->nodeValue;
                }

                //$publishedInPush = utils::getSemanticRequest('http://'.$wgServerName.$wgScriptPath, '', $param);
                if(!is_null($published)) {
                    $unpublished = array_diff($patchs, $published);
                    $output .= '<td>'.count($unpublished).'/'.count($patchs).' unpublished patchs </td></tr>';
                }else {
                    $output .= '<td> '.count($patchs).'/'.count($patchs).' unpublished patchs </td></tr>';
                }
            }
            $output .= '</table></div>';

            //part list of pull
            $pulls = utils::getSemanticRequest($urlServer,'[[ChangeSet:+]][[hasPatch::'.$patchs[0].']][[inPullFeed::+]]','?inPullFeed');
            $output .= '<div><table><caption>List of pull</caption>';
            foreach ($pulls as $pull) {
                $pullName = explode('!',$pull);
                $pullName = $pullName[1];
                $pushServer = getPushURL($pullName);
                $pushName = getPushName($pullName);
                $pushName = str_replace(' ', '_', $pushName);
                $url = $pushServer.'/api.php?action=query&meta=patchPushed&pppushName='.
                    $pushName.'&pppageName='.$title.'&format=xml';
                $patchXML = file_get_contents($pushServer.'/api.php?action=query&meta=patchPushed&pppushName='.
                    $pushName.'&pppageName='.$title.'&format=xml');

                $output .= '<tr><td><a href="'.$_SERVER['PHP_SELF'].'?title='.$pullName.'">'.$pullName.'</a> : </td>';
                $dom = new DOMDocument();
                $dom->loadXML($patchXML);
                $patchPublished = $dom->getElementsByTagName('patch');
                $published = null;
                foreach($patchPublished as $p) {
                    $published[] = $p->firstChild->nodeValue;
                }

                //$publishedInPush = utils::getSemanticRequest('http://'.$wgServerName.$wgScriptPath, '', $param);
                if(!is_null($published)) {
                    $unpublished = array_diff($patchs, $published);
                    $t = count($published);
                    $t = count($patchs);
                    $count = count($published) - count($patchs);
                    $output .= '<td> '.count($patchs).' patchs and '.$count.' unpulled patchs </td></tr>';
                }else {
                    $output .= '<td> up to date </td></tr>';
                }
            }
            $output .= '</table></div>';

            //part push page
            $url = "http://".$wgServerName.$wgScriptPath."/index.php";
            $output .= '
<div><FORM METHOD="POST" ACTION='.$url.' name="formPush">
<table >
  <tr><td> <button type="submit">[Push page : "'.$title.'"]</button></td></tr>
<input type="hidden" name="action" value="onpush"/>
<input type="hidden" name="push" value="PushFeed:PushPage_'.$title.'"/>
<input type="hidden" name="request" value="[['.$title.']]"/>
<input type="hidden" name="page" value="'.$title.'"/></table></form></div>';


            $wgOut->addHTML($output);
            return false;
        }
        //        global $wgOut, $wgSitename, $wgCachePages, $wgLang, $wgUser, $wgTitle, $wgDenyAccessMessage, $wgAllowAnonUsers, $wgRequest,$wgMessageCache, $wgWatchingMessages, $namespace_titles, $wgSitename;
        //        //require_once("WhoIsWatchingTabbed.i18n.php");
        //
        //        $wgCachePages = false;
        //        //Verify that the action coming in is "admin"
        //        if($action == "admin") {
        //
        //            if(isset($_POST['wiki'])&& isset ($_POST['title'])&& isset ($_POST['id'])) {
        //
        //                $patchArray = $this->getPatches($_POST['id'], $_POST['title'], $_POST['wiki']);
        //                foreach ($patchArray as $patch){
        //                     $this->integratePatch($patch, $article);
        //                }$style = ' style="border-bottom: 2px solid #000;"';
        //                $tableStyle = ' style="float: left; margin-left: 40px;"';
        //                $output = "";
        //
        //                $tables = array("site");
        //                $columns = array("site_id", "site_url", "site_name");
        //                $conditions = '';
        //                $fname = "Database::select";
        //                $options = array(
        //            "ORDER BY" => "site_id",
        //                );
        //                if ($page_limit > 0) {
        //                    $options["LIMIT"] = $page_limit;
        //                }
        //                if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
        //                    $output .= '<p>Error accessing list.</p>';
        //                } else if($db->numRows($result) == 0) {
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
        //</table>
        //</FORM>';
        //                }
        //
        //            }
        //
        //
        //            $page_title=$_GET['title'];
        //
        //
        //            $wgOut->setPagetitle($page_title.": Administration page");
        //
        //            //adding javascript to page header
        //            $file = dirname($_SERVER['PHP_SELF']).'/extensions/p2pExtension/specialPage/SPFunctions.js';
        //            $wgOut->addScriptFile($file);
        //
        //            $db = &wfGetDB(DB_SLAVE);
        //            $tables = array("site", "site_cnt", "page");
        //            $conditions = array("site.site_id = site_cnt.site_id", "site_cnt.page_title = page.page_title",
        //                        "page.page_title='".$_GET['title']."'");
        //            $fname = "Database::select";
        //            $columns = array("site.site_id","site_url","site_name","counter","page.page_title");
        //            $options = array("ORDER BY site.site_id");
        //
        //            $output = "";
        //            if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
        //                $output .= '<p>Error accessing database.</p>';
        //            } else if($db->numRows($result) == 0) {
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
        //            $wgOut->addHTML($output);
        //
        //
        //
        //
        //
        //
        //
        //
        //
        //
        //
        //
        //
        //            //
        //            //            $db = &wfGetDB(DB_SLAVE);
        //            //
        //            //            $tables = array("user", "watchlist");
        //            //            $conditions = array("wl_user = user_id", "wl_namespace IN (".implode(", ", array_keys($namespace_titles)).")");
        //            //            $fname = "Database::select";
        //            //
        //            //            //Determine which results are going to be shown and the appropriate columns
        //            //            if(isset($_REQUEST["user_name"])) {
        //            //                $wgOut->setPagetitle(wfMsg("pages_watched_by_user"));
        //            //                $columns = array("wl_namespace","wl_title");
        //            //                $conditions[] = "LOWER(user_name) = " . $db->addQuotes(strtolower($_REQUEST["user_name"]));
        //            //            } else {
        //            //                $wgOut->setPagetitle(wfMsg("users_watching_page"));
        //            //                $columns = array("user_name", "user_real_name");
        //            //                $conditions[] = "LOWER(wl_title) = " . $db->addQuotes(strtolower($page_title));
        //            //            }
        //            //
        //            //            $order_col = "user_name";
        //            //            if(isset($_REQUEST["order_col"]) && in_array($_REQUEST["order_col"], $columns)) {
        //            //                $order_col = $_REQUEST["order_col"];
        //            //            }
        //            //
        //            //            //Change the way the results are ordered
        //            //            if(isset($_REQUEST["order_type"]) && $_REQUEST["order_type"] == "DESC") {
        //            //                $ordertypePOST  = "DESC";
        //            //                $ordertypeR = "ASC";
        //            //            } else {
        //            //                $ordertype  = "ASC";
        //            //                $ordertypeR = "DESC";
        //            //            }
        //            //            $options = array("ORDER BY" => "$order_col $ordertype");
        //            //
        //            //            $output = "";
        //            //            if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
        //            //                $output .= '<p>Error accessing watchlist.</p>';
        //            //            } else if($db->numRows($result) == 0) {
        //            //                $output .= '<p>Nobody is watching this page.</p>';
        //            //            } else {
        //            //                $style = ' style="border-bottom:2px solid #000; text-align:left;"';
        //            //                $output .= '<table cellspacing="0" cellpadding="5"><tr>';
        //            //
        //            //                //Generate sortable column headings
        //            //                foreach($columns as $column){
        //            //                    $output .= '<th'.$style.'><a href="'.$_SERVER["PHP_SELF"].'?title='.$_REQUEST["title"].'&order_col='.$column.'&action=watching&order_type='.$ordertypeR.
        //            //                    (isset(POST$_REQUEST["user_name"]) ? '&user_name='.$_REQUEST["user_name"] : '').
        //            //                    (isset($_REQUEST["user_real_name"]) ? '&user_real_name='.$_REQUEST["user_real_name"] : '').'">'.wfMsg($column).'</a></th>';
        //            //
        //            //                }
        //            //                $output .= '</tr>';
        //            //
        //            //                //Display the data--display some data differently than others.
        //            //                while ($row = $db->fetchRow($result)) {
        //            //                    $output .= '<tr>';
        //            //                    foreach($columns as $column){
        //            //                        $output .= "<td>";
        //            //                        if ($column == "user_name") {
        //            //                            $output .= '<a href="'.$_SERVER["PHP_SELF"].'?title='.$_REQUEST["title"].'&action=watching&user_name='.$row[$column].'">'.$row[$column];
        //            //                        } elseif ($column == "user_real_name") {
        //            //                            $output .= $row[$column];
        //            //                        } elseif ($column == "wl_title") {
        //            //                            $output .= '<a href="'.$_SERVER["PHP_SELF"].'?title='.($row["wl_namespace"]!=0 ? $namespace_titles[$row["wl_namespace"]].':' : '').$row[$column].'&action=watching">'.$row[$column].'</a>';
        //            //                        } elseif ($column == "wl_namespace") {
        //            //                            $output .= $namespace_titles[$row[$column]];
        //            //                        } else {
        //            //                            $output .= htmlspecialchars($row[$column]).'&nbsp;';
        //            //                        }
        //            //                        $output .= "</td>";
        //            //                    }
        //            //                    $output .= '</tr>';
        //            //                }
        //            //                $output .= '</table>';
        //            //            }
        //            //            $wgOut->addHTML($output);
        //            return false;
           /* }*/ else {
            return true;
        }
    }
    //
    function onSkinTemplateTabs(&$skin, &$content_actions) {
        global $wgRequest, $wgServerName, $wgScriptPath;
        $urlServer = 'http://'.$wgServerName.$wgScriptPath;

        $action = $wgRequest->getText("action");
        $db = &wfGetDB(DB_SLAVE);

        $patchCount = 0;
        $patchList = utils::getSemanticRequest($urlServer,'[[Patch:+]][[onPage::'.$skin->mTitle->getText().']]','?patchID');
        $patchCount = count($patchList);
        if($skin->mTitle->mNamespace == PATCH
            || $skin->mTitle->mNamespace == PULLFEED
            || $skin->mTitle->mNamespace == PUSHFEED
            || $skin->mTitle->mNamespace == CHANGESET
        ) {
        }else {

            $content_actions["admin"] = array(
                "class" => ($action == "admin") ? "selected" : false,
                "text" => "Article admin (".$patchCount." patches)",
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
        global $wgServerName, $wgScriptPath;
        $req = utils::encodeRequest($request);
        $url1 = 'http://'.$wgServerName.$wgScriptPath."/index.php/Special:Ask/".$req."/headers=hide/format=csv/sep=,/limit=100";
        $string = file_get_contents($url1);
        $res = explode("\n", $string);
        foreach ($res as $key=>$page) {
            if($page=="") {
                unset ($res[$key]);
            }else {
            //$page = strtr($page, "\"", "\0");
            //            $pos = strrpos($page, ":");//NS removing
            //            if ($pos === false) {
            //                // not found...
            //            }else{
            //                $page = substr($page, $pos+1);
            //            }
                $res[$key] = str_replace("\"", "", $page);
            //$res[$key] = strtr($page, "\"", "");
            }
        }

        return $res;
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
