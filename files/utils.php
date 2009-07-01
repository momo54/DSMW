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
previousChangetSet: [[previousChangeSet::'.$previousCS.']]
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
previousChangetSet: [[previousChangeSet::'.$previousCS.']]
';
        foreach ($listPatch as $patch) {
            $newtext .=" hasPatch: [[hasPatch::".$patch."]]";
        }

        $title = Title::newFromText($CSID, CHANGESET);
        $article = new Article($title);
        $article->doEdit($newtext, $summary="");
    }

    
}
?>
