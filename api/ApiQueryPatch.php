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
class ApiQueryPatch extends ApiQueryBase{
    public function __construct( $query, $moduleName ) {
        parent :: __construct( $query, $moduleName, 'pa' );
    }
    public function execute() {
        $this->run();
    }

    private function run() {

        $db = $this->getDB();

        $params = $this->extractRequestParams();

        // Page filters
        $this->addTables(array('patchs', 'page', 'revision'));

        if (isset ($params['fromid'])) {// in the url: pafromid=xxx
            $this->addWhere('id>=' . intval($params['fromid']));
        }
        else if (isset ($params['id'])) {// in the url: paid=xxx
            $this->addWhere('id=' . intval($params['id']));
        }

        if (isset ($params['page_title'])) {// in the url: papage_title=xxx
           $this->addWhere('page_title=\'' .$params['page_title'].'\'');
        }

        $this->addWhere('patchs.rev_id=revision.rev_id');
        $this->addWhere('page.page_id=revision.rev_page');

        if(isset ($params['oper']))
        $operations = $params['oper'];

        if($operations==true){
            $this->addFields(array (
                'id',
                'patch_id',
                'operations',
                'patchs.rev_id',
                    'page_title',
                ));

            $limit = $params['limit'];
            $this->addOption('LIMIT', $limit+1);
            $res = $this->select(__METHOD__);

            $data = array ();
            while ($row = $db->fetchObject($res)) {

                $data[] = array(
                    'id' => intval($row->id),
                    'patch_id' => $row->patch_id,
                    'operations' => $row->operations,
                    'rev_id' => $row->rev_id,
                    'page_title' => $row->page_title);

            }
        }else{//without operations
            $this->addFields(array (
                //'id',
                'patch_id',
                    'page_title',
                //'rev_id'
                ));

            $limit = $params['limit'];
            $this->addOption('LIMIT', $limit+1);
            $res = $this->select(__METHOD__);

            $data = array ();
            while ($row = $db->fetchObject($res)) {

                $data[] = array(
                    //'id' => intval($row->id),
                    'patch_id' => $row->patch_id,
                    'page_title' => $row->page_title
                    //'rev_id' => $row->rev_id
                    );
            }
        }

        $db->freeResult($res);


        $result = $this->getResult();
        $result->setIndexedTagName($data, 'pa');
        $result->addValue('query', $this->getModuleName(), $data);

    }

    public function getAllowedParams() {
        global $wgRestrictionTypes, $wgRestrictionLevels;

        return array (
            'oper' => array (
                ApiBase :: PARAM_DFLT => false,
                ApiBase :: PARAM_TYPE => 'boolean',
            ),
            'fromid' => array (
                ApiBase :: PARAM_TYPE => 'integer',
            ),
            'id' => array (
                ApiBase :: PARAM_TYPE => 'integer',
            ),
            'page_title' => array (
                ApiBase :: PARAM_TYPE => 'string',
            ),

            'limit' => array (
                ApiBase :: PARAM_DFLT => 10,
                ApiBase :: PARAM_TYPE => 'limit',
                ApiBase :: PARAM_MIN => 1,
                ApiBase :: PARAM_MAX => ApiBase :: LIMIT_BIG1,
                ApiBase :: PARAM_MAX2 => ApiBase :: LIMIT_BIG2
            )
        );
    }

    public function getParamDescription() {
        return array(
            'limit' => 'limit how many patch (id) will be returned',
            'fromid' =>  'from which patch (id) to start enumeration',
        );
    }

    public function getDescription() {
        return 'Return id of patches.';
    }

    protected function getExamples() {
        return array(
            'api.php?action=query&meta=patch&pafromid=1&palimit=200',
            'api.php?action=query&meta=patch&pafromid=1&paoper=true',
            'api.php?action=query&meta=patch&pafromid=100',
        );
    }

    public function getVersion() {
        return __CLASS__ . ': $Id: ApiQueryPatch.php xxxxx 2009-02-26 14:00:00Z jpmuller $';
    }
}
?>
