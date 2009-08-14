<?php
if( !defined('MEDIAWIKI') ) {
// Eclipse helper - will be ignored in production
    require_once( 'ApiQueryBase.php' );
}

/**
 * Description of ApiQueryPatch
 * return the patch contain given by the parameter patchId
 *
 * @author hantz
 */
class ApiQueryPatch extends ApiQueryBase {
    public function __construct( $query, $moduleName ) {
        parent :: __construct( $query, $moduleName, 'pa' );
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
        wfDebugLog('p2p','ApiQueryPatch params '.$params['patchId']);
        $request = $this->encodeRequest('[[patchID::'.$params['patchId'].']]');
        
        wfDebugLog('p2p','  -> request : '.$request);
        $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php/Special:Ask/'.$request.'/-3FpatchID/-3FonPage/-3FhasOperation/-3Fprevious/headers=hide/format=csv/sep=!';
        wfDebugLog('p2p','  -> url request : '.$url);
        $data = file_get_contents($url);

        $result = $this->getResult();
        $data = str_replace('"', '', $data);

        $data = explode('!',$data);
        if($data[1]) {
            substr($data[3],0,-1);
            $op = explode(',',$data[3]);
            $result->setIndexedTagName($op, 'operation');
            $result->addValue(array('query',$this->getModuleName()),'id',$data[1]);
            $result->addValue(array('query',$this->getModuleName()),'onPage',$data[2]);
            $result->addValue(array('query',$this->getModuleName()),'previous',substr($data[4],0,-1));
            $result->addValue('query', $this->getModuleName(), $op);
        }
    }

    public function getAllowedParams() {
        global $wgRestrictionTypes, $wgRestrictionLevels;

        return array (
        'patchId' => array (
        ApiBase :: PARAM_TYPE => 'string',
        ),
        );
    }

    public function getParamDescription() {
        return array(
        'patchId' => 'which patch id must be returned',
        );
    }

    public function getDescription() {
        return 'Return information of patch.';
    }

    protected function getExamples() {
        return array(
        'api.php?action=query&meta=patch&papatchId=1&format=xml',
        );
    }

    public function getVersion() {
        return __CLASS__ . ': $Id: ApiQueryPatch.php xxxxx 2009-07-01 09:00:00Z hantz $';
    }
}
?>
