<?php
/**
 * DSMW Special page
 *
 * @copyright INRIA-LORIA-SCORE Team
 * @author  jean-Philippe Muller
 */

require_once "$IP/includes/SpecialPage.php";

/* Extension variables */
$wgExtensionFunctions[] = "wfSetupDSMWGenExhibits";

class DSMWGeneralExhibits extends SpecialPage {
//Constructor
    function DSMWGeneralExhibits() {
        if(defined('SRF_VERSION')){
        SpecialPage::SpecialPage('DSMWGeneralExhibits');
        }
        wfLoadExtensionMessages('DSMW');
    }

    function getDescription() {
        return "DSMW general exhibits";
    }

    /**
     * Executed when the user opens the "DSMW general exhibits" special page
     * Displays information about DSMW, e.g. all the DSMW PushFeeds in a timeline
     * (This special page works only when the Semantic Results Format extension is installed)
     *
     * There are 3 links used to see informations about Patches, PullFeeds or PushFeeds
     *
     * 
     */
    function execute() {
        global $wgOut, $wgRequest, $wgServerName, $wgScriptPath, $wgDSMWIP, $wgServerName, $wgScriptPath;

        //If the Semantic Results Format isn't installed, a blank warning page appears
//        if (!defined('SRF_VERSION')) {
//            $wgOut->disable(); // raw output
//            ob_start();
//            print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\" dir=\"ltr\">\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><title>DSMW General Exhibits</title></head><body><p><pre>";
//            header( "Content-type: text/html; charset=UTF-8" );
//
//            print '</pre></p>';
//            print '<p><b>In order to use this feature, you must have installed the Semantic Results Format extension.</b></p>';
//            $SRFUrl = 'http://www.mediawiki.org/wiki/Extension:Semantic_Result_Formats';
//            print '<p> <a href="' . htmlspecialchars($SRFUrl) . '">Extension:Semantic_Result_Formats</a></p>';
//            $returntitle = Title::makeTitle(NS_SPECIAL, 'SpecialPages');
//            print '<p> <a href="' . htmlspecialchars($returntitle->getFullURL()) . '"> Back to Special:SpecialPages</a></p>';
//            print '</body></html>';
//            ob_flush();
//            flush();
//            return;
//        }


        $wgOut->setPagetitle("DSMW General Exhibits");

        $output = '<p>This page displays general informations about Distributed Semantic MediaWiki.</p>';

        $returntitle1 = Title::makeTitle(NS_SPECIAL, 'DSMWGeneralExhibits');
        $output .= '<b><a href="' . htmlspecialchars($returntitle1->getFullURL()) . '?action=pushdisplay">[PushFeed data] </a></b>';

        $returntitle1 = Title::makeTitle(NS_SPECIAL, 'DSMWGeneralExhibits');
        $output .= '<b><a href="' . htmlspecialchars($returntitle1->getFullURL()) . '?action=pulldisplay">[PullFeed data] </a></b>';

        $returntitle1 = Title::makeTitle(NS_SPECIAL, 'DSMWGeneralExhibits');
        $output .= '<b><a href="' . htmlspecialchars($returntitle1->getFullURL()) . '?action=patchdisplay">[Patches data] </a></b>';


        $action = $wgRequest->getText( 'action' );

        switch ($action) {
            case "pushdisplay":
                $wikitext = '
==PushFeeds==
{{#ask: [[PushFeed:+]]
|?name
|?modification date
|?pushFeedServer
|?pushFeedName
|?hasPushHead
| format=exhibit
| views=timeline, table, tabular
| sort=modification date
| timelineHeight=400
|facets=modification date
|limit=500
}}
';
                break;
            case "pulldisplay":
                $wikitext = '
==PullFeeds==
{{#ask: [[PullFeed:+]]
|?name
|?modification date
|?pushFeedName
|?pushFeedServer
| format=exhibit
| views=timeline, table, tabular
| sort=modification date
| timelineHeight=400
|facets=modification date
|limit=500
}}
';
                break;
            case "patchdisplay":
                $wikitext = '
==Patches==
{{#ask: [[Patch:+]]
|?patchID
|?modification date
|?onPage
|?previous
| format=exhibit
| views=timeline, table, tabular
| sort=modification date
| timelineHeight=400
|facets=onPage, modification date
|limit=500
}}
';
                break;

            default:
                $wikitext = '
==Patches==
{{#ask: [[Patch:+]]
|?patchID
|?modification date
|?onPage
|?previous
| format=exhibit
| views=timeline, table, tabular
| sort=modification date
| timelineHeight=400
|facets=onPage, modification date
|limit=500
}}
';
                break;
        }

        $wgOut->addHTML($output);
        $wgOut->addWikiText($wikitext);

        return false;
}//end execute fct


}

/* Global function */
# Called from $wgExtensionFunctions array when initialising extensions
function wfSetupDSMWGenExhibits() {
    global $wgUser;
    SpecialPage::addPage( new DSMWGeneralExhibits() );
    if ($wgUser->isAllowed("DSMWGeneralExhibits")) {
        global $wgDSMWGenExhibits;
        $wgDSMWGenExhibits = new DSMWGeneralExhibits();
    }
}
?>
