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
       $request = $this->encodeRequest('[[inPushFeed::PushFeed:CityPush2]][[previousChangetSet::none]]');
        //$request = '-5B-5BinPushFeed::PushFeed:Pushcity-5D-5D-5B-5BpreviousChangetSet::localhost-2Fwiki14-5D-5D';
        $url = $wgServerName.'/index.php/Special:Ask//-3FhasPatch/format=csv/sep=!';
        $url = 'http://'.$wgServerName.$wgScriptPath.'/index.php/Special:Ask/'.$request.'/-3FhasPatch/format=csv/sep=!';
        $data = file_get_contents('http://'.$wgServerName.$wgScriptPath.'/index.php/Special:Ask/'.$request.'/-3FhasPatch/format=csv/sep=!');
        $result = $this->getResult();
        $data = str_replace('"', '', $data);

       // $data = $data[1];
        $data = split('!',$data);



//        $result->setIndexedTagName($data[0], 'cs');
 //       $result->addValue('query', $this->getModuleName(), $data[0]);

        $data = split(',',$data[1]);
        $result->setIndexedTagName($data, 'patch');
        $result->addValue('query', $this->getModuleName(), $data);
        /*$db = $this->getDB();

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

        if($operations==true) {
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
        }else {//without operations
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
        $result->addValue('query', $this->getModuleName(), $data);*/

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
