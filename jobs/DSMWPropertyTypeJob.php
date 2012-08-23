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

        $title = Title::newFromText('opid', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::String]]';
        $editpage->attemptSave();
        }

        $title = Title::newFromText('optype', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::String]]';
        $editpage->attemptSave();
        }

	$title = Title::newFromText('logootid', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::String]]';
        $editpage->attemptSave();
        }

        $title = Title::newFromText('opcontent', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
        $editpage->textbox1 = '[[has type::Text]]';
        $editpage->attemptSave();
        }

	//record compatibility of SMW-1.6
        $title = Title::newFromText('hasOperation', SMW_NS_PROPERTY);
        if(!$title->exists()){
        $article = new Article($title);
        $editpage = new EditPage($article);
	// old version for smw<1.6
	//        $editpage->textbox1 = '[[has type::Record]]
	//[[has fields::String;String;String;Text]]';
	$editpage->textbox1='[[has type::Record]]
[[has fields::opid;optype;logootid;opcontent]]';
        $editpage->attemptSave();
        }



        wfProfileOut('DSMWPropertyTypeJob::run()');
        return true;
    }
}
?>
