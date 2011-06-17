<?php

/**
 * Object that wraps an operation list and other features concerning an article
 * page.
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe
 */
class Patch {

    private $mPatchId;
    private $mOperations = array();
    private $mPrevPatch;
    private $mCausal;
    private $mSiteId;
    private $mSiteUrl;
    private $mRemote;
    private $mAttachment;
    private $mMime;
    private $mSize;
    private $mUrl;
    private $mDate;
    private $mID;

    /**
     *
     * @param <bool> $remote
     * @param <bool> $attachment
     * @param <array> $operations
     * @param <string> $siteUrl
     * @param <array> $causalLink
     * @param <string> $patchid
     * @param <string> $previousPatch
     * @param <string> $siteID
     * @param <string> $Mime
     * @param <string> $Size
     * @param <string> $Url
     * @param <string> $Date
     */
    public function __construct($remote, $attachment, $operations, 
                                $siteUrl = '', $causalLink = '', $patchid = '', 
                                $previousPatch = '', $siteID = '', $Mime = '', 
                                $Size = '', $Url = '', $Date = '') {
        global $wgServer;
        $this->mRemote = $remote;
        $this->mID = utils::generateID();
        if ($remote) {
            $this->mPatchId = $patchid;
            $this->mSiteId = $siteID;
            $this->mID = $patchid;
            wfDebugLog('p2p', '- '.__METHOD__.' - '.__CLASS__."- remote Patch (Site:$siteID ; PatchID:$patchid ) ");
        } else {
            $this->mPatchId = "Patch:".$this->mID;
            $this->mSiteId = DSMWSiteId::getInstance()->getSiteId();
            wfDebugLog('p2p', '- '.__METHOD__.' - '.__CLASS__."- new Patch (Site:$this->mSiteId ; PatchID:$this->mPatchId ) ");
        }
        
        if (!isset($operations)) $operations = new LogootPatch($this->mPatchId);
        else $operations->setId($this->mPatchId);
        
        $this->mOperations = $operations;
        $this->mPrevPatch = $previousPatch;
        $this->mSiteUrl = $siteUrl;
        $this->mCausal = $causalLink;

        $this->mAttachment = $attachment;
        if ($attachment) {
            $this->mMime = $Mime;
            $this->mSize = $Size;
            if ($remote) {
                $this->mDate = $Date;
                $this->mUrl = $Url;
            }
            else {
                $this->mDate = date(DATE_RFC822);
                $this->mPatchId = "Patch:ATT".$this->mID;
                $this->mUrl = $wgServer.$Url;
                $this->mID= "Patch:ATT".$this->mID;
            }
        }
    }

    public function storePage($pageName, $rev) {
    	wfDebugLog('p2p', '- '.__METHOD__.' - '.__CLASS__."- $pageName ($rev ; $this->mPatchId ; ) ");
        global $wgUser;
        $text = "\n[[Special:ArticleAdminPage|DSMW Admin functions]]\n\n".
				"==Features==\n[[patchID::" . $this->mPatchId . "| ]]\n\n".
				"'''SiteID:''' [[siteID::" . $this->mSiteId . "]]\n\n".
				"'''SiteUrl:''' [[siteUrl::" . $this->mSiteUrl . "]]\n\n".
				"'''Rev:''' [[Rev::" . $rev . "]]\n\n";

        if ($this->mRemote) {
            $text .= "'''Remote Patch'''\n\n";
        } else {
            $this->mPrevPatch = utils::getLastPatchId($pageName);
            if ($this->mPrevPatch == false) {
                $this->mPrevPatch = "none";
            }
            $this->mCausal = utils::searchCausalLink($pageName,$this->mCausal);
        }

        $text .= "'''Date:''' " . date(DATE_RFC822) . "\n\n";
        if ($this->mAttachment) {
            $text .= "'''Date of upload of the Attachment:''' [[DateAtt::" . $this->mDate . "]]\n\n".
                     "'''Mime:''' [[Mime::" . $this->mMime . "]]\n\n".
                     "'''Size:''' [[Size::" . $this->mSize . "]]\n\n".
                     "'''Url:''' [[Url::" . $this->mUrl . "]]\n\n";
        }
        $text .= "'''User:''' " . $wgUser->getName() . 
        		 "\n\nThis is a patch of the article: [[onPage::" . $pageName . "]] <br>\n\n";
        		 
        if (! $this->mAttachment) {
            $text .= "==Operations of the patch==\n\n{| "
                    ."class='wikitable' border='1' style='text-align:left; width:80%;'"
                    ."\n|-\n"
                    ."!bgcolor=#c0e8f0 scope=col | Type\n"
                    ."!bgcolor=#c0e8f0 scope=col | Content\n"
                    ."|-\n";

           	$liste_op = "";
               $i = 1; //op counter
               foreach ($this->mOperations as $operation) {
                   $lineContent = $operation->getLineContent();
                   $lineContent1 = utils::contentEncoding($lineContent); //base64 encoding
                   $type = "";
                   if ($operation->type() == LogootOperation::INSERT)// instanceof LogootIns)
                       $type = "Insert";
                   else
                       $type="Delete";
                   $operationID = utils::generateID();
                   $liste_op .='|[[hasOperation::' . $operationID . ';' . $type . ';'
                               . $operation->getLogootPosition()->toString() . ';' . $lineContent1 
                               . '| ]]' . $type;

                   //displayed text
                   $lineContent2 = $lineContent;
                   $liste_op .= "\n|<nowiki>" . $lineContent2 ." : "
                                .$operation->getLogootPosition()->toString()."</nowiki>\n|-\n";
               }
               $text .= $liste_op.'|}';
        }
        if (is_array($this->mPrevPatch)) {
            $text.="\n\n==Previous patch(es)==\n[[previous::";
            foreach ($this->mPrevPatch as $prev) {
                $text.=$prev . ';';
            }
            $text.=']]';
        } else {
            $text.="\n\n==Previous patch(es)==\n[[previous::" . $this->mPrevPatch . ']]';
        }
        $text.="\n\n==Causal Link==\n[[causal::" . $this->mCausal . ']]';

        $title = Title::newFromText($this->mID, PATCH);
        $article = new Article($title);
        $article->doEdit($text, $summary = "");
        
        wfDebugLog('p2p', '- '.__METHOD__.' - '.__CLASS__."- ############# Début ###################");
        wfDebugLog('p2p', $text);
        wfDebugLog('p2p', '- '.__METHOD__.' - '.__CLASS__."- #############  Fin  ###################");
    }
    

    private function splitLine($line) {
        $text = "";
        $arr = str_split($line, 150);
        foreach ($arr as $element) {
            $text.=$element . '<br>';
        }
        return $text;
    }

}
?>
