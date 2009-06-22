<?php
require_once 'PHPUnit/Framework/Assert.php';

function assertPageExist($server,$pageName) {
    $rev = file_get_contents($server.'/api.php?action=query&prop=info&titles='.$pageName.'&format=php');
    $rev =  unserialize($rev);
    PHPUnit_Framework_Assert::assertFalse(count($rev['query']['pages'][-1])>0);
}

function assertContentEquals($server,$pageName,$content) {
    $contentPage = getContentPage($server,$pageName);
    PHPUnit_Framework_Assert::assertEquals($content,$contentPage);
}

function assertPushCreated($server,$pushName,$request) {
    $content = getContentPage($server,$pushName);

    assertPush($content,$pushName,$request);

    //0 changeset
    $changeSetFound = getPushChangeSet($content);
    PHPUnit_Framework_Assert::assertNull($changeSetFound);
}

function assertPushUpdated($server,$pushName,$request,$previousChangeSet,$op) {
    $content = getContentPage($server,$pushName);

    assertPush($content,$pushName,$request);

    //1 changeset
    $CSIDFound = getPushChangeSet($content);
    PHPUnit_Framework_Assert::assertNotNull($CSIDFound);

    assertPageExist($server,'ChangeSet:'.$CSIDFound);
    //$url1 = 'http://'.$wgServerName.$wgScriptPath."/index.php/Special:Ask/".$req."/-3FhasPatch/format=csv/sep=,/limit=100";
    //http://localhost/mediawiki-1.13.5/index.php/Special:Ask/-5B-5BchangeSetID::localhost-2Fmediawiki-1.13.5796-5D-5D-20-5B-5BhasPatch::+-5D-5D/-3FhasPatch/format=csv/sep=,/limit=100
    //http://localhost/mediawiki-1.13.5/index.php/Special:Ask/-5B-5BchangeSetID::localhost-2Fmediawiki-2D1.13.5796-5D-5D-5D-5D/-3FhasOperation/format=php/sep=,/limit=100

    //--------------- ChangeSet -------------------
    $contentCS = getContentPage($server,'ChangeSet:'.$CSIDFound);
    $CSID = getCSID($contentCS);
    PHPUnit_Framework_Assert::assertEquals($CSIDFound,$CSID);

    $inPushFeed = getCSPushFeed($contentCS);
    PHPUnit_Framework_Assert::assertEquals($inPushFeed,$pushName);

    $previousCSFound = getPreviousCS($contentCS);
    PHPUnit_Framework_Assert::assertEquals($previousChangeSet,$previousCSFound);

    assertPatch($server,$CSID,$op);

}

function assertPatch($server,$changeSetId,$op){
    $patchFound = getSemanticRequest($server,'[[changeSetID::'.$changeSetId.']]','-3FhasPatch');

    for ($i = 0 ; $i < count($patchFound) ; $i++) {
        $posStart = strpos($patchFound[$i], ':')+1;
        $patchId = substr($patchFound[$i], $posStart,strlen($patchFound[$i])-1-$posStart);
        $opFound = getSemanticRequest($server, '[[patchID::'.$patchId.']]','-3FOnPage/-3FhasOperation');
        $pageConcerned = $opFound[0];
        
        PHPUnit_Framework_Assert::assertTrue(count($op)+1==count($opFound));

        for ($j = 1 ; $j < count($opFound) ; $j++) {
            $opi = split(';', $opFound[$j]);
            $opCS = $opi[0];
            PHPUnit_Framework_Assert::assertEquals(strtolower($changeSetId), strtolower($opCS));
            $opOp = strtolower($opi[1]);
            $opContent = substr(reConvertRequest($opi[3]),0,-1);
            PHPUnit_Framework_Assert::assertEquals($op[$pageConcerned][$opOp],$opContent);
        }
    }
}
//assertPatch('http://localhost/wiki1','localhost/wiki1873','');

function assertPush($content,$pushName,$request) {
//push name
    $pushNameFound = getPushName($content);
    PHPUnit_Framework_Assert::assertEquals($pushName,$pushNameFound);

    //push request
    $requestFound = getPushRequest($content);
    $request = convertRequest($request);
    PHPUnit_Framework_Assert::assertEquals($request,$requestFound);
}

function assertPullCreated($server,$pullName) {
    $content = getContentPage($server,'PullFeed:'.$pullName);
}
//assertPushCreated('http://localhost/wiki1','pushCity','[[Category:city]]');

function assertPullUpdated($server,$pullName,$changeSet) {
    $content = getContentPage($server,'PullFeed:'.$pullName);
}

function assertPull() {

}



function getContentPage($server,$pageName) {
    $php = file_get_contents($server.'/api.php?action=query&prop=revisions&titles='.$pageName.'&rvprop=content&format=php');
    $array=$php = unserialize($php);
    $array = $array['query']['pages'];
    $array = array_shift($array);

    $content = $array['revisions'][0]["*"];
    return($content);
}

function getPushName($content) {
    $posStart = strpos($content, '[[name::');
    if($posStart > 0) $posStart += strlen('[[name::');
    else return null;
    $posEnd = strpos($content,']]
hasSemanticQuery:') - $posStart;
    return substr($content, $posStart,$posEnd);
}

function getPushRequest($content) {
    $posStart = strpos($content, '[[hasSemanticQuery::');
    if($posStart > 0) $posStart += strlen('[[hasSemanticQuery::');
    else return null;
    $posEnd = strpos($content,']]
Pages concerned:') - $posStart;
    return substr($content, $posStart,$posEnd);
}

function getPushChangeSet($content) {
    $posStart = strpos($content,'[[hasPushHead::ChangeSet: ');
    if($posStart > 0) $posStart += strlen('[[hasPushHead::ChangeSet: ');
    else return null;
    $posEnd = strlen($content) - 2 - $posStart;
    return substr($content,$posStart,$posEnd);

}

function getCSID($content) {
    $posStart = strpos($content,'[[changeSetID::');
    if($posStart > 0) $posStart += strlen('[[changeSetID::');
    else return null;
    $posEnd = strpos($content,']]
inPushFeed:') - $posStart;
    return substr($content,$posStart,$posEnd);
}

function getCSPushFeed($content) {
    $posStart = strpos($content,'[[inPushFeed::');
    if($posStart > 0) $posStart += strlen('[[inPushFeed::');
    else return null;
    $posEnd = strpos($content,']]
previousChangetSet:') - $posStart;
    return substr($content,$posStart,$posEnd);
}

function getPreviousCS($content) {
    $posStart = strpos($content,'[[previousChangetSet::');
    if($posStart > 0) $posStart += strlen('[[previousChangetSet::');
    else return null;
    $posEnd = strpos($content,']]
 hasPatch:') - $posStart;
    return substr($content,$posStart,$posEnd);
}


function getSemanticRequest($server,$request,$param) {
    /*$posStart = strpos($content,'[[hasPatch:');
    if($posStart > 0) $posStart += strlen('[[hasPatch:');
    else return null;*/
    $request = convertRequest($request);
    $url = $server.'/index.php/Special:Ask/'.$request.'/'.$param.'/format=csv/sep=!/limit=100';
    $php = file_get_contents($server.'/index.php/Special:Ask/'.$request.'/'.$param.'/format=csv/sep=!/limit=100');
    $array = split('!', $php);
    $arrayRes[] = $array[1];
    for ($i = 2 ; $i < count($array) ; $i++) {
        $arrayRes[] = ereg_replace('"', '',$array[$i]);
    }
    return $arrayRes;
}

function getRelatedPushFeed($content) {

}

function convertRequest($request) {
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
}

?>
