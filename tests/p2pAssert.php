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
    $content = getContentPage($server,'PushFeed:'.$pushName);

    assertPush($content,$pushName,$request);

    //0 changeset
    $changeSetFound = getPushChangeSet($content);
    PHPUnit_Framework_Assert::assertNull($changeSetFound);
}

function assertPushUpdated($server,$pushName,$request,$previousChangeSet) {
    $content = getContentPage($server,'PushFeed:'.$pushName);

    assertPush($content,$pushName,$request);

    //1 changeset
    $CSIDFound = getPushChangeSet($content);
    PHPUnit_Framework_Assert::assertNotNull($changeSetFound);

    assertPageExist('ChangeSet'.$changeSetFound);
    //$url1 = 'http://'.$wgServerName.$wgScriptPath."/index.php/Special:Ask/".$req."/-3FhasPatch/format=csv/sep=,/limit=100";
//http://localhost/mediawiki-1.13.5/index.php/Special:Ask/-5B-5BchangeSetID::localhost-2Fmediawiki-1.13.5796-5D-5D-20-5B-5BhasPatch::+-5D-5D/-3FhasPatch/format=csv/sep=,/limit=100
//http://localhost/mediawiki-1.13.5/index.php/Special:Ask/-5B-5BchangeSetID::localhost-2Fmediawiki-2D1.13.5796-5D-5D-5D-5D/-3FhasPatch/format=csv/sep=,/limit=100
    $contentCS = getContentPage($server,'ChangeSet:'.$changeSetFound);
    $CSID = getCSID($content);
    assertEquals($CSIDFound,$CSID);

    $inPushFeed = getCSPushFeed($content);
    assertEquals($inPushFeed,$pushName);

    $previousCSFound = getPreviousCS($content);
    assertEquals($previousChangeSet,$previousCSFound);
    
    $patchFound = getChangeSetPatch($contentCS);
}

function assertPush($content,$pushName,$request) {
    //push name
    $pushNameFound = getPushName($content);
    PHPUnit_Framework_Assert::assertEquals($pushName,$pushNameFound);

    //push request
    $requestFound = getPushRequest($content);
    $requestFound = convertRequest($requestFound);
    PHPUnit_Framework_Assert::assertEquals($request,$requestFound);
}

function assertPullCreated($server,$pullName){
    $content = getContentPage($server,'PullFeed:'.$pullName);
}
//assertPushCreated('http://localhost/wiki1','pushCity','[[Category:city]]');

function assertPullUpdated($server,$pullName,$changeSet){
    $content = getContentPage($server,'PullFeed:'.$pullName);
}

function assertPull(){
    
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
    $posStart = strpos($content, '[[name::PushFeed:');
    if($posStart > 0) $posStart += strlen('[[name::PushFeed:');
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
    if($posStart > 0) $posStart += strlen('[[hasPushHead::ChangeSet : ');
    else return null;
    $posEnd = strlen($content) - 2 - $posStart;
    return substr($content,$posStart,$posEnd);

}

function getChangeSetPatch($content){
    $posStart = strpos($content,'[[hasPatch:');
    if($posStart > 0) $posStart += strlen('[[hasPatch:');
    else return null;
    $posEnd=
}

function getRelatedPushFeed($content){
    
}

function convertRequest($request) {
    $req = str_replace(
        array('-2D', '-23', '-0A', '-20', '-2F', '-5B', '-5D', '-3C', '-3E', '-3C', '-3E', '-26', '-27-27', '-7C', '-26', '-25', '-3F'),
        array('-', '#', "\n", ' ', '/', '[', ']', '<', '>', '&lt;', '&gt;', '&amp;', '\'\'', '|', '&', '%', '?'), $request);
    return $req;
}

?>
