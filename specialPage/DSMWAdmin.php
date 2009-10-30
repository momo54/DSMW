<?php
/**
 * DSMW Special page
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author  jean-Philippe Muller
 */

 require_once "$IP/includes/SpecialPage.php";

/* Extension variables */
$wgExtensionFunctions[] = "wfSetupDSMWAdmin";

class DSMWAdmin extends SpecialPage{
   // Constructor
    function DSMWAdmin() {
        global $wgHooks, $wgSpecialPages, $wgWatchingMessages;
        # Add all our needed hooks
        $wgHooks["SkinTemplateTabs"][] = $this;
        SpecialPage::SpecialPage('DSMWAdmin'/*, "block"*/);// avec block => pasges speciales restreintes
        wfLoadExtensionMessages('DSMW');
    }

    function getDescription() {
        return "DSMW Settings";
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
        global $wgOut, $wgRequest, $wgServerName, $wgScriptPath, $wgDSMWIP, $wgServerName, $wgScriptPath;/*, $wgSitename, $wgCachePages, $wgUser, $wgTitle, $wgDenyAccessMessage, $wgAllowAnonUsers, $wgRequest, $wgMessageCache, $wgWatchingMessages, $wgDBtype, $namespace_titles;*/
        $urlServer = 'http://'.$wgServerName.$wgScriptPath;
//        $url = 'http://'.$wgServerName.$wgScriptPath."/index.php";
//        $urlServer = 'http://'.$wgServerName.$wgScriptPath;



            /**** Execute actions if any ****/

		$action = $wgRequest->getText( 'action' );

                        if ($action=='logootize') {

                    //test if the main_page has been logootized
                    //if there is a blobinfo for rev_id 1, the page has been logootized
                    $db = wfGetDB( DB_SLAVE );
                    $blobInfo = $db->selectField('model','blob_info', array(
            'rev_id'=>1));
                    if($blobInfo===false){

                    $wgOut->disable(); // raw output
                    ob_start();
                    print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\" dir=\"ltr\">\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><title>Setting up Storage for Semantic MediaWiki</title></head><body><p><pre>";
                    header( "Content-type: text/html; charset=UTF-8" );
                    print '</pre></p>';

                    //Getting the Main page text
                    $dbr = wfGetDB( DB_SLAVE );
                    $title = Title::newFromText( 'Main_Page' );
                    $lastRevision = Revision::loadFromTitle($dbr, $title);
                    $mainPageText = $lastRevision->getText();

                    $model = manager::loadModel(0);
                    $logoot = new logootEngine($model);

                    $listOp = $logoot->generate("", $mainPageText);
                    $modelAfterIntegrate = $logoot->getModel();
                    $tmp = serialize($listOp);
                    $patchid = sha1($tmp);
                    $patch = new Patch($patchid, $listOp, utils::getNewArticleRevId(), 1);
                    $patch->storePage($title->getText());//stores the patch in a wikipage
                    manager::storeModel(1, $sessionId=session_id(), $modelAfterIntegrate, $blobCB=0);

                    print '<p> The Main_Page has been updated!</p>';
                    $returntitle = Title::makeTitle(NS_SPECIAL, 'DSMWAdmin');
		    print '<p> <a href="' . htmlspecialchars($returntitle->getFullURL()) . '">Special:DSMWAdmin</a> </p>';
                    
                    print '</body></html>';
                    ob_flush();
                    flush();
                    return;
                    }else{
                         $wgOut->disable(); // raw output
                    ob_start();
                    print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\" dir=\"ltr\">\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><title>Setting up Storage for Semantic MediaWiki</title></head><body><p><pre>";
                    header( "Content-type: text/html; charset=UTF-8" );
                    print '</pre></p>';
                    print '<p> The Main_Page is up to date!</p>';
                    $returntitle = Title::makeTitle(NS_SPECIAL, 'DSMWAdmin');
		    print '<p> <a href="' . htmlspecialchars($returntitle->getFullURL()) . '">Special:DSMWAdmin</a> </p>';

                    print '</body></html>';
                    ob_flush();
                    flush();
                    return;
                    }

                }




        $wgOut->setPagetitle("DSMW Settings");

        $output = '<p>This page helps you during installation of Distributed Semantic MediaWiki.</p>';

                // creating tables
		$output .= '<form name="properties" action="'.$urlServer.'/extensions/DSMW/bot/DSMWBot.php" method="POST">' .
				'<input type="hidden" name="server" value="'.$urlServer.'">';
		$output .= '<br /><h2>Update properties type</h2>' .
				'<p>Distributed Semantic MediaWiki requires some properties type to be set.</p>';
		$output .= '<input type="submit" value="Update properties type"/></form>';

                 //Pass wiki article through Logoot
		$output .= '<form name="logoot" action="" method="POST">' .
				'<input type="hidden" name="action" value="logootize" />';
		$output .= '<br /><h2>DSMW update older articles</h2>' .
				'<p>For reasons of conflict management, DSMW works only with articles created after it\'s installation.
                        Therefore you need to update the "Main_Page" article in order to edit it after DSMW\'s installation.</p>';
		$output .= '<input type="submit" value="Article update"/></form>';

        $wgOut->addHTML($output);
        return false;
    }//end execute fct


}//end class

/* Global function */
# Called from $wgExtensionFunctions array when initialising extensions
function wfSetupDSMWAdmin() {
    global $wgUser;
    SpecialPage::addPage( new DSMWAdmin() );
    if ($wgUser->isAllowed("DSMWAdmin")) {
        global $wgArticleAdminPage;
        $wgDSMWAdmin = new DSMWAdmin();
    }
}
?>
