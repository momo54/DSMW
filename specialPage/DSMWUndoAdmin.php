<?php

/**
 * DSMW Special page
 */
////////////////////BEN////////////////////
require_once "$IP/includes/SpecialPage.php";
require_once "$wgDSMWIP/includes/SemanticFunctions.php";
require_once "$wgDSMWIP/files/utils.php";

/* Extension variables */
$wgExtensionFunctions[] = "wfSetupDSMWUndoAdmin";

class DSMWUndoAdmin extends SpecialPage {
// Constructor
    function DSMWUndoAdmin() {
        global $wgHooks, $wgSpecialPages, $wgWatchingMessages;
        # Add all our needed hooks
        $wgHooks["UnknownAction"][] = $this;
        $wgHooks["SkinTemplateTabs"][] = $this;
        SpecialPage::SpecialPage('DSMWUndoAdmin'/*, "block"*/);// with "block", the special page has an access constraints
        wfLoadExtensionMessages('DSMW');
    }
    
    function getDescription() {
        return "DSMW Undo Admin function";
    }

    /**
     * Executed the user opens the special page
     * Displays a research form to allow first search of the patchs he wants to remove
     *
     * @global <Object> $wgOut Output page instance
     * @global <String> $wgServerName
     * @global <String> $wgScriptPath
     * @return <bool>
     */
    function execute() {
        global $wgOut, $wgRequest, $wgServerName, $wgScriptPath, $wgDSMWIP, $wgServer, $wgScriptPath, $wgScriptExtension;
        $urlServer = 'http://'.$wgServerName.$wgScriptPath;
        $output = '';//initialisation of $output as an empty string

        $output .= '<p><b>Result:</b></p>';//we add the xhtml code wich should be displayed into $output
        $wgOut->setPagetitle("Admin Undo Page");
        $url = $urlServer.'/index.php';
        $title = 'Special:DSMWUndoAdmin';

        $wgOut->addHTML('
            <form  name="formViewUndo">
            <table style="border-bottom: 2px solid #000;">
                <tr>
                    <td><b>Page: </b></td>
                    <td><input type="text" id="reqPage"/></td>
                </tr>
                <tr>
                    <td><b>Date: </b></td>
                    <td><input type="text" id="reqDate"/></td>
                    <td>use the synthax: 2 February 2002</td>
                </tr>
                <tr>
                    <td> <input type="button" value="Search" onClick="viewtoundopatchs(\''.$url.'\',\''.$title.'\');"></input></td>
                </tr>
            </table>
            </form>
            <div id="viewpatchs" style="display: none; width: 100%; clear: both;" >
            <a name="viewOfPatches:" id="viewOfPatches:"></a><h2> <span class="mw-headline"> Patchs: </span></h2>
            <div id="viewToUndoPatches" ></div><br />
            </div>
            ');
   
    return false;
    }

public function onUnknownAction($action, $article) {
    global $wgOut, $wgCachePages, $wgTitle, $wgDBPrefix, $wgDenyAccessMessage;

    $wgCachePages = false;

    if($action == 'undo') {
        $wgOut->setPageTitle(str_replace('_', ' ', $wgTitle->getDBKey()));

        
        echo('<p>WE ARE EAR!!!</p>');

        //des trucs...
        
        utils::writeAndFlush('Yeah');
        $title = Title::newFromText($_POST['page']);
        $article = new Article($title);
        $article->doRedirect();
        return false;
        }
    else
        {
        return true;
        }
    }

}//End Class UndoAdmin

/* Global function */
# Called from $wgExtensionFunctions array when initialising extensions
function wfSetupDSMWUndoAdmin() {
    global $wgUser;
    SpecialPage::addPage( new DSMWUndoAdmin );
    if ($wgUser->isAllowed("DSMWUndoAdmin")) {
        global $wgDSMWUndoAdmin;
        $wgDSMWUndoAdmin = new DSMWUndoAdmin();
    }
}

////////////////////BEN////////////////////

?>
