<?php
if( !defined('MEDIAWIKI') ) {
// Eclipse helper - will be ignored in production
    require_once( 'ApiQueryBase.php' );
}


/**
 * Description of ApiQueryPatch
 * Note: the "fromid" parameter is the autoincrement id in the patchs table and
 * not the patch_id
 *
 * @author mullejea
 */
class ApiQueryChangeSet extends ApiQueryBase {
    public function __construct( $query, $moduleName ) {
        parent :: __construct( $query, $moduleName, 'cs' );
    }
    public function execute() {
        $this->run();
    }

    public function encodeRequest($request) {
        $req = str_replace(
            array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}'),
            array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D'), $request);
        return $req;
    }
    private function run() {
        global $wgServerName, $wgScriptPath;

        $params = $this->extractRequestParams();
        // $request = $this->encodeRequest('[[inPushFeed::'.$params['pushName'].']][[previousChangeSet::'.$params['changeSet'].']]');
        $request = $this->encodeRequest('[[inPushFeed::PushFeed:'.$params['pushName'].']][[previousChangeSet::'.$params['changeSet'].']]');
        //$request = '-5B-5BinPushFeed::PushFeed:Pushcity-5D-5D-5B-5BpreviousChangetSet::localhost-2Fwiki14-5D-5D';
        $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php/Special:Ask/'.$request.'/-3FhasPatch/format=csv/sep=!';
        $data = file_get_contents('http://'.$wgServerName.$wgScriptPath.'/index.php/Special:Ask/'.$request.'/-3FchangeSetID/-3FhasPatch/format=csv/sep=!');
        $result = $this->getResult();
        $data = str_replace('"', '', $data);

        // $data = $data[1];
        $data = split('!',$data);
        $CSID = $data[1];

        //        $result->setIndexedTagName($data[0], 'cs');
        //        $result->addValue('query', $this->getModuleName(), $data[0]);
        if($CSID) {
            $data = split(',',$data[2]);
            $result->setIndexedTagName($data, 'patch');
            //$result->addValue((array ('query', $this->getModuleName(),$CSID)));
            $result->addValue(array('query',$this->getModuleName()),'id',$CSID);
            $result->addValue('query', $this->getModuleName(), $data);
        }else{
            $result->addValue(array('query',$this->getModuleName()),'url',$url);
        }
    }

    public function getAllowedParams() {
        global $wgRestrictionTypes, $wgRestrictionLevels;

        return array (
        'pushName' => array (
        ApiBase :: PARAM_TYPE => 'string',
        ),
        'changeSet' => array (
        ApiBase :: PARAM_TYPE => 'string',
        ),
        /*'limit' => array (
        ApiBase :: PARAM_DFLT => 10,
        ApiBase :: PARAM_TYPE => 'limit',
        ApiBase :: PARAM_MIN => 1,
        ApiBase :: PARAM_MAX => ApiBase :: LIMIT_BIG1,
        ApiBase :: PARAM_MAX2 => ApiBase :: LIMIT_BIG2
        )*/
        );
    }

    public function getParamDescription() {
        return array(
        //'limit' => 'limit how many patch (id) will be returned',
        'pushName' =>  'name of the related push feed',
        'changeSet' =>  'last changeSet (id) ',
        );
    }

    public function getDescription() {
        return 'retunr the previous changeset of the changeset parameter';
    }

    protected function getExamples() {
        return array(
        'api.php?action=query&meta=changeSet&cspushName=push&cschangeSet=localhost/wiki12',
        );
    }

    public function getVersion() {
        return __CLASS__ . ': $Id: ApiQueryChangeSet.php xxxxx 2009-06-26 14:00:00Z jpmuller $';
    }
}
?>
