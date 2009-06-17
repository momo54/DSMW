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
}
?>
