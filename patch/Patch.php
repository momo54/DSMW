<?php

/**
 * Object that wraps an operation list and other features concerning an article
 * page.
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe
 */
class Patch {
    private $mId;
    private $mPatchId;
    private $mOperations = array();
    private $mRevId;
    private $mActive;
    private $mPageId;

    public function __construct($patchid, $operations, $revid/*, $active*/, $pageId) {
        $this->mPatchId = $patchid;
        $this->mOperations = $operations;
        $this->mRevId = $revid;
        $this->mPageId = $pageId;
        //$this->active = $active;
    }

    public function getPatchid() {
        return $this->mPatchId;
    }

    public function setPatchid($mPatchId) {
        $this->mPatchId = $mPatchId;
    }
    public function getActive() {
        return $this->mActive;
    }

    public function setActive($active) {
        $this->mActive = $active;
    }

    public function getOperations() {
        return $this->mOperations;
    }

    public function setOperations($operations) {
        $this->mOperations = $operations;
    }

    public function getRevid() {
        return $this->mRevId;
    }

    public function setRevid($mRevId) {
        $this->mRevId = $mRevId;
    }

    public function getPageid() {
        return $this->mPageId;
    }

    public function setPageid($PageId) {
        $this->mPageId = $PageId;
    }

    public function getId() {
        return $this->mId;
    }

    public function setId($Id) {
        $this->mId = $Id;
    }


    public function storePage($pageName){
        global $wgUser;
        $previous = utils::getLastPatchId($pageName);
        if($previous==false) {
            $previous = "none";
        }
        $ID = utils::generateID();

$serverID = DSMWSiteId::getInstance();

        $text = '
[[Special:ArticleAdminPage|DSMW Admin functions]]

==Features==
[[patchID::Patch:'.$ID.'| ]]

\'\'\'SiteID:\'\'\' [[siteID::'.$serverID->getSiteId().']]

\'\'\'Date:\'\'\' '.date(DATE_RFC822).'

\'\'\'User:\'\'\' '.$wgUser->getName().'

This is a patch of the article: [[onPage::'.$pageName.']]<br>
==Operations of the patch==

{| class="wikitable" border="1" style="text-align:left; width:80%;"
|-
!bgcolor=#c0e8f0 scope=col | Type
!bgcolor=#c0e8f0 scope=col | Content
|-
';
        $i=1;//op counter
        foreach ($this->mOperations as $operation){
            $lineContent = $operation->getLineContent();
            $lineContent1 = utils::contentEncoding($lineContent);//base64 encoding
            $type="";
            if($operation instanceof LogootIns) $type="Insert";
            else $type="Delete";
            $operationID = utils::generateID();
            $text.='|[[hasOperation::'.$operationID.';'.$type.';'
            .$operation->getLogootPosition()->toString().';'.$lineContent1.'| ]]'.$type;

            //displayed text
            $lineContent2 = $lineContent;
            $text.='
| <nowiki>'.$lineContent2.'</nowiki>
|-
';
        }
        $text.='|}';
        if (is_array($previous)){
            $text.='
==Previous patch(es)==
[[previous::';
            foreach ($previous as $prev){
                $text.=$prev.';';
            }
            $text.=']]';
        }
        else{
        $text.='
==Previous patch(es)==
[[previous::'.$previous.']]';
        }
        $title = Title::newFromText($ID, PATCH);
        $article = new Article($title);
        $article->doEdit($text, $summary="");

    }

private function splitLine($line){
    $text = "";
    $arr = str_split($line, 150);
    foreach ($arr as $element){
        $text.=$element.'<br>';
    }
    return $text;
}

}
?>
