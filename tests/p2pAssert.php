<?php
require_once 'PHPUnit/Framework/Assert.php';
require_once '../files/utils.php';

function assertPageExist($server,$pageName) {
    $rev = file_get_contents($server.'/api.php?action=query&prop=info&titles='.$pageName.'&format=php');
    $rev =  unserialize($rev);
    PHPUnit_Framework_Assert::assertFalse(count($rev['query']['pages'][-1])>0);
}

function assertContentEquals($server,$pageName,$content) {
    $contentPage = getContentPage($server,$pageName);
    PHPUnit_Framework_Assert::assertEquals($content,$contentPage);
}

/*function assertPushCreated($server,$pushName,$request) {
    assertPush($server,$pushName,$request);

    //0 changeset
    $pushFound = getSemanticRequest($server,'[[name::'.$pushName.']]','-3FhasPushHead');
//    PHPUnit_Framework_Assert::assertEquals(' ',$pushFound[0]);
}

function assertPushUpdated($server,$pushName,$request,$previousChangeSet,$op) {

    assertPush($server,$pushName,$request);

    //1 changeset
    $pushFound = getSemanticRequest($server,'[[name::'.$pushName.']]','-3FhasPushHead');
    $CSIDFound = substr($pushFound[0],0,-1);
    PHPUnit_Framework_Assert::assertNotNull($CSIDFound);
    assertPageExist($server,$CSIDFound);
    //$url1 = 'http://'.$wgServerName.$wgScriptPath."/index.php/Special:Ask/".$req."/-3FhasPatch/format=csv/sep=,/limit=100";
    //http://localhost/mediawiki-1.13.5/index.php/Special:Ask/-5B-5BchangeSetID::localhost-2Fmediawiki-1.13.5796-5D-5D-20-5B-5BhasPatch::+-5D-5D/-3FhasPatch/format=csv/sep=,/limit=100
    //http://localhost/mediawiki-1.13.5/index.php/Special:Ask/-5B-5BchangeSetID::localhost-2Fmediawiki-2D1.13.5796-5D-5D-5D-5D/-3FhasOperation/format=php/sep=,/limit=100

    //--------------- ChangeSet -------------------
    $CSName = strtolower(substr($CSIDFound, strlen('ChangeSet:')));
    $CSFound = getSemanticRequest($server,'[[changeSetID::'.$CSName.']]','-3FchangeSetID/-3FinPushFeed/-3FpreviousChangeSet');
    PHPUnit_Framework_Assert::assertEquals($CSIDFound,'ChangeSet:'.$CSFound[0]);

    PHPUnit_Framework_Assert::assertEquals(strtolower($pushName),strtolower($CSFound[1]));

    PHPUnit_Framework_Assert::assertEquals($previousChangeSet,substr($CSFound[2],0,-1));

    assertPushPatch($server,$CSName,$op);

}*/

/*function assertPushPatch($server,$changeSetId,$op) {
    $patchFound = getSemanticRequest($server,'[[changeSetID::'.$changeSetId.']]','-3FhasPatch');
    $patchFound = split(',',$patchFound[0]);

    for ($i = 0 ; $i < count($patchFound) ; $i++) {
        $posStart = strpos($patchFound[$i], ':')+1;
        $atchFound[$i] = str_replace(' ','', $patchFound[$i]);
        $patchId = substr($patchFound[$i], $posStart,strlen($patchFound[$i])-$posStart);
        $pageConcerned = getSemanticRequest($server, '[[patchID::'.$patchId.']]','-3FOnPage');
        $opFound = getSemanticRequest($server, '[[patchID::'.$patchId.']]','-3FhasOperation');
        $pageConcerned = substr($pageConcerned[0],0,-1);

        $opFound[0]  = substr($opFound[0],1,-2);
        $opFound = split(',',$opFound[0]);
        PHPUnit_Framework_Assert::assertTrue(count($op[$pageConcerned])==count($opFound));

        for ($j = 0 ; $j < count($opFound) ; $j++) {
            $opi = split(';', $opFound[$j]);
            $opCS = $opi[0];
            //PHPUnit_Framework_Assert::assertEquals(strtolower($changeSetId), strtolower($opCS));
            $opOp = strtolower($opi[1]);
            $opContent = reConvertRequest($opi[3]);
            $c = $op[$pageConcerned][$j][$opOp];
            PHPUnit_Framework_Assert::assertEquals($op[$pageConcerned][$j][$opOp],$opContent);
        }
    }
}*/

function assertPatch($server,$patchId,$clock,$pageName,$op,$previousPatch) {
    $patchName = substr($patchId,0,-strlen($clock-1));
    $patch = getSemanticRequest($server,'[[patchID::'.$patchId,'-3FonPage/-3FhasOperation/-3Fprevious');

    PHPUnit_Framework_Assert::assertEquals($pageName,$patch[0]);
    if(strtolower(substr($patch[2],0,strlen('Patch:')))=='patch:') {
        $patch[2] = substr($patch[2],strlen('patch:'));
    }
    PHPUnit_Framework_Assert::assertEquals(strtolower($previousPatch),strtolower(substr($patch[2],0,-1)));

    $opFound = split(',',$patch[1]);
    PHPUnit_Framework_Assert::assertTrue(count($op[$pageName])==count($opFound));

    for ($j = 0 ; $j < count($opFound) ; $j++) {
        $opi = split(';', $opFound[$j]);
        PHPUnit_Framework_Assert::assertEquals(strtolower($patchName.($clock)), strtolower($opi[0]));
        PHPUnit_Framework_Assert::assertEquals($op[$pageName][$j][strtolower($opi[1])],utils::decodeRequest($opi[3]));
        $clock = $clock + 1;
    }
}

/*function assertPullUpdated($server,$wikiPush,$pushFeed,$pullName) {
    $pullFound = getSemanticRequest($server,'[[name::PullFeed:'.$pullName.']][[relatedPushFeed::'.$pushFeed.']]','-3Fname/-3FrelatedPushFeed/-3FhasPullHead');

    PHPUnit_Framework_Assert::assertEquals(strtolower('PullFeed:'.$pullName),strtolower($pullFound[0]));
    PHPUnit_Framework_Assert::assertEquals(strtolower($pushName),strtolower($pullFound[1]));

    $changeSetPush = getSemanticRequest($wikiPush,'[[name::'.$pushFeed.']]','-3FhasPushHead');
    $changeSetPush = $changeSetPush[0];
    assertPageExist($server.'/'.$changeSetPush);
    PHPUnit_Framework_Assert::assertEquals($changeSetPush,$pullFound[3]);

    $CSFound = getSemanticRequest($server,'[[changeSetId::'.$changeSetPush.']]','-3FinPullFeed');
    PHPUnit_Framework_Assert::assertEquals($pullName,$CSFound[0]);

    $patchPush = getSemanticRequest($server,'[[changeSetID::'.$changeSetPush.']]','-3FhasPatch');

    for ($i = 0 ; $i < count($patchPush) ; $i++) {
        $posStart = strpos($patchPush[$i], ':')+1;
        $patchId = substr($patchPush[$i], $posStart,strlen($patchPush[$i])-1-$posStart);

        assertPageExist($server.'/'.$patchId);

        $a = getSemanticRequest($server,'[[patchID::'.$patchId.']]','-3FonPage/-3FhasOperation');
        $b = getSemanticRequest($wikiPush,'[[patchID::'.$patchId.']]','-3FonPage/-3FhasOperation');
        PHPUnit_Framework_Assert::assertEquals($a, $b);
    }
}*/

function assertCSFromPushIncluded($serverPush,$pushNane,$serverPull,$pullName) {
    $pushhead = getSemanticRequest($serverPush, '[[name::PushFeed:'.$pushName, '-3FhasPushHead');
    $pushhead = substr($pushhead[0],0,-1);
    $pullhead = getSemanticRequest($serverPull, '[[name::PullFeed:'.$pullName, '-3FhasPullHead');
    $pullhead = substr($pullhead[0],0,-1);
    PHPUnit_Framework_Assert::assertEquals($pushhead, $pullhead);
    assertPageExist($serverPull,$pullhead);
}

function getContentPage($server,$pageName) {
    $php = file_get_contents($server.'/api.php?action=query&prop=revisions&titles='.$pageName.'&rvprop=content&format=php');
    $array=$php = unserialize($php);
    $array = $array['query']['pages'];
    $array = array_shift($array);

    $content = $array['revisions'][0]["*"];
    return($content);
}

function getSemanticRequest($server,$request,$param,$sep='!') {
    $request = utils::encodeRequest($request);
    $url = $server.'/index.php/Special:Ask/'.$request.'/'.$param.'/format=csv/sep='.$sep.'/limit=100';
    $php = file_get_contents($server.'/index.php/Special:Ask/'.$request.'/'.$param.'/format=csv/sep='.$sep.'/limit=100');
    $array = split($sep, $php);
    $arrayRes[] = $array[1];
    for ($i = 2 ; $i < count($array) ; $i++) {
        $arrayRes[] = ereg_replace('"', '',$array[$i]);
    }
    return $arrayRes;
}

/*function convertRequest($request) {
    $req = str_replace(
        array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?'),
        array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F'), $request);
    return $req;
}

function reConvertRequest($request) {
    $req = str_replace(
        array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F'),
        array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?'),$request);
    return $req;
}*/

?>
