<?php
 
/**
 * To the extent possible under law,  I, Mark Hershberger, have waived all copyright and
 * related or neighboring rights to Hello World. This work is published from United States.
 *
 * @copyright CC0 http://creativecommons.org/publicdomain/zero/1.0/
 * @author Mark A. Hershberger <mah@everybody.org>
 * @ingroup Maintenance
 */
 
require_once( dirname( __FILE__ ) . "/../../../maintenance/Maintenance.php" );
 
class ArticleUpdate extends Maintenance {

  public function execute() {
    global $wgServerName,$wgScriptPath, $wgServer, $wgScriptExtension;
    $urlServer = 'http://'.$wgServerName;

    $revids = array();
    $revids1 = array();
    $page_ids = array();

    //Getting all the revision ids of pages having been logootized
    $db = wfGetDB( DB_SLAVE );    
    $model_table = $db->tableName( 'model' );
    $sql ="SELECT `rev_id` FROM $model_table";
    
    $res = $db->query($sql);
    while ($row = $db->fetchObject($res)) {
      $revids[] = $row->rev_id;
    }
    $db->freeResult($res);
    
    //Getting all the revision ids without the pages in the DSMW namespaces and
    //Administration_pull_site_addition and Administration_pull_site_addition pages    
    $rev_table = $db->tableName( 'revision' );
    $page_table = $db->tableName( 'page' );
    
    $sql ="SELECT $rev_table.`rev_id` FROM $rev_table, $page_table WHERE
     `rev_page`=`page_id` and `page_namespace`!= 110 and `page_namespace`!= 200
        and `page_namespace`!= 210 and `page_namespace`!= 220 
and `page_title`!= \"Administration_pull_site_addition\"
and `page_title` != \"Administration_push_site_addition\"";
    $res1 = $db->query($sql);
    while ($row = $db->fetchObject($res1)) {
      $revids1[] = $row->rev_id;
    }
    $db->freeResult($res1);

    // debug
    /* foreach ($revids1 as $id) { */
    /*   $page_id = $db->selectField('revision','rev_page', array('rev_id'=>$id)); */
    /*   $title = Title::newFromID($page_id); */
    /*   echo 'selected '.$title->getText()."\n"; */
    /* } */
    
    //Array_diff returns an array containing all the entries from $revids1 that are
    //not present in $revids.
    $diff = array_diff($revids1, $revids);
    
    //get page ids of these revisions (each id must be unique in the array)
    foreach ($diff as $id) {
      $page_id = $db->selectField('revision','rev_page', array('rev_id'=>$id));
      $page_ids[]=$page_id;
    }
    
    $page_ids = array_unique($page_ids);
    sort($page_ids);
    //Now we can logootize:
    if(count($page_ids)!=0) {
      $processed=0;
      foreach($page_ids as $pageid) {
	$processed++;
	$title = Title::newFromID($pageid);

	echo "processing (".$processed.",".count($page_ids).") : ".$title->getText().".\n";

	$lastRev = Revision::loadFromPageId($db, $pageid);
	$pageText = $lastRev->getText();
	
	//load an empty model
	$model = manager::loadModel(0);
	$logoot = manager::getNewEngine($model,DSMWSiteId::getInstance()->getSiteId());
	
	$listOp = $logoot->generate("", $pageText);
	$modelAfterIntegrate = $logoot->getModel();
	$tmp = serialize($listOp);
	$patchid = sha1($tmp);

	// Media file management...
	$ns = $title->getNamespace();
	if ($ns == NS_FILE || $ns == NS_IMAGE || $ns == NS_MEDIA) {
	  $apiUrl = $wgServer . $wgScriptPath . "/api" . $wgScriptExtension;
	  $onPage = str_replace(array(' '), array('%20'), $lastRev->getTitle()->getText());
	  $download = $apiUrl . "?action=query&titles=File:" . $onPage . "&prop=imageinfo&format=php&iiprop=mime|url|size";
	  $resp = Http::get($download);
	  $resp = unserialize( $resp );
	  $a = $resp['query']['pages'];
	  $a = current($a);
	  $a = $a['imageinfo'];
	  $a = current($a);
	  $mime = $a['mime'];
	  $size = $a['size'];
	  $url = $a['url'];
	  $patch = new Patch(false, true, null, $urlServer. $wgScriptPath, 0, null, null, null, $mime, $size, $url, null);
	  $patch->storePage('File:'.$lastRev->getTitle()->getText(), $lastRev->getId());
	} else {
	  $patch = new Patch(false, false, $listOp, $urlServer, 0);
	  $patch->storePage($lastRev->getTitle()->getText(), $lastRev->getId());
	}

	// That's all folks
	manager::storeModel($lastRev->getId(), $sessionId = session_id(), $modelAfterIntegrate, $blobCB = 0);
      }
    }
  }
}

$maintClass = 'ArticleUpdate';
if( defined('RUN_MAINTENANCE_IF_MAIN') ) {
  require_once( RUN_MAINTENANCE_IF_MAIN );
} else {
  require_once( DO_MAINTENANCE ); # Make this work on versions before 1.17
}