<?php
/**
 * @copyright 2009 INRIA-LORIA-Score Team
 * @author jean-philippe muller
 */

/**
 * Job that assigns a type to some properties used in the DSMW ontology
 *
 */
class DSMWPropertyTypeJob extends Job {
   
   function  __construct($title) {
        parent::__construct( 'DSMWPropertyTypeJob', $title);
    }

    function run() {
        wfProfileIn('DSMWPropertyTypeJob::run()');

        $title = Title::newFromText('changeSetID', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::String]]';
        $editpage->attemptSave();
        }

        $title = Title::newFromText('hasSemanticQuery', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::String]]';
        $editpage->attemptSave();
        }

        $title = Title::newFromText('patchID', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::String]]';
        $editpage->attemptSave();
        }

        $title = Title::newFromText('siteID', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::String]]';
        $editpage->attemptSave();
        }

        $title = Title::newFromText('pushFeedServer', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::URL]]';
        $editpage->attemptSave();
        }

        $title = Title::newFromText('pushFeedName', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::String]]';
        $editpage->attemptSave();
        }

        $title = Title::newFromText('hasOperation', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::Record]]
[[has fields::String;String;Text;Text]]';
        $editpage->attemptSave();
        }
        
        wfProfileOut('DSMWPropertyTypeJob::run()');
        return true;
    }
}
?>
