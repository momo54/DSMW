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

class DSMWAdmin extends SpecialPage {
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

            /**** Get status of refresh job, if any ****/
        $dbr =& wfGetDB( DB_SLAVE );
        $row = $dbr->selectRow( 'job', '*', array( 'job_cmd' => 'DSMWUpdateJob' ), __METHOD__ );
        if ($row !== false) { // similar to Job::pop_type, but without deleting the job
            $title = Title::makeTitleSafe( $row->job_namespace, $row->job_title);
            $updatejob = Job::factory( $row->job_cmd, $title, Job::extractBlob( $row->job_params ), $row->job_id );
        } else {
            $updatejob = NULL;
        }

            /**** Execute actions if any ****/

        $action = $wgRequest->getText( 'action' );

        if ($action=='logootize') {


            if ($updatejob === NULL) { // careful, there might be race conditions here
                $title = Title::makeTitle(NS_SPECIAL, 'DSMWAdmin');
                $newjob = new DSMWUpdateJob($title);
                $newjob->insert();
                $wgOut->addHTML('<p>Articles update process started.</p>');
            } else {
                $wgOut->addHTML('<p>Articles update process is already running.</p>');
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
                        Therefore you need to update articles created before it\'s installation in order to edit them.</p>';
        $output .= '<input type="submit" value="Articles update"/></form>';

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
