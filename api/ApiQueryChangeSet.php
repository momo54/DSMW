<?php

if (!defined('MEDIAWIKI')) {
// Eclipse helper - will be ignored in production
    require_once( 'ApiQueryBase.php' );
}

/**
 * Description of ApiQueryPatch
 * return the changeSet which has the previous changeSet given by
 * the parametere changeSet 
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author hantz
 */
class ApiQueryChangeSet extends ApiQueryBase {

    public function __construct($query, $moduleName) {
        parent :: __construct($query, $moduleName, 'cs');
    }

    public function execute() {
        $this->run();
    }

    public function encodeRequest($request) {
        $req = str_replace(
                array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?', '{', '}'), array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F', '-7B', '-7D'), $request);
        return $req;
    }

    private function run() {
        global $wgServerName, $wgScriptPath;
        wfDebugLog('p2p', '@@@@@@@@@@@@@@@@@@@@ ApiQueryChangeSet:' . $wgServerName . ',' . $wgScriptPath);
        $params = $this->extractRequestParams();

        $res = utils::getSemanticQuery('[[inPushFeed::PushFeed:' . $params['pushName'] . ']][[previousChangeSet::' . $params['changeSet'] . ']]', '?changeSetID
?hasPatch');

        $results = array();
        while($row=$res->getNext()) {
	  $results[1]=$row[1]->getNextDataValue()->getWikiValue();
	  while ($value=$row[2]->getNextDataValue()) {
	    $patches[]=$value->getWikiValue();
	  }
	  $results[2]=$patches;
        }

        $result = $this->getResult();

        if (isset($results[1]))
            $CSID = $results[1];
        else
            $CSID = null;
        wfDebugLog('p2p', '  -> CSID : ' . $CSID);
        if ($CSID) {
            $data = $results[2];
            $result->setIndexedTagName($data, 'patch');
            $result->addValue(array('query', $this->getModuleName()), 'id', $CSID);
            $result->addValue('query', $this->getModuleName(), $data);
        }
    }

    public function getAllowedParams() {
        global $wgRestrictionTypes, $wgRestrictionLevels;

        return array(
            'pushName' => array(
                ApiBase :: PARAM_TYPE => 'string',
            ),
            'changeSet' => array(
                ApiBase :: PARAM_TYPE => 'string',
            ),
        );
    }

    public function getParamDescription() {
        return array(
            'pushName' => 'name of the related push feed',
            'changeSet' => 'last changeSet (id) ',
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
