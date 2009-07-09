<?php


require_once "$IP/includes/SpecialPage.php";

/* Extension variables */
$wgExtensionFunctions[] = "wfSetupAdminPage";

class ArticleAdminPage extends SpecialPage {
    // Constructor
    function ArticleAdminPage() {
        global $wgHooks, $wgSpecialPages, $wgWatchingMessages;
        # Add all our needed hooks
//        $wgHooks["UnknownAction"][] = $this;
//        $wgHooks["SkinTemplateTabs"][] = $this;
        SpecialPage::SpecialPage('ArticleAdminPage'/*, "block"*/);// avec block => pasges speciales restreintes
    }

        function getDescription() {
            return "Article Administration Page";
        }


    function execute() {
        global $wgOut, $wgServerName, $wgScriptPath;/*, $wgSitename, $wgCachePages, $wgUser, $wgTitle, $wgDenyAccessMessage, $wgAllowAnonUsers, $wgRequest, $wgMessageCache, $wgWatchingMessages, $wgDBtype, $namespace_titles;*/

        $url = 'http://'.$wgServerName.$wgScriptPath."/index.php";
        //$wgOut->addHeadItem('script', ArticleAdminPage::javascript());


        $script1 = '<SCRIPT language="Javascript"> function pushFeedDel(){
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
        
if (!$this->getArticle('Property:ChangeSetID')->exists()){
$output .='
<FORM METHOD="POST" ACTION="'.dirname(dirname($_SERVER['HTTP_REFERER'])).'/extensions/p2pExtension/bot/DSMWBot.php" name="scriptExec">
<table'.$tableStyle.'><td><button type="submit"><b>[UPDATE PROPERTY TYPE]</b></button>
</td></table>
<input type="hidden" name="server" value="'.dirname(dirname($_SERVER['HTTP_REFERER'])).'">
</form>';
}

        $wgOut->addHTML($output);
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
//if the browser page is refresh, feed keeps the same value
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

        //update deleted Value
        $result = str_replace("[[deleted::false| ]]", "[[deleted::true| ]]", $pageContent);
        if($result=="") return true;
        $pageContent = $result;
        
    //save update
    $article = new Article($title);
    $article->doEdit($pageContent, $summary="");

    return true;
}

//function getDistantPatchesCount($feed){
//
//}
//
//function getLocalPatchesCount($feed){
//
//}

    function getPageIdWithTitle($title){
        $dbr = wfGetDB( DB_SLAVE );
        $id = $dbr->selectField('page','page_id', array(
        'page_title'=>$title));
        return $id;
    }

    /**
     * returns an array of page titles received via the request
     */
function getRequestedPages($request){
    global $wgServerName, $wgScriptPath;
    $req = utils::encodeRequest($request);
    $url1 = 'http://'.$wgServerName.$wgScriptPath."/index.php/Special:Ask/".$req."/format=csv/sep=,/limit=100";
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


//static function javascript(){
//$output = '
//<SCRIPT language="Javascript">
//function processAdd (){
//		var xhr_object = null;
//	   if(window.XMLHttpRequest) // Firefox
//	      xhr_object = new XMLHttpRequest();
//	   else if(window.ActiveXObject) // Internet Explorer
//	      xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
//	   else {
//	      alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
//	      return;
//	   }
//	   xhr_object.open("POST", document.URL+"?site="+document.formAdd.site.value, true);
//	   xhr_object.onreadystatechange = function() {
//	      if(xhr_object.readyState == 4) {
////alert(xhr_object.responseText);
//            document.location.reload();
//	         eval(xhr_object.responseText);
//		  }
//	   }
//	   xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//	   var data = "url="+document.formAdd.url.value+"&keyword="+document.formAdd.keyword.value+"&name="+document.formAdd.name.value;
//	   xhr_object.send(data);
//       document.formAdd.url.value="";
//       document.formAdd.name.value="";
//       document.formAdd.keyword.value="";
//}
//function processPull (value){
//		//alert(value);
//var cnt = document.formPull.checkboxcount.value;
//var tmp;
//var first = "true";
//for (i=1; i<=cnt; i++) {
//if(document.getElementById(i).checked){
//if(first=="true"){
//tmp="pull["+i+"]="+document.getElementById(i).value;
//first="false";
//}
//else{
//tmp = tmp+"&pull["+i+"]="+document.getElementById(i).value;
//}
//}
//}//end for
////alert(tmp);
//
//       var xhr_object = null;
//	   if(window.XMLHttpRequest) // Firefox
//	      xhr_object = new XMLHttpRequest();
//	   else if(window.ActiveXObject) // Internet Explorer
//	      xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
//	   else {
//	      alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
//	      return;
//	   }
//	   xhr_object.open("POST", document.URL+"?value="+value, true);
//	   xhr_object.onreadystatechange = function() {
//	      if(xhr_object.readyState == 4) {
////alert(xhr_object.responseText);
//        for (i=1; i<=cnt; i++) {
//           document.getElementById(i).checked=false;
//        }
//        document.location.reload();
//        eval(xhr_object.responseText);
//
//		  }
//	   }
//	   xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
//	   var data = tmp;
//	   xhr_object.send(data);
//}
//
//function el(value){
//alert(value);
//  var boxValue = "";
//  for (var i=0; i < document.formPush.Push[].length; i++)
//     {
//     if (document.formPush.Push[i].checked)
//        {
//        boxValue = document.formPush.Push[i].value;
//        }
//     }
//alert(boxValue);
//}
//</SCRIPT>';
//return $output;
//}

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