<?php
if( !defined('MEDIAWIKI') ) {
// Eclipse helper - will be ignored in production
    require_once( 'ApiQueryBase.php' );
}

/**
 * Description of ApiQueryPatchPush
 * return the patch contain given by the parameter patchId
 *
 * @author hantz
 */
class ApiPatchPush extends ApiQueryBase {
    public function __construct( $query, $moduleName ) {
        parent :: __construct( $query, $moduleName, 'pp' );
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
        wfDebugLog('p2p','ApiQueryPatchPushed params '.$params['pushName']);
       /* $request = $this->encodeRequest('[[patchID::'.strtolower($params['patchId']).']]');
        wfDebugLog('p2p','  -> request : '.$request);
        $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php/Special:Ask/'.$request.'/-3FpatchID/-3FonPage/-3FhasOperation/-3Fprevious/headers=hide/format=csv/sep=!';
        wfDebugLog('p2p','  -> url request : '.$url);
        $data = file_get_contents($url);
        wfDebugLog('p2p','  -> result : '.$data);
        $result = $this->getResult();
        $data = str_replace('"', '', $data);

        $data = split('!',$data);
        if($data[1]) {
            substr($data[3],0,-1);
            $op = split(',',$data[3]);
            $result->setIndexedTagName($op, 'operation');
            $result->addValue(array('query',$this->getModuleName()),'id',$data[1]);
            $result->addValue(array('query',$this->getModuleName()),'onPage',$data[2]);
            $result->addValue(array('query',$this->getModuleName()),'previous',$data[4]);
            $result->addValue('query', $this->getModuleName(), $op);
        }*/
        //published page in pushFeed
        $publishedInPush = getPublishedPatches($params['pushName']);
        $published = null;

        //filtered on published patch on page title
        foreach ($publishedInPush as $patch) {
            if(count(utils::getSemanticRequest('http://'.$wgServerName.$wgScriptPath,'[[Patch:+]][[patchID::'.$patch.']][[onPage::'.$params['pageName'].']]',''))) {
                $published[] = $patch;
            }
        }
        $result = $this->getResult();
        if(!is_null($published)) {
            $result->setIndexedTagName($published,'patch');
            $result->addValue('query', $this->getModuleName(), $published);
            $result->addValue(array('query',$this->getModuleName()),'pushFeed',$params['pushName']);
        }
    }

    public function getAllowedParams() {
        global $wgRestrictionTypes, $wgRestrictionLevels;

        return array (
        'pushName' => array (
        ApiBase :: PARAM_TYPE => 'string',
        ),
        'pageName' => array (
        ApiBase :: PARAM_TYPE => 'string',
        ),

        );
    }

    public function getParamDescription() {
        return array(
        'pushName' => 'patch published in pushName',
        'pageName' => 'patch of this pageName',
        );
    }

    public function getDescription() {
        return 'Return information of patches.';
    }

    protected function getExamples() {
        return array(
        'api.php?action=query&meta=patchPushed&pppushName=PushToto&pppageName=Titi&format=xml',
        );
    }

    public function getVersion() {
        return __CLASS__ . ': $Id: ApiQueryPatch.php xxxxx 2009-07-24 09:00:00Z hantz $';
    }
}
?>
