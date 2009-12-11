<?php
if( !defined('MEDIAWIKI') ) {
// Eclipse helper - will be ignored in production
    require_once( 'ApiQueryBase.php' );
}

/**
 * Description of ApiQueryPatch
 * return the patch contain given by the parameter patchId
 *
 * @copyright INRIA-LORIA-ECOO project
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
        

        $res = utils::getSemanticQuery('[[patchID::'.$params['patchId'].']]', '?patchID
?onPage
?hasOperation
?previous');
        $count = $res->getCount();
        for($i=0; $i<$count; $i++) {

            $row = $res->getNext();
            if ($row===false) break;
            $patchId = $row[1];
            $col = $patchId->getContent();//SMWResultArray object
            foreach($col as $object) {//SMWDataValue object
                $wikiValue = $object->getWikiValue();
                $results[1] = $wikiValue;
            }
            $onPage = $row[2];
            $col = $onPage->getContent();//SMWResultArray object
            foreach($col as $object) {//SMWDataValue object
                $wikiValue = $object->getWikiValue();
                $results[2] = $wikiValue;
            }
            $hasOperation = $row[3];
            $col = $hasOperation->getContent();//SMWResultArray object
            foreach($col as $object) {//SMWDataValue object
                $wikiValue = $object->getWikiValue();
                $op[] = $wikiValue;
            }
            $results[3]=$op;
            $previous = $row[4];
            $col = $previous->getContent();//SMWResultArray object
            foreach($col as $object) {//SMWDataValue object
                $wikiValue = $object->getWikiValue();
                $results[4] = $wikiValue;
            }
        }
        $result = $this->getResult();
        //$data = str_replace('"', '', $data);

        //$data = explode('!',$data);
        $op = $results[3];
        if($results[1]) {
            $title = trim($results[2],":");
            $result->setIndexedTagName($op, 'operation');
            $result->addValue(array('query',$this->getModuleName()),'id',$results[1]);
            $result->addValue(array('query',$this->getModuleName()),'onPage',$title);
            $result->addValue(array('query',$this->getModuleName()),'previous',$results[4]);
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
