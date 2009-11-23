<?php
/**
 * @copyright 2009 INRIA-LORIA-Score Team
 * @author jean-philippe muller
 */

/**
 * DSMWUpdateJob iterates over all the pages existing before DSMW installation
 * and pass them through the logoot algorithm to add them to the DSMW page model
 *
 * @author mullejea
 */
class DSMWUpdateJob extends Job {

    function  __construct($title) {
        parent::__construct( 'DSMWUpdateJob', $title);
    }

    function run() {
        wfProfileIn('DSMWUpdateJob::run()');
        $revids = array();
        $revids1 = array();
        $page_ids = array();
//Getting all the revision ids of pages having been logootized
        $db = wfGetDB( DB_SLAVE );

        $sql ="SELECT `rev_id` FROM `model`";

        $res = $db->query($sql);
        while ($row = $db->fetchObject($res)) {
            $revids[] = $row->rev_id;
        }
        $db->freeResult($res);

        //if (count($revids)==0) $revids=array();


//Getting all the revision ids without the pages in the DSMW namespaces and
//Administration_pull_site_addition and Administration_pull_site_addition pages

        $sql ="SELECT `revision`.`rev_id` FROM `revision`, `page` WHERE
     `rev_page`=`page_id` and `page_namespace`!= 110 and `page_namespace`!= 200
        and `page_namespace`!= 210 and `page_namespace`!= 220
and `page_title`!= \"Administration_pull_site_addition\"
and `page_title` != \"Administration_push_site_addition\"";
        $res1 = $db->query($sql);
        while ($row = $db->fetchObject($res1)) {
            $revids1[] = $row->rev_id;
        }
        $db->freeResult($res1);

        //if (count($revids1)==0) $revids1=array();

//Array_diff returns an array containing all the entries from $revids1 that are
//not present in $revids.
        $diff = array_diff($revids1, $revids);

//get page ids of these revisions (each id must be unique in the array)
        foreach ($diff as $id) {
            $page_id = $db->selectField('revision','rev_page', array('rev_id'=>$id));
            $page_ids[]=$page_id;
        }

        $page_ids = array_unique($page_ids);

//Now we can logootize:
        if(count($page_ids)!=0) {
            foreach($page_ids as $pageid) {

                $lastRev = Revision::loadFromPageId($db, $pageid);
                $pageText = $lastRev->getText();

                //load an empty model
                $model = manager::loadModel(0);
                $logoot = new logootEngine($model);

                $listOp = $logoot->generate("", $pageText);
                $modelAfterIntegrate = $logoot->getModel();
                $tmp = serialize($listOp);
                $patchid = sha1($tmp);
                $patch = new Patch($patchid, $listOp, $lastRev->getId(), $pageid);
                $patch->storePage($lastRev->getTitle()->getText());
                manager::storeModel($lastRev->getId(), $sessionId=session_id(), $modelAfterIntegrate, $blobCB=0);
            }
        }


        wfProfileOut('DSMWUpdateJob::run()');
        return true;
    }
}
?>
