<?php


require_once "$IP/includes/SpecialPage.php";

/* Global variables */
$wgAllowAnonUsers = false; # set to false to deny access to anonymous users

/* Extension variables */
$wgExtensionFunctions[] = "wfSetupAdminPage";

$namespace_titles = array(
    NS_MEDIA     =>"Media",
    NS_SPECIAL   =>"Special",
    NS_MAIN      =>"Main",
    NS_USER      =>"User",
    NS_IMAGE     =>"Image",
    NS_MEDIAWIKI =>"MediaWiki",
    NS_TEMPLATE  =>"Template",
    NS_HELP      =>"Help",
    NS_CATEGORY  =>"Category",
    NS_PROJECT   =>str_replace(" ","_",$wgSitename)
);

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
        global $wgOut, $wgSitename, $wgCachePages, $wgUser, $wgTitle, $wgDenyAccessMessage, $wgAllowAnonUsers, $wgRequest, $wgMessageCache, $wgWatchingMessages, $wgDBtype, $namespace_titles;

        $wgOut->addHeadItem('script', ArticleAdminPage::javascript());

        if($_GET['site']==true){// ce post en ajax
            $url = $_POST['url'];
            $name = $_POST['name'];
            $keyword = $_POST['keyword'];
            $res = $this->addSite($url, $name, $keyword);

        }
        elseif($_GET['value']=="Remove"/*isset ($_POST['pullremove'])*/){
            foreach ($_POST['pull'] as $url){
                $this->deleteSite($url);
            }

        }
        elseif($_GET['value']=="Pull"/*isset ($_POST['pullremove'])*/){
            foreach ($_POST['pull'] as $url){
                $titleArray = $this->getSitePageTitles($url);

                   foreach ($titleArray as $title=>$counter){

                    $patchArray = $this->getPatches($counter, $title, $url);
                            foreach ($patchArray as $patch){
                                $this->integratePatch($patch, $title);
                            }


                   }
            }

        }



        $wgOut->setPagetitle("P2P Administration");


        //Set the limit of rows returned
        $page_limit = 30;
        $i = 0;
        $db   = &wfGetDB(DB_SLAVE);

        $style = ' style="border-bottom: 2px solid #000;"';
        $tableStyle = ' style=" clear:both; float: left; margin-left: 40px; margin-top: 20px"';
        $output = "";






//$tableStyle1 = ' style="clear:both; float: left; margin-left: 40px; "';
//        $output .= '
//
//<table'.$tableStyle1.' border>
//  <tr>
//    <th colspan="5"'.$style.'><FORM METHOD="GET" ACTION="'.dirname($_SERVER['HTTP_REFERER']).'">
//<INPUT TYPE="submit" VALUE="ADD">
//<INPUT TYPE="hidden" name="title" VALUE="administration_pull_site_addition">
//<INPUT TYPE="hidden" name="action" VALUE="addpullpage">
//
//</FORM></th>
//  </tr>
//  ';
//        $output .= '
//
//</table>
//';




$i=0;
        $req = "[[PullFeed:+]]";
        $url = dirname($_SERVER['HTTP_REFERER']);
        if($_GET['back']==true){
            $pullFeeds = $this->getRequestedPages($req, $url, true);
        }else{
        $pullFeeds = $this->getRequestedPages($req, $url, false);
        }
        
            $output .= '
<FORM METHOD="GET" ACTION="'.dirname($_SERVER['HTTP_REFERER']).'" name="formPull">
<table'.$tableStyle.' >
  <tr>
    <th colspan="5"'.$style.'>PULL:
  <a href='.dirname($_SERVER['HTTP_REFERER']).'?title=administration_pull_site_addition&action=addpullpage>[Add]</a>';

  if ($pullFeeds!=false) {
           $output .='<a href="javascript:processPull(document.formPull.pullremove.value);">[Remove]</a>
  <button type="submit">[Pull]</button></th>
  </tr>
  <tr>
    <th colspan="2" >Site</th>
    <th >Pages</th>
    <th>Remote <br>Patchs</th>
    <th >Local <br>Patchs</th>


  </tr>
  ';
            foreach ($pullFeeds as $pullFeed){
                $i = $i + 1;
                $data = //$this->getAwarenessData($row["site_url"]);
                $output .= '
  <tr>
    <td align="center"><input type="checkbox" id="'.$i.'" name="pull[]" value="'.$pullFeed.'" /></td>
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
        












//        $tables = array("site");
//        $columns = array("site_id", "site_url", "site_name");
//        $conditions = '';
//        $fname = "Database::select";
//        $options = array(
//            "ORDER BY" => "site_id",
//        );
//        if ($page_limit > 0) {
//            $options["LIMIT"] = $page_limit;
//        }
//        if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
//            $output .= '<p>Error accessing list.</p>';
//        } else {
//            $output .= '
//<FORM METHOD="POST" ACTION="" name="formPull">
//<table'.$tableStyle.' >
//  <tr>
//    <th colspan="5"'.$style.'>PULL:
//  <a href='.dirname($_SERVER['HTTP_REFERER']).'?title=administration_pull_site_addition&action=addpullpage>[Add]</a>
//  <a href="javascript:processPull(document.formPull.pullremove.value);">[Remove]</a>
//  <a href="javascript:processPull(document.formPull.pull.value);">[Pull]</a></th>
//  </tr>
//  <tr>
//    <th colspan="2" >Site</th>
//    <th >Pages</th>
//    <th>Remote <br>Patchs</th>
//    <th >Local <br>Patchs</th>
//
//
//  </tr>
//  ';
//            while ($row = $db->fetchRow($result)) {
//                $i = $i + 1;
//                $data = $this->getAwarenessData($row["site_url"]);
//                $output .= '
//  <tr>
//    <td align="center"><input type="checkbox" id="'.$i.'" name="pull['.$i.']" value="'.$row["site_url"].'" /></td>
//    <td title="'.$row["site_url"].'">'.$row["site_name"].'</td>
//    <td align="center">['.$data['pageCount'].']</td>
//    <td align="center">['.$data['patchCount'].']</td>
//    <td align="center">['.$data['localPatchCount'].']</td>
//  </tr>';
//            }
//            $output .= '
//
//<input type="hidden" name="checkboxcount" value="'.$i.'">
//</table>
//</FORM>';
//        }





        $i=0;
        $req = "[[PushFeed:+]]";
        $url = dirname($_SERVER['HTTP_REFERER']);
        if($_GET['back']==true){
            $pushFeeds = $this->getRequestedPages($req, $url, true);
        }else{
        $pushFeeds = $this->getRequestedPages($req, $url, false);
        }

        
            $output .= '
<FORM METHOD="GET" ACTION="'.dirname($_SERVER['HTTP_REFERER']).'" name="formPush">
<table'.$tableStyle.' >
  <tr>
    <th colspan="5"'.$style.'>PUSH:
  <a href='.dirname($_SERVER['HTTP_REFERER']).'?title=administration_push_site_addition&action=addpushpage>[Add]</a>';
            if ($pushFeeds!=false) {


 $output .= ' <a href="javascript:processPull(document.formPull.pullremove.value);">[Remove]</a>
  <button type="submit">[Push]</button></th>
  </tr>
  <tr>
    <th colspan="2" >Site</th>
    <th >Pages</th>
    <th>Remote <br>Patchs</th>
    <th >Local <br>Patchs</th>


  </tr>
  ';
            foreach ($pushFeeds as $pushFeed){
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
        

















//        $tables = array("site");
//        $columns = array("site_id", "site_url", "site_name");
//        $conditions = '';
//        $fname = "Database::select";
//        $options = array(
//            "ORDER BY" => "site_id",
//        );
//        if ($page_limit > 0) {
//            $options["LIMIT"] = $page_limit;
//        }
//        if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
//            $output .= '<p>Error accessing list.</p>';
//        } else {
//            $output .= '
//<FORM METHOD="GET" ACTION="'.dirname($_SERVER['HTTP_REFERER']).'" name="formPush">
//<table'.$tableStyle.' >
//  <tr>
//    <th colspan="5"'.$style.'>PUSH:
//  <a href='.dirname($_SERVER['HTTP_REFERER']).'?title=administration_push_site_addition&action=addpushpage>[Add]</a>
//  <a href="javascript:processPull(document.formPull.pullremove.value);">[Remove]</a>
//  <button type="submit">[Push]</button></th>
//  </tr>
//  <tr>
//    <th colspan="2" >Site</th>
//    <th >Pages</th>
//    <th>Remote <br>Patchs</th>
//    <th >Local <br>Patchs</th>
//
//
//  </tr>
//  ';
//            while ($row = $db->fetchRow($result)) {
//                $i = $i + 1;
//                $data = $this->getAwarenessData($row["site_url"]);
//                $output .= '
//  <tr>
//    <td align="center"><input type="checkbox" id="'.$i.'" name="push[]" value="'.$row["site_name"].'" /></td>
//    <td title="'.$row["site_url"].'">'.$row["site_name"].'</td>
//    <td align="center">['.$data['pageCount'].']</td>
//    <td align="center">['.$data['patchCount'].']</td>
//    <td align="center">['.$data['localPatchCount'].']</td>
//  </tr>';
//            }
//            $output .= '
//
//<input type="hidden" name="action" value="onpush">
//</table>
//</FORM>';
//        }



        $wgOut->addHTML($output);
        return false;
    }

    function onUnknownAction($action, $article) {
        global $wgOut, $wgSitename, $wgCachePages, $wgLang, $wgUser, $wgTitle, $wgDenyAccessMessage, $wgAllowAnonUsers, $wgRequest,$wgMessageCache, $wgWatchingMessages, $namespace_titles, $wgSitename;
        //require_once("WhoIsWatchingTabbed.i18n.php");

        $wgCachePages = false;
        //Verify that the action coming in is "admin"
        if($action == "admin") {

            if(isset($_POST['wiki'])&& isset ($_POST['title'])&& isset ($_POST['id'])) {

                $patchArray = $this->getPatches($_POST['id'], $_POST['title'], $_POST['wiki']);
                foreach ($patchArray as $patch){
                     $this->integratePatch($patch, $article);
                }$style = ' style="border-bottom: 2px solid #000;"';
                $tableStyle = ' style="float: left; margin-left: 40px;"';
                $output = "";

                $tables = array("site");
                $columns = array("site_id", "site_url", "site_name");
                $conditions = '';
                $fname = "Database::select";
                $options = array(
            "ORDER BY" => "site_id",
                );
                if ($page_limit > 0) {
                    $options["LIMIT"] = $page_limit;
                }
                if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
                    $output .= '<p>Error accessing list.</p>';
                } else if($db->numRows($result) == 0) {
                    $output .= '<p>No remote site.</p>';
                } else {
                    $output .= '
<FORM METHOD="POST" ACTION="">
<table'.$tableStyle.' border>
  <tr>
    <th colspan="5"'.$style.'>'.$db->numRows($result).' Remote Sites</th>
  </tr>
  <tr>
    <th colspan="2" >Site</th>

    <th><input type="submit" value="Push"></th>
    <th><input type="submit" value="Pull"></th>
    <th><input type="submit" value="Remove"></th>
    <input type="hidden" name="ppc" value="true">
  </tr>';
                    while ($row = $db->fetchRow($result)) {
                        $i = $i + 1;
                        $output .= '
  <tr>
    <td>'.$row["site_id"].'</td>
    <td title="'.$row["site_url"].'">'.$row["site_name"].'</td>
    <td colspan="3" align="center"><input type="checkbox" name="push['.$i.']"/></td>
  </tr>';
                    }
                    $output .= '


</table>
</FORM>';
                }

            }


            $page_title=$_GET['title'];


            $wgOut->setPagetitle($page_title.": Administration page");

            //adding javascript to page header
            $file = dirname($_SERVER['PHP_SELF']).'/extensions/p2pExtension/specialPage/SPFunctions.js';
            $wgOut->addScriptFile($file);

            $db = &wfGetDB(DB_SLAVE);
            $tables = array("site", "site_cnt", "page");
            $conditions = array("site.site_id = site_cnt.site_id", "site_cnt.page_title = page.page_title",
                        "page.page_title='".$_GET['title']."'");
            $fname = "Database::select";
            $columns = array("site.site_id","site_url","site_name","counter","page.page_title");
            $options = array("ORDER BY site.site_id");

            $output = "";
            if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
                $output .= '<p>Error accessing database.</p>';
            } else if($db->numRows($result) == 0) {
                $output .= '<p>This page is up to date.</p>';
            } else {
                $style = ' style="border-bottom:2px solid #000; text-align:left;"';
                $output .= '<table border cellspacing="0" cellpadding="5"><tr>';



                $output .= '<th'.$style.'>Remote site</th><th'.$style.'>Info</th><th'.$style.'>Action</th>';


                $output .= '</tr>';

                //Display the data--display some data differently than others.
                while ($row = $db->fetchRow($result)) {
                    $output .= '<tr>';

                    $output .= "<td title='yop'>";
                    $output .= htmlspecialchars($row['site_name']).'&nbsp;';
                    $output .= "</td>";
                    $output .= "<td>";
                    $output .= htmlspecialchars($row['counter']).'&nbsp;';
                    $output .= "</td>";
                    $output .= "<td>";
                    //                                        $output .= "<button type='button' onclick=\"document.location='".$_SERVER["PHP_SELF"]."?title="
                    //                                        .$row['page_title']."&action=admin&wiki=".$row['site_url']."&id=".$row['counter']."'\">PULL</button>".'&nbsp;';
                    $output .= "<button type='button' onclick=\"document.location='javascript:process(\'".$row['counter']."\', \'".$row['page_title']."\', \'".$row['site_url']."\')'\">PULL</button>".'&nbsp;';
                    $output .= "</td>";
                    $output .= '</tr>';
                }

                $output .= '</table>';
            }


            $wgOut->addHTML($output);













            //
            //            $db = &wfGetDB(DB_SLAVE);
            //
            //            $tables = array("user", "watchlist");
            //            $conditions = array("wl_user = user_id", "wl_namespace IN (".implode(", ", array_keys($namespace_titles)).")");
            //            $fname = "Database::select";
            //
            //            //Determine which results are going to be shown and the appropriate columns
            //            if(isset($_REQUEST["user_name"])) {
            //                $wgOut->setPagetitle(wfMsg("pages_watched_by_user"));
            //                $columns = array("wl_namespace","wl_title");
            //                $conditions[] = "LOWER(user_name) = " . $db->addQuotes(strtolower($_REQUEST["user_name"]));
            //            } else {
            //                $wgOut->setPagetitle(wfMsg("users_watching_page"));
            //                $columns = array("user_name", "user_real_name");
            //                $conditions[] = "LOWER(wl_title) = " . $db->addQuotes(strtolower($page_title));
            //            }
            //
            //            $order_col = "user_name";
            //            if(isset($_REQUEST["order_col"]) && in_array($_REQUEST["order_col"], $columns)) {
            //                $order_col = $_REQUEST["order_col"];
            //            }
            //
            //            //Change the way the results are ordered
            //            if(isset($_REQUEST["order_type"]) && $_REQUEST["order_type"] == "DESC") {
            //                $ordertypePOST  = "DESC";
            //                $ordertypeR = "ASC";
            //            } else {
            //                $ordertype  = "ASC";
            //                $ordertypeR = "DESC";
            //            }
            //            $options = array("ORDER BY" => "$order_col $ordertype");
            //
            //            $output = "";
            //            if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
            //                $output .= '<p>Error accessing watchlist.</p>';
            //            } else if($db->numRows($result) == 0) {
            //                $output .= '<p>Nobody is watching this page.</p>';
            //            } else {
            //                $style = ' style="border-bottom:2px solid #000; text-align:left;"';
            //                $output .= '<table cellspacing="0" cellpadding="5"><tr>';
            //
            //                //Generate sortable column headings
            //                foreach($columns as $column){
            //                    $output .= '<th'.$style.'><a href="'.$_SERVER["PHP_SELF"].'?title='.$_REQUEST["title"].'&order_col='.$column.'&action=watching&order_type='.$ordertypeR.
            //                    (isset(POST$_REQUEST["user_name"]) ? '&user_name='.$_REQUEST["user_name"] : '').
            //                    (isset($_REQUEST["user_real_name"]) ? '&user_real_name='.$_REQUEST["user_real_name"] : '').'">'.wfMsg($column).'</a></th>';
            //
            //                }
            //                $output .= '</tr>';
            //
            //                //Display the data--display some data differently than others.
            //                while ($row = $db->fetchRow($result)) {
            //                    $output .= '<tr>';
            //                    foreach($columns as $column){
            //                        $output .= "<td>";
            //                        if ($column == "user_name") {
            //                            $output .= '<a href="'.$_SERVER["PHP_SELF"].'?title='.$_REQUEST["title"].'&action=watching&user_name='.$row[$column].'">'.$row[$column];
            //                        } elseif ($column == "user_real_name") {
            //                            $output .= $row[$column];
            //                        } elseif ($column == "wl_title") {
            //                            $output .= '<a href="'.$_SERVER["PHP_SELF"].'?title='.($row["wl_namespace"]!=0 ? $namespace_titles[$row["wl_namespace"]].':' : '').$row[$column].'&action=watching">'.$row[$column].'</a>';
            //                        } elseif ($column == "wl_namespace") {
            //                            $output .= $namespace_titles[$row[$column]];
            //                        } else {
            //                            $output .= htmlspecialchars($row[$column]).'&nbsp;';
            //                        }
            //                        $output .= "</td>";
            //                    }
            //                    $output .= '</tr>';
            //                }
            //                $output .= '</table>';
            //            }
            //            $wgOut->addHTML($output);
            return false;
        } else {
            return true;
        }
    }

    function onSkinTemplateTabs(&$skin, &$content_actions) {
        global $wgRequest, $wgUser, $wgSitename;
        //        if (!$wgUser->isAllowed("whoiswatchingtabbed")) {
        //            return false;
        //        }

        $action = $wgRequest->getText("action");
        $db = &wfGetDB(DB_SLAVE);

        //        $page_title = $_REQUEST["title"];
        //        $page_namespace = 0;
        //        $pt = explode(":",$_REQUEST["title"]);
        //        if(sizeof($pt)>1) {
        //            if(strtoupper($pt[0])==strtoupper(str_replace(" ","_",$wgSitename))) {
        //                $page_namespace = constant("NS_PROJECT");
        //            } else {
        //                $page_namespace = constant("NS_".strtoupper($pt[0]));
        //            }
        //            $page_title = $pt[1];
        //        }
        //
        //        $tables = array("watchlist","user");
        //        $columns = array("COUNT(1) AS users_watching_page");
        //        $conditions = array("wl_user = user_id", "LOWER(wl_title) = ". $db->addQuotes(strtolower($page_title)),"wl_namespace=".$page_namespace);
        //        $fname = "Database::select";

        $watcherCount = 0;
        //        if (false == $result = $db->select($tables, $columns, $conditions, $fname)) {
        //            $output .= '<p>Error accessing watchlist.</p>';
        //        } else {
        //$row = $db->fetchRow($result);
        // $watcherCount = $row["users_watching_page"];
        $content_actions["admin"] = array(
                "class" => ($action == "admin") ? "selected" : false,
                "text" => "Article admin (".$watcherCount." updates)",
                "href" => $skin->mTitle->getLocalURL("action=admin")
        );
        //}
        //tab links
        return false;
    }


    function getPatches($from_id, $title, $wiki){
        //http://localhost/www/mediawiki-1.13.2/api.php?action=query&meta=patch&paid=2&palimit=2
        //$wiki='http://localhost/www/mediawiki-1.13.2/';
        //javascript "waiting..." ??
        //increment du counter, si 10 patch recuperer, counter=counter+10 puis stocker dans db
        //file_get_contents return false si erreur
        $cnt = 0;
        $success = $php = file_get_contents($wiki.'api.php?action=query&meta=patch&paoper=true&format=php&pafromid='
            .$from_id.'&papage_title='.$title);
        $array=$php = unserialize($php);
        $array = array_shift($array);
        $array = array_shift($array);
        $patchArr = array();
        foreach ($array as $patchDistant){
            $pageId = $this->getPageIdWithTitle($title);
            $patch = new Patch($patchDistant['patch_id'],
                unserialize($patchDistant['operations']),
                $patchDistant['rev_id'], $pageId);
            $patchArr[] = $patch;
            $cnt = $cnt + 1;// nb of patches
        }
        if($success!=false){
            $tmpCnt = $this->getPatchCounter($wiki, $title);//get cnt from db, increment it and save it
            $tmpCnt = $tmpCnt + $cnt;
            $this->setPatchCounter($wiki, $title, $tmpCnt);
        }
        return $patchArr;
    }

    function integratePatch($patch, $article){
        global $wgUser, $wgTitle;

        // voir l'utilisation de pc (clock)
        $pc = new persistentClock();
        $pc->load();

        if(is_string($article)){
            $db = wfGetDB( DB_SLAVE );
            $pageid = $this->getPageIdWithTitle($article);
            $lastRev = Revision::loadFromPageId($db, $pageid);
            $rev_id = $lastRev->getId();
        }
        else{
        $rev_id = $article->getRevIdFetched();
        }
        $blobInfo = BlobInfo::loadBlobInfo($rev_id);

        $listOp = $patch->getOperations();
        foreach ($listOp as $operation){
            $blobInfo->integrateBlob($operation, $pc);
        }
        $revId = $blobInfo->getNewArticleRevId();
        $blobInfo->integrate($revId, $sessionId=session_id(), $blobCB=0);
        $pc->store();
        unset($pc);


        //est-ce qu'il faut sauvegarder en local ce patch ou est-il créé dans le hook
        // 'attemptSave'. S'il est créé dans le hook, comment se passe partie "feed"
        $article->updateArticle($blobInfo->getTextImage(), $article->getComment(),
            $article->getMinorEdit(), $wgUser->isWatched($wgTitle));
//        $titleobj = Title::newFromText( wfMsgNoDB( "mainpage" ) );
//			$article = new Article( $titleobj );
    }

    function getPageIdWithTitle($title){
        $dbr = wfGetDB( DB_SLAVE );
        $id = $dbr->selectField('page','page_id', array(
        'page_title'=>$title));
        return $id;
    }

    function getPatchCounter($wiki, $title){
        $siteId = $this->getSiteIdWithURL($wiki);
        $dbr = wfGetDB( DB_SLAVE );
        $counter = $dbr->selectField(array('site_cnt'),'counter', array(
        'page_title'=>$title, 'site_id'=>$siteId));
        return $counter;
    }
    function setPatchCounter($wiki, $title, $cnt){
        $siteId = $this->getSiteIdWithURL($wiki);
        $db = wfGetDB( DB_MASTER );
        $res = $db->update('site_cnt', array('counter' => $cnt), array( "page_title"=>$title, "site_id"=>$siteId) );
    }

    function getSiteIdWithURL($url){
        $dbr = wfGetDB( DB_SLAVE );
        $id = $dbr->selectField('site','site_id', array(
        'site_url'=>$url));
        return $id;
    }

    function getPageSearchResult($wiki, $search){
        $pageTitleArray = array();
        $php = file_get_contents($wiki.'api.php?action=query&list=search&srsearch='.$search.'&srwhat=text&format=php&srlimit=100');
        //[[catégorie:pickle]]
        $array=$php = unserialize($php);
        $pageTitleArray = $array['query']['search'];
        return $pageTitleArray;
    }

    function getAwarenessData($wiki){
        $data = array();
        $pageTitleArray=array();
        $pagecount=0;
        $patchcount=0;
        $counter=0;


         $db = wfGetDB(DB_SLAVE);
        $tables = array("site", "site_cnt");
        $conditions = array("site.site_id = site_cnt.site_id", 'site_url'=>$wiki);
        $fname = "Database::select";
        $columns = array("page_title", "counter");
        $options = "";
        if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
            return false;
        } else if($db->numRows($result) == 0) {
            return false;
        } else {
            while ($row = $db->fetchRow($result)) {
                $pageTitleArray[] = $row['page_title'];
                $counter = $counter + $row['counter'];
            }
        }

        $pagecount = count($pageTitleArray);

        foreach ($pageTitleArray as $title){
            $php = file_get_contents($wiki.'api.php?action=query&meta=patch&paoper=true&format=php&pafromid=0&papage_title='.$title);
            if($php!=false){
            $array=$php = unserialize($php);
            $array = array_shift($array);
            $array = array_shift($array);
            $patchcount = $patchcount + count($array);
            }else{
                $patchcount = 0;
            }
        }
        $data['pageCount']=$pagecount;
        $data['patchCount']=$patchcount;
        $data['localPatchCount']=$counter;
        return $data;
    }

    function addSite($wiki, $name, $search){
        //siteAuthorized??
        $res = $this->siteExists($wiki, $name);
        if($res==false){// site does not exist
            $db = wfGetDB( DB_MASTER );
            $db->begin();

            $res = $db->insert( 'site', array(
            'site_url'        => $wiki,
            'site_name'    => $name,
                ), __METHOD__ );
            $id = $db->insertId();

            $pageTitleArray = array();
            $php = file_get_contents($wiki.'api.php?action=query&list=search&srsearch='.$search.'&srwhat=text&format=php&srlimit=100');
            //[[catégorie:pickle]]
            $array=$php = unserialize($php);
            $pageTitleArray = $array['query']['search'];
            foreach ($pageTitleArray as $title){
                $db->insert('site_cnt', array(
            'site_id' => $id,
            'page_title' => $title['title'],
            'counter' => 0
                    ), __METHOD__);
            }
            $db->commit();
        }
        elseif($res==true){//site exists but we add the pages anyway
            $db = wfGetDB( DB_MASTER );
            $db->begin();


            $id = $db->selectField('site','site_id', array(
        'site_url'=>$wiki,
        'site_name' => $name));

            $pageTitleArray = array();
            $php = file_get_contents($wiki.'api.php?action=query&list=search&srsearch='.$search.'&srwhat=text&format=php&srlimit=100');
            //[[catégorie:pickle]]
            $array=$php = unserialize($php);
            $pageTitleArray = $array['query']['search'];
            foreach ($pageTitleArray as $title){
                $db->insert('site_cnt', array(
            'site_id' => $id,
            'page_title' => $title['title'],
            'counter' => 0
                    ), __METHOD__);
            }
            $db->commit();
        }

    }

    function deleteSite($url){
        $id = $this->getSiteIdWithURL($url);
        $db = wfGetDB( DB_MASTER );
        $db->begin();
        $res = $db->delete( 'site', array(
            'site_url'        => $url,
            ), __METHOD__ );

        $res1 = $db->delete( 'site_cnt', array(
            'site_id'        => $id,
            ), __METHOD__ );
        $db->commit();
    }

    function siteExists($url, $name){
        $val=0;
        $db = wfGetDB(DB_SLAVE);
        $tables = array("site");
        $conditions = "";
        $fname = "Database::select";
        $columns = array("site_url","site_name");
        $options = "";
        if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
            return false;
        } else if($db->numRows($result) == 0) {
            return false;
        } else {
            while ($row = $db->fetchRow($result)) {
                if($row['site_name']==$name || $row['site_url']==$url){
                    $val = true;
                    break;
                }
                else return false;

            }
        }
        return $val;
    }

    function siteAuthorized($url){
        //verif droit site
    }

/**
 *
 * @param <type> $url
 * @return array[page_title]=counter
 */
    function getSitePageTitles($url){
        $id = $this->getSiteIdWithURL($url);
        $db = wfGetDB(DB_SLAVE);
        $tables = array("site_cnt");
        $conditions = array('site_id'=>$id);
        $fname = "Database::select";
        $columns = array("page_title", "counter");
        $options = "";
        if (false == $result = $db->select($tables, $columns, $conditions, $fname, $options)) {
            return false;
        } else if($db->numRows($result) == 0) {
            return false;
        } else {
            while ($row = $db->fetchRow($result)) {
                $pageTitleArray[$row['page_title']] = $row['counter'];
            }
        }
        return $pageTitleArray;
    }

    /**returns an array of page titles received via the request*/
function getRequestedPages($request, $url, $index=true){
    $req = str_replace(
				          array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?'),
				          array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F'), $request);
    if($index==true){
        $url1 = $url."/index.php/Special:Ask/".$req."/format=csv/sep=,/limit=100";
    }elseif($index==false){
        $url1 = $url."/Special:Ask/".$req."/format=csv/sep=,/limit=100";
    }
    $string = file_get_contents($url1);
    $res = explode("\n", $string);
    foreach ($res as $key=>$page){
        if($page==""){
            unset ($res[$key]);
        }else{
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

function getArticle( $article_title )
        {
                $title = Title::newFromText( $article_title );

                // Can't load page if title is invalid.
                if ($title == null)     return null;
                $article = new Article($title);

                return $article;
        }


static function javascript(){
$output = '
<SCRIPT language="Javascript">
function processAdd (){
		var xhr_object = null;
	   if(window.XMLHttpRequest) // Firefox
	      xhr_object = new XMLHttpRequest();
	   else if(window.ActiveXObject) // Internet Explorer
	      xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
	   else {
	      alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	      return;
	   }
	   xhr_object.open("POST", document.URL+"?site="+document.formAdd.site.value, true);
	   xhr_object.onreadystatechange = function() {
	      if(xhr_object.readyState == 4) {
//alert(xhr_object.responseText);
            document.location.reload();
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
function processPull (value){
		//alert(value);
var cnt = document.formPull.checkboxcount.value;
var tmp;
var first = "true";
for (i=1; i<=cnt; i++) {
if(document.getElementById(i).checked){
if(first=="true"){
tmp="pull["+i+"]="+document.getElementById(i).value;
first="false";
}
else{
tmp = tmp+"&pull["+i+"]="+document.getElementById(i).value;
}
}
}//end for
//alert(tmp);

       var xhr_object = null;
	   if(window.XMLHttpRequest) // Firefox
	      xhr_object = new XMLHttpRequest();
	   else if(window.ActiveXObject) // Internet Explorer
	      xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
	   else {
	      alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	      return;
	   }
	   xhr_object.open("POST", document.URL+"?value="+value, true);
	   xhr_object.onreadystatechange = function() {
	      if(xhr_object.readyState == 4) {
//alert(xhr_object.responseText);
        for (i=1; i<=cnt; i++) {
           document.getElementById(i).checked=false;
        }
        document.location.reload();
        eval(xhr_object.responseText);

		  }
	   }
	   xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	   var data = tmp;
	   xhr_object.send(data);
}
</SCRIPT>';
return $output;
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