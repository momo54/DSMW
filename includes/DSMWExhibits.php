<?php
/*
 * @copyright INRIA-LORIA-SCORE Team
 * @author muller jean-philippe
 */

/**
 * DSMWExhibits adds a tab on each article only if SemanticResultFormats
 * extension is loaded. This tab opens a page that shows informations about DSMW
 *
 */
class DSMWExhibits {
//Constructor
    public function DSMWExhibits() {
        global $wgHooks;

        $wgHooks['UnknownAction'][] = $this;
        $wgHooks['SkinTemplateTabs'][] = $this;
    }

    public function onUnknownAction($action, $article) {
        global $wgOut, $wgCachePages, $wgTitle, $wgDBPrefix, $wgDenyAccessMessage;

        $wgCachePages = false;

        if($action == 'exhibit') {
            $wgOut->setPageTitle(str_replace('_', ' ', $wgTitle->getDBKey()));

            if( $wgTitle->mNamespace == PULLFEED) {//if this page is a PullFeed
                $text = '==Patches pulled on the {{PAGENAME}}\'s channel==
                        
{{#ask: [[-hasPatch::<q>[[inPullFeed::PullFeed:{{PAGENAME}}]]</q>]]
|?onPage
|?modification date
| format=exhibit 
| views=timeline, table, tabular 
|sort=modification date
|facets=onPage
| limit=1000
}}';
            }elseif($wgTitle->mNamespace == PUSHFEED) {//if this page is a PushFeed
                $text = '==Patches pushed on the {{PAGENAME}}\'s channel==

{{#ask: [[-hasPatch::<q>[[inPushFeed::PushFeed:{{PAGENAME}}]]</q>]]
|?onPage
|?modification date
| format=exhibit
| views=timeline, table, tabular
|sort=modification date
|facets=onPage
| limit=1000
}}';
            }
            else {//articles in main namespace

                $text = '=={{PAGENAME}}\'s patches==

{{#ask: [[Patch:+]] [[onPage::'.$wgTitle->getDBKey().']]
|?patchID
| ?modification date
| format=exhibit
| views=timeline, table, tabular
| start=modification date
| timelineHeight=200
| limit=500
}}

==Informations about the patches of this page==

{{#ask: [[hasPatch::<q>[[Patch:+]] [[onPage::{{PAGENAME}}]]</q>]]
|?inPushFeed
|?modification date
| format=table
| sort=modification date
|default=no results
|mainlabel=ChangeSet
}}
';
            }//end else

            $wgOut->addWikiText($text);



            return false;
        }
        else {
            return true;
        }
    }

    public function onSkinTemplateTabs( $skin, $content_actions ) {
        global $wgRequest;

        $action = $wgRequest->getText( 'action' );

        if($skin->mTitle->mNamespace == PATCH
            //                || $skin->mTitle->mNamespace == PULLFEED
            //                || $skin->mTitle->mNamespace == PUSHFEED
            || $skin->mTitle->mNamespace == CHANGESET
        ) {
        }else {
            $content_actions['exhibit'] = array(
                'class' => ($action == 'exhibit') ? 'selected' : false,
                'text' => "dsmw exhibits",
                'href' => $skin->mTitle->getLocalURL( 'action=exhibit' )
            );
        }
        return true;
    }


}
?>
