<?php
/* 
 * @copyright INRIA-LORIA-SCORE Team
 * @author muller jean-philippe
 */

/**
 * Description of DSMWServerId
 *
 * @author mullejea
 */
class DSMWSiteId {
    private static $_instance = null;

    private $_SiteId;

    private function  __construct() {
        $this->_SiteId = $this->getId();
    }

    public static function getInstance(){
        if(is_null(self::$_instance)){
            self::$_instance = new DSMWSiteId();
        }

        return self::$_instance;
    }

    public function getSiteId(){
        return strtoupper($this->_SiteId);
    }
    private function getId() {
        $serverId = $this->loadServerId();
        if ($serverId===false) {
            throw new MWException( __METHOD__.': Can\'t get the SiteId from the DB!');
        }
        if($serverId=="0") {
            $serverId = md5(uniqid(mt_rand(), true));
            $this->store(strtoupper($serverId));
        }
        return strtoupper($serverId);
    }

    private function loadServerId(){
        $db = wfGetDB( DB_SLAVE );
        $res = $db->selectField('p2p_params','server_id');
        return $res;
    }

    private function store($ServerId){
        $dbw = wfGetDB( DB_MASTER );
        $dbw->update( 'p2p_params', array(
            'server_id'        => $ServerId,
            ), '*', __METHOD__ );
    }
}
?>
