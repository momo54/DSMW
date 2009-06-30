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
    static function generateID(){
        global $wgServerName, $wgScriptPath;
        $pc = new persistentClock();
        $pc->load();
        $pc->incrementClock();
        $id = $wgServerName.$wgScriptPath.$pc->getValue();
        $pc->store();
        unset ($pc);
        return $id;
    }

    static function encodeRequest($request){
        $req = str_replace(
            array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}'),
            array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D'), $request);
        return $req;
    }

    static function decodeRequest($req){
        $request = str_replace(
            array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D'),
            array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}'), $req);
        return $request;
    }
}
?>
