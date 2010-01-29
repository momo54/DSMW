<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DSMWDBHelpers
 *
 * @copyright INRIA-LORIA-SCORE Team
 * @author  jean-Philippe Muller
 */
class DSMWDBHelpers {

    public static function setup($db){
    extract( $db->tableNames('model','p2p_params') );
    DSMWDBHelpers::reportProgress("Setting up database configuration for DSMW ...\n\n");
    DSMWDBHelpers::setupTable($model, $db);
    DSMWDBHelpers::setupTable($p2p_params, $db);
    DSMWDBHelpers::reportProgress("Database initialised successfully.\n\n");
    return true;
    }

    public static function setupTable($table, $db){
        global $wgDBname, $wgDBprefix;
        $fname = 'DSMWDBHelpers::setupTable';
        DSMWDBHelpers::reportProgress("Setting up table $table ...\n");
        if ($db->tableExists($table) === false) {
            //creation
            if($table=="`{$wgDBprefix}model`"){
                $sql = 'CREATE TABLE '."`$wgDBname`.".$table.' (
`rev_id` INT( 10 ) NOT NULL ,
`session_id` VARCHAR( 50 ) NOT NULL ,
`blob_info` LONGBLOB NULL ,
`causal_barrier` BLOB NULL ,
PRIMARY KEY ( `rev_id` , `session_id` )
) ENGINE = InnoDB CHARACTER SET binary;';
                $db->query( $sql, $fname );
            }elseif($table=="`{$wgDBprefix}p2p_params`"){
                $sql = 'CREATE TABLE '."`$wgDBname`.".$table.' (
`value` BIGINT( 18 ) NOT NULL DEFAULT \'0\',
 `server_id` VARCHAR( 40 ) NOT NULL DEFAULT \'0\'
) ENGINE = InnoDB  DEFAULT CHARSET = latin1;';
$sql1 = 'INSERT INTO '."`$wgDBname`.".$table.' (`value`, `server_id`) VALUES (\'0\', \'0\');';
                $db->query( $sql, $fname );
                $db->query($sql1, $fname);
            }
            
            DSMWDBHelpers::reportProgress("   ... new table created\n");
        }else{
            DSMWDBHelpers::reportProgress("   ... table exists already\n");
        }
    }

    public static function reportProgress($msg) {
		if (ob_get_level() == 0) {
			ob_start();
		}
		print $msg;
		ob_flush();
		flush();
	}
}
?>
