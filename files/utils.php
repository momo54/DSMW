<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of utils
 *
 * @author mullejea
 */
class utils {

/**
 * generates IDs ==> SiteURL.SiteName.localclock   (ChangeSetID,patchID,OperationID)
 * Locally unique
 */
    static function generateID() {
        global $wgServerName, $wgScriptPath;
        $pc = new persistentClock();
        $pc->load();
        $pc->incrementClock();
        $id = $wgServerName.$wgScriptPath.$pc->getValue();
        $pc->store();
        unset ($pc);
        return $id;
    }

    static function encodeRequest($request) {
        $req = str_replace(
            array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}'),
            array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D'), $request);
        return $req;
    }

    static function decodeRequest($req) {
        $request = str_replace(
            array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D'),
            array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}'), $req);
        return $request;
    }

    static function pageExist($server,$pageName) {
        $rev = file_get_contents($server.'/api.php?action=query&prop=info&titles='.$pageName.'&format=php');
        $rev =  unserialize($rev);
    //PHPUnit_Framework_Assert::assertFalse(count($rev['query']['pages'][-1])>0);
    }

    static function createChangeSetPush($CSID,$inPushFeed,$previousCS,$listPatch) {
        $newtest = 'ChangeSet:
changeSetID: [[changeSetID::'.$CSID.']]
inPushFeed: [[inPushFeed::'.$inPushFeed.']]
previousChangeSet: [[previousChangeSet::'.$previousCS.']]
';
        foreach ($listPatch as $patch) {
            $newtext.=" hasPatch: [[hasPatch::".$patch."]]";
        }

        $title = Title::newFromText($CSID, CHANGESET);
        $article = new Article($title);
        $article->doEdit($newtext, $summary="");
    }

    static function createChangeSetPull($CSID,$inPullFeed,$previousCS,$listPatch) {
        $newtest = 'ChangeSet:
changeSetID: [[changeSetID::'.$CSID.']]
inPushFeed: [[inPullFeed::'.$inPullFeed.']]
previousChangeSet: [[previousChangeSet::'.$previousCS.']]
';
        foreach ($listPatch as $patch) {
            $newtext .=" hasPatch: [[hasPatch::".$patch."]]";
        }

        $title = Title::newFromText($CSID, CHANGESET);
        $article = new Article($title);
        $article->doEdit($newtext, $summary="");
}

    static function createPatch($patchId, $onPage, $previousPatch, $operations) {
        $text = 'Patch: patchID: [[patchID::'.$patchId.']]
 onPage: [[onPage::'.$onPage.']] ';
        foreach ($operations as $op) {
            $text .= 'hasOperation [[hasOperation::'.$op.']] ';
        }

        foreach($previousPatch as $prev){
            $text .= 'previous : [[previous::Patch:'.$prev.']] ';
        }
      //  $text .= 'previous : [[previous::Patch:'.$previousPatch.']]';
        $title = Title::newFromText($patchId, PATCH);
        $article = new Article($title);
        $article->doEdit($text, $summary="");
    }

    static function getLastPatchId($pageName, $url='') {
        global $wgServerName, $wgScriptPath;
        $req = '[[Patch:+]] [[onPage::'.$pageName.']]';
        $req = utils::encodeRequest($req);
        if($url=='')    $url = 'http://'.$wgServerName.$wgScriptPath;
        $url1 = $url."/index.php/Special:Ask/".$req."/-3FpatchID/headers=hide/sep=!/format=csv/limit=100";
        $string = file_get_contents($url1);//patches list
        $string = strtolower($string);
        if ($string=="") return false;
        $string = explode("\n", $string);
        foreach ($string as $key=>$str1) {
            if ($str1=="") unset ($string[$key]);
            $pos = strpos($str1, '!');
            if($pos !== false) $string[$key] = 'patch:'.substr($str1, $pos+1);
        //else $string[$key] = 'Patch:'.$str1;
        }
/*$string is the list of the patches */

        $url2 = $url."/index.php/Special:Ask/".$req."/-3Fprevious/headers=hide/sep=!/format=csv/limit=100";
        $string1 = file_get_contents($url2);//previous list
        $string1 = strtolower($string1);
        if ($string1=="") return false;
        $string1 = explode("\n", $string1);
        foreach ($string1 as $key=>$str) {
            $pos = strpos($str, '!');
            if($pos !== false) $string1[$key] = substr($str, $pos+1);
            if ($string1[$key]=="") unset ($string1[$key]);
            $pos1 = strpos($string1[$key], ';');
            if($pos1 !== false) {
                $res = explode(';', $string1[$key]);
                $string1 = array_merge($string1, $res);
            }
        }
    /*$string1 is the list of the patches witch are previouses */

        $result = array_diff($string, $string1);
        if (count($result)>1) return $result;
        else array_shift($result);
    }


}
?>
