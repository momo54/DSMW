<?php
if( !defined('MEDIAWIKI') ) {
// Eclipse helper - will be ignored in production
    require_once( 'ApiQueryBase.php' );
}


/**
 * Description of ApiQueryPatch
 * return the changeSet which has the previous changeSet given by
 * the parametere changeSet
 *
 * @author hantz
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
        wfDebugLog('p2p','ApiQueryChangeSet');
        $params = $this->extractRequestParams();
        $request = $this->encodeRequest('[[inPushFeed::PushFeed:'.$params['pushName'].']][[previousChangeSet::'.$params['changeSet'].']]');
        wfDebugLog('p2p','  -> request : '.$request);
        $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php/Special:Ask/'.$request.'/-3FhasPatch/format=csv/sep=!';
        wfDebugLog('p2p','  -> url request : '.$url);
        $data = file_get_contents('http://'.$wgServerName.$wgScriptPath.'/index.php/Special:Ask/'.$request.'/-3FchangeSetID/-3FhasPatch/headers=hide/format=csv/sep=!');
        $data = trim($data);
        $result = $this->getResult();
        $data = str_replace('"', '', $data);

        $data = split('!',$data);
        $CSID = $data[1];
        wfDebugLog('p2p','  -> CSID : '.$CSID);
        if($CSID) {
            $data = split(',',$data[2]);
            for ($i = 0 ; $i < count($data) ; $i++) {
                wfDebugLog('p2p','  -> patch : '.$data[$i]);
            }
            $result->setIndexedTagName($data, 'patch');
            $result->addValue(array('query',$this->getModuleName()),'id',$CSID);
            $result->addValue('query', $this->getModuleName(), $data);
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

        );
    }

    public function getParamDescription() {
        return array(
        'pushName' =>  'name of the related push feed',
        'changeSet' =>  'last changeSet (id) ',
        );
    }

    public function getDescription() {
        return 'retunr the previous changeset of the changeset parameter';
    }

    protected function getExamples() {
        return array(
        'api.php?action=query&meta=changeSet&cspushName=push&cschangeSet=localhost/wiki12&format=xml',
        );
    }

    public function getVersion() {
        return __CLASS__ . ': $Id: ApiQueryChangeSet.php xxxxx 2009-06-26 14:00:00Z hantz $';
    }
}
?>
