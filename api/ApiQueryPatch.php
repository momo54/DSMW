<?php

if (!defined('MEDIAWIKI')) {
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

    public function __construct($query, $moduleName) {
        parent :: __construct($query, $moduleName, 'pa');
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

        $params = $this->extractRequestParams();
        wfDebugLog('p2p', 'ApiQueryPatch params ' . $params['patchId']);

        $array = array(1 => 'id', 2 => 'onPage', 3 => 'operation', 4 => 'previous', 5 => 'siteID', 6 => 'mime', 7 => 'size', 8 => 'url', 9 => 'DateAtt', 10 => 'siteUrl', 11 => 'causal');
        $array1 = array(1 => 'patchID', 2 => 'onPage', 3 => 'hasOperation', 4 => 'previous', 5 => 'siteID', 6 => 'mime', 7 => 'size', 8 => 'url', 9 => 'DateAtt', 10 => 'siteUrl', 11 => 'causal');

        $query = '';

        for ($j = 1; $j <= count($array1); $j++) {
            $query = $query . '?' . $array1[$j] . "\n";
        }

        // getSemanticQuery returns a SMWQueryResult object
        $res = utils::getSemanticQuery('[[patchID::' . $params['patchId'] . ']]', $query);
        $results = array();

	while($row = $res->getNext()) {

            foreach ($row as $field) {
                $req = $field->getPrintRequest();
                wfDebugLog('p2p', "label field:" . $req->getLabel());
                switch (strtolower($req->getLabel())) {
                    case "hasoperation" :
		      $ops=array();
		      while ($value = $field->getNextDataValue()) {
			wfDebugLog('p2p', "op field: " . $value->getWikiValue());
			$ops[] = $value->getWikiValue();
		      }
		      $results[3] = $ops;
		      break;
                    case "previous":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[4] = $value->getShortWikiText();
                        }
                        break;
                    case "siteid":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[5] = $value->getShortWikiText();
                        }
                        break;
                    case "mime":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[6] = $value->getShortWikiText();
                        }
                        break;
                    case "size":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[7] = $value->getShortWikiText();
                        }
                        break;
                    case "url":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[8] = $value->getShortWikiText();
                        }
                        break;
                    case "dateatt":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[9] = $value->getShortWikiText();
                        }
                        break;
                    case "causal":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[11] = $value->getShortWikiText();
                        }
                        break;
                    case "patchid":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[1] = $value->getShortWikiText();
                        }
                        break;
                    case "onpage":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[2] = $value->getShortWikiText();
                        }
                        break;
                    case "siteurl":
                        $value = $field->getNextDataValue();
                        if ($value !== false) {
                            $results[10] = $value->getShortWikiText();
                        }
                        break;
                }
            }

            //     wfDebugLog('p2p',"ApiQueryPatch res ($i,$j)". serialize($row[3]->getContent()));
            // for ($j = 1; $j <= count($array); $j++) {
            //     if ($j == 3) { // collect operation of patches
            //         $col = $row[$j]->getContent(); // return an array of SMWDataItem
            //         foreach ($col as $object) {//SMWDataValue object
            // 	    	// $wikiValue = $object->getWikiValue();
            //             $wikiValue = utils::getDataValue($object);
            //             $op[] = $wikiValue;
            //         }
            //         $results[$j] = $op;
            //         wfDebugLog('p2p', "ApiQueryPatch op" . serialize($op));
            //     } else {
            //         $col = $row[$j]->getContent(); //SMWResultArray object
            //         foreach ($col as $object) {//SMWDataValue object
            // 	        // $wikiValue = $object->getWikiValue();
            //             $wikiValue = Utils::getDataValue($object);
            //             $results[$j] = $wikiValue;
            //         }
            //     }
            // }
        }

        wfDebugLog('p2p',"ApiQueryPatch results: ". serialize($results));

        $result = $this->getResult();
        //$data = str_replace('"', '', $data);
        //$data = explode('!',$data);

        if ($results[1]) {
            for ($i = 1; $i <= count($array); $i++) {
                if (isset($results[$i])) {
                    if ($i == 2) {
                        $title = trim($results[$i], ":");
                        $result->addValue(array('query', $this->getModuleName()), $array[$i], $title);
                    } elseif ($i == 3) {
                        $op = $results[$i];
                        $result->setIndexedTagName($op, $array[$i]);
                        $result->addValue('query', $this->getModuleName(), $op);
                    }
                    else
                        $result->addValue(array('query', $this->getModuleName()), $array[$i], $results[$i]);
                }
            }
        }
    }

    private function run_try() {
        global $wgServerName, $wgScriptPath;

        $params = $this->extractRequestParams();
        wfDebugLog('p2p', 'ApiQueryPatch params momo' . $params['patchId']);

        preg_match("/^(.+?)_*:_*(.*)$/S", $params['patchId'], $m);
        $nameWithoutNS = $m[2];
        wfDebugLog('p2p', 'ApiQueryPatch params pagename:' . $nameWithoutNS);
        $title = Title::newFromText($nameWithoutNS, PATCH);
        $wp = SMWDIWikiPage::newFromTitle($title);

        $result = $this->getResult();

        $sd = smwfGetStore()->getSemanticData($wp);
        wfDebugLog('p2p', 'getsemanticdata:' . serialize($sd));


        foreach ($sd->getProperties() as $prop) {
            wfDebugLog('p2p', 'prop:' . $prop->getKey());
            $result->addValue('query', $this->getModuleName(), $prop->getSerialization());
        }

        foreach ($sd->getPropertyValues(SMWDIProperty::newFromUserLabel("HasOperation")) as $op) {
            wfDebugLog('p2p', 'prop value:' . $op->getSerialization());
            $result->addValue('query', $this->getModuleName(), $op->getSerialization());
        }
    }

    public function getAllowedParams() {
        global $wgRestrictionTypes, $wgRestrictionLevels;

        return array(
            'patchId' => array(
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
