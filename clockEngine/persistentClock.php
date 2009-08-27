<?php
require_once 'Clock.php';

/**
 * Persistent clock
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe
 */
class persistentClock implements Clock{

    public $mClock;
    

    public function __construct() {
      ;

    }

    public function __destruct() {
        $this->mClock = 0;
    }

    public function getValue() {
        return $this->mClock;
    }

    public function setValue($i) {
        $this->mClock = $i;
    }

    public function incrementClock() {
        $this->mClock = $this->mClock+1;
    }

//    public function load() {
//        try {
//            $fp = fopen(dirname( __FILE__ )."/store.txt", "r");
//            $ck = fread($fp, filesize(dirname( __FILE__ )."/store.txt"));
//            fclose($fp);
//            $this->mClock = unserialize($ck);
//        } catch (Exception $e) {
//             throw new Exception ($e);
//        }
//
//    }
//
//    public function store() {
//        try {
//            $ck = serialize($this->mClock);
//            $fp = fopen(dirname( __FILE__ )."/store.txt", "w");
//            fwrite($fp, $ck);
//            fclose($fp);
//        } catch (Exception $e) {
//            throw new Exception ($e);
//        }
//
//    }

    function load(){
    $db = wfGetDB( DB_SLAVE );
        $this->mClock = $db->selectField('p2p_params','value');
}

function store(){

        $dbw = wfGetDB( DB_MASTER );
        $dbw->update( 'p2p_params', array(
            'value'        => $this->mClock,
            ), '*', __METHOD__ );

    }

}
?>
