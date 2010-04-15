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
        SpecialPage::SpecialPage('DSMWAdmin'/*, "block"*/);// avec block => pages speciales restreintes
        wfLoadExtensionMessages('DSMW');
    }

    function getDescription() {
        return "DSMW Settings";
    }


    /**
     * Executed when the user opens the DSMW administration special page
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
       

            /**** Get status of refresh job, if any ****/
        $dbr =& wfGetDB( DB_SLAVE );
        $row = $dbr->selectRow( 'job', '*', array( 'job_cmd' => 'DSMWUpdateJob' ), __METHOD__ );
        if ($row !== false) { // similar to Job::pop_type, but without deleting the job
            $title = Title::makeTitleSafe( $row->job_namespace, $row->job_title);
            $updatejob = Job::factory( $row->job_cmd, $title, Job::extractBlob( $row->job_params ), $row->job_id );
        } else {
            $updatejob = NULL;
        }
        $row1 = $dbr->selectRow( 'job', '*', array( 'job_cmd' => 'DSMWPropertyTypeJob' ), __METHOD__ );
        if ($row1 !== false) { // similar to Job::pop_type, but without deleting the job
            $title = Title::makeTitleSafe( $row1->job_namespace, $row1->job_title);
            $propertiesjob = Job::factory( $row1->job_cmd, $title, Job::extractBlob( $row1->job_params ), $row1->job_id );
        } else {
            $propertiesjob = NULL;
        }
            /**** Execute actions if any ****/

        $action = $wgRequest->getText( 'action' );

        if ($action=='logootize') {


            if ($updatejob === NULL) { // careful, there might be race conditions here
                $title = Title::makeTitle(NS_SPECIAL, 'DSMWAdmin');
                $newjob = new DSMWUpdateJob($title);
                $newjob->insert();
                $wgOut->addHTML('<p><font color="red"><b>Articles update process started.</b></font></p>');
            } else {
                $wgOut->addHTML('<p><font color="red"><b>Articles update process is already running.</b></font></p>');
            }

        }

        elseif($action=='addProperties'){
            if ($propertiesjob === NULL) { 
                $title1 = Title::makeTitle(NS_SPECIAL, 'DSMWAdmin');
                $newjob1 = new DSMWPropertyTypeJob($title1);
                $newjob1->insert();
                $wgOut->addHTML('<p><font color="red"><b>Properties type update process started.</b></font></p>');
            } else {
                $wgOut->addHTML('<p><font color="red"><b>Properties type update process is already running.</b></font></p>');
            }
        }
        elseif ( $action=='updatetables' ) {

				$wgOut->disable(); // raw output
				ob_start();
				print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\" dir=\"ltr\">\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><title>Setting up Storage for Distributed Semantic MediaWiki</title></head><body><p><pre>";
				header( "Content-type: text/html; charset=UTF-8" );
                                $db =& wfGetDB( DB_MASTER );
				$result = DSMWDBHelpers::setup($db);
				print '</pre></p>';
				if ($result === true) {
					print '<p><b>The database was set up successfully.</b></p>';
				}
				$returntitle = Title::makeTitle(NS_SPECIAL, 'DSMWAdmin');
				print '<p> <a href="' . htmlspecialchars($returntitle->getFullURL()) . '">Special:DSMWAdmin</a></p>';
				print '</body></html>';
				ob_flush();
				flush();
				return;

		}


        $wgOut->setPagetitle("DSMW Settings");

        $output = '<p>This page helps you during installation of Distributed Semantic MediaWiki.</p>';

        // creating tables
        $output .= '<form name="buildtables" action="" method="POST">' .
            '<input type="hidden" name="action" value="updatetables">';
        $output .= '<br /><h2>Database: DSMW tables installation</h2>' .
            '<p>Distributed Semantic MediaWiki requires some tables to be created in the database.</p>';
        $output .= '<input type="submit" value="Initialise tables"/></form>';
        // creating properties
        $output .= '<form name="properties" action="" method="POST">' .
            '<input type="hidden" name="action" value="addProperties">';
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
        global $wgDSMWAdmin;
        $wgDSMWAdmin = new DSMWAdmin();
    }
}
?>
