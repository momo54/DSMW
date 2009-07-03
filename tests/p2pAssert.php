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

function assertCSFromPushIncluded($serverPush,$pushName,$serverPull,$pullName) {
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

?>
