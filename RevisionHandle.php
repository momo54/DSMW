<?php

if ( !defined( 'MEDIAWIKI' ) ) {
    exit;
}
define ('INT_MAX', "1000000000000000000000");//22
define ('INT_MIN', "0");
 
//require_once('magpierss/rss_fetch.inc');

//include 'ConnectDB.php';
require_once 'logootop/LogootOp.php';
require_once 'logootop/LogootIns.php';
require_once 'logootop/LogootDel.php';
require_once 'logootEngine/BlobInfo.php';
require_once 'logootEngine/LogootId.php';
require_once 'logootEngine/LogootPosition.php';
require_once 'differenceEngine/DiffEngine.php';
require_once 'clockEngine/persistentClock.php';
//require_once 'clockEngine/Clock.php';
require_once 'patch/Patch.php';


require_once 'api/ApiQueryPatch.php';

//$wgHooks['ArticleSaveComplete'][] = 'articleSaveComplete';
//$wgHooks['ArticleSave'][] = 'articleSave';
global $wgAPIMetaModules;
$wgApiQueryMetaModules = array('patch' => 'ApiQueryPatch');

$wgHooks['EditPage::attemptSave'][] = 'attemptSave';
$wgHooks['EditPageBeforeConflictDiff'][] = 'conflict';
$wgHooks['SkinTemplateTabs'][] = 'fnMyHook';


function fnMyHook($skin,&$content_actions) {
$yop = $content_actions;

return true;
}


function conflict(&$editor, &$out) {

    $conctext = $editor->textbox1;
    $actualtext = $editor->textbox2;
    $initialtext = $editor->getBaseRevision()->mText;
    $editor->mArticle->updateArticle( $actualtext, $editor->summary, $editor->minoredit,
        $editor->watchthis, $bot=false, $sectionanchor='' );



        /*logoot*/
    /*
    $text1 = "merge entre: ".$conctext." et ".$actualtext;
    $editor->mArticle->updateArticle( $text1, $editor->summary, $editor->minoredit,
            $editor->watchthis, $bot=false, $sectionanchor='' );
*/


    //$diffs = handleDiff($initialtext, $actualtext);
    //$patch = createPatch($diffs, $editor->mTitle->mTextform, 0, 0,
    //           0, 0, 0);

    return true;
}




function articleSave(&$article, &$user, &$text, &$summary,
    $minor, $watch, $sectionanchor, &$flags)
{


    $pc = new persistentClock();
    $i=13;
    $pc->setValue($i);
    $pc->store();
    $pc->load();
    $result = $pc->getValue();


    $handle = fopen("/home/mullejea/Bureau/file.txt", "w");
    fwrite($handle, $result);
    fclose($handle);
    unset($pc);
    return true;
}



/******************************************************************************/
/*
                V0 : initial revision
               /  \
              /
          P1 /      \P2
            /
           /          \
          V1          V2:2nd edit of the same article
        1st Edit
*/
/******************************************************************************/

function attemptSave($editpage)
{
    $pc = new persistentClock();
    $pc->load();


    $firstRev = 0;
    $actualtext = $editpage->textbox1;//V2

    $dbr = wfGetDB( DB_SLAVE );
    $lastRevision = Revision::loadFromTitle($dbr, $editpage->mTitle);
    if(is_null($lastRevision)){
        $conctext = "";
        $rev_id = 0;//getNewArticleRevId();
        $firstRev = 1;
    }
    else{
        $conctext= $lastRevision->getText();//V1 conc
        $rev_id = $lastRevision->getId();
    }

    $blobInfo = BlobInfo::loadBlobInfo($rev_id);//blob ou on integre, V1

    //// essai text /////////////////////////
    $blobInfo->setTextImage($conctext);
    //modifier ce texte dans integrateBlob
    /////////////////////////////////////////


    //get the revision with the edittime==>V0
    $rev = Revision::loadFromTimestamp($dbr, $editpage->mTitle, $editpage->edittime);
    if(is_null($rev)){
        $text = "";
        $rev_id1=0;
        $firstRev = 1;
    }
    else{
        $text = $rev->getText();//VO
        $rev_id1 = $rev->getId();
    }


    if($conctext!=$text){
        //$rev_id1 = $rev->getId();
        $blobInfo1 = BlobInfo::loadBlobInfo($rev_id1);//blob de la version sur
        //laquelle generer les positions
        $listPos = $blobInfo1->handleDiff($text/*V0*/, $actualtext/*V2*/, $firstRev/*, $pc*/);




        //creation Patch P2
        $tmp = serialize($listPos);
        $patchid = sha1($tmp);
        $patch = new Patch($patchid, $listPos, $blobInfo->getNewArticleRevId());
        $patch->store();





        //integration des diffs entre VO et V2 dans V1
        foreach ($listPos as $operation){
            $blobInfo->integrateBlob($operation, $pc);
        }
    }else{//no edition conflict
        $diffs = $blobInfo->handleDiff($conctext, $actualtext, $firstRev/*, $pc*/);


        //creation patch sans qu'il y ait concurrence
        /* le rev id dans le patch est l'id de la revision à venir*/

         $tmp = serialize($diffs);
        $patchid = sha1($tmp);
        $patch = new Patch($patchid, $diffs, $blobInfo->getNewArticleRevId());
        $patch->store();

    }
    $revId = $blobInfo->getNewArticleRevId();
    $blobInfo->integrate($revId, $sessionId=session_id(), $blobCB=0);



//$handle = fopen("/home/mullejea/Bureau/file.txt", "w");
//foreach ($blobInfo->getBlobInfo() as $key=>$pos){
//    fwrite($handle, "\n ".$key." ");
//    foreach ($pos->getThisPosition() as $id){
//        fwrite($handle, " ".$id->toString()." ");
//    }
//}
    
   // fclose($handle);


   
    $pc->store();
    unset($pc);
    $editpage->textbox1 = $blobInfo->getTextImage();
    return true;
}



// CREATE TABLE `wikidb`.`patchs` (
//`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
//`patch_id` VARCHAR( 50 ) NOT NULL ,
//`operations` LONGBLOB NOT NULL ,
//`rev_id` INT( 8 ) NOT NULL ,
//PRIMARY KEY ( `id` )
//) ENGINE = InnoDB CHARACTER SET binary

//function createPatch($elements, $pageName, $siteId, $siteURL, $dateCreation,
//    $dateIntegration, $patchId)
//{
//    $patch = new Patch();
//    $patch->setElements($elements);
//    $patch->setPageName($pageName);
//    $patch->setSiteId($siteId);
//    $patch->setSiteURL($siteURL);
//    $patch->setDateCreation($dateCreation);
//    $patch->setDateIntegration($dateIntegration);
//
//    return $patch;
//}

?>
