<?php
/**
 * @copyright INRIA-LORIA-ECOO project
 * @author jean-philippe muller
 */

# create namespace
define("PATCH",110);
define("PUSHFEED",200);
define("PULLFEED",210);
define("CHANGESET",220);

$wgExtraNamespaces[PATCH] = "Patch";
$wgExtraNamespaces[PUSHFEED] = "PushFeed";
$wgExtraNamespaces[PULLFEED] = "PullFeed";
$wgExtraNamespaces[CHANGESET] = "ChangeSet";
# protect namespace
$wgNamespaceProtection[PATCH] = Array("editpatch");
$wgNamespacesWithSubpages[PATCH] = true;
$wgGroupPermissions['*']['editpatch'] = false;
$wgGroupPermissions['sysop']['editpatch'] = true;
$wgNamespaceProtection[PUSHFEED] = Array("editpushfeed");
$wgNamespacesWithSubpages[PUSHFEED] = true;
$wgGroupPermissions['*']['editpushfeed'] = false;
$wgGroupPermissions['sysop']['editpushfeed'] = true;
$wgNamespaceProtection[PULLFEED] = Array("editpullfeed");
$wgNamespacesWithSubpages[PULLFEED] = true;
$wgGroupPermissions['*']['editpullfeed'] = false;
$wgGroupPermissions['sysop']['editpullfeed'] = true;
$wgNamespaceProtection[CHANGESET] = Array("editchangeset");
$wgNamespacesWithSubpages[CHANGESET] = true;
$wgGroupPermissions['*']['editchangeset'] = false;
$wgGroupPermissions['sysop']['editchangeset'] = true;


$wgGroupPermissions['*']['ArticleAdminPage']       = true;//sysop
require_once("$IP/extensions/DSMW/specialPage/ArticleAdminPage.php" );

$wgGroupPermissions['*']['DSMWAdmin']       = true;//sysop
require_once("$IP/extensions/DSMW/specialPage/DSMWAdmin.php" );

$wgGroupPermissions['*']['DSMWGeneralExhibits']       = true;//sysop
require_once("$IP/extensions/DSMW/specialPage/DSMWGeneralExhibits.php" );

//Simple forms extension
include("$IP/extensions/DSMW/SimpleForms/SimpleForms.php");

//semantic mediawiki extension
 $smwgNamespaceIndex = 120;


//Database update
//updateDB();
//
// //define unique serverId
// global $serverId;
// $serverId = getServerId();
// if($serverId=="0"){
//     $serverId = md5(uniqid(mt_rand(), true));
//     store(strtoupper($serverId));
// }
//
// /**
//  * Gets the Server id (unique)
//  *
//  * @return <String>
//  */
// function getServerId(){
//        $db = wfGetDB( DB_SLAVE );
//        $res = $db->selectField('p2p_params','server_id');
//        return $res;
// }
//
///**
// * Stores the server id
// *
// * @param <String> $serverId
// */
// function store($serverId){
//        $dbw = wfGetDB( DB_MASTER );
//        $dbw->update( 'p2p_params', array(
//            'server_id'        => $serverId,
//            ), '*', __METHOD__ );
//    }
//
//    /**
//     *
//     * @global <type> $wgDBname
//     * @global <type> $wgDSMWIP
//     */
//    function setDBName(){
//        global $wgDBname, $wgDSMWIP;
//        $content = file_get_contents($wgDSMWIP.'/db/addedTables.sql');
//		if ( false === $content ) {
//			throw new MWException( "Could not open \"../db/addedTables.sql\".\n" );
//		}
//        $newContent = str_replace('`wikidb`', "`".$wgDBname."`", $content);
//
//        $fp = fopen( $wgDSMWIP.'/db/addedTables.sql', 'w+' );
//		if ( false === $fp ) {
//			throw new MWException( "Could not open \"../db/addedTables.sql\".\n" );
//		}
//        fwrite($fp, $newContent);
//
//	fclose( $fp );
//    }
//
//    /**
//     * Automatic database tables creation
//     * @global <String> $wgDSMWIP
//     */
//    function updateDB() {
//        global $wgDSMWIP;
//
//        $db   = &wfGetDB(DB_SLAVE);
//        if ($db->tableExists('model') === false && $db->tableExists('p2p_params') === false) {
//        //if the dsmw tables doesn't exist, there are created automatically
//            setDBName();
//            $db->sourceFile($wgDSMWIP.'/db/addedTables.sql' );
//        }
//    }
?>