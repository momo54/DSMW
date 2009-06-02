<?php
class phpwikibot
{	public $wiki = 'http://localhost/www/mediawiki-1.13.2/';		//wiki where bot works
	public $epm;		//edit for minute
	public $max_lag;	//max lag to server
public function __construct($username, $password, $wiki='', $epm=5, $lag=5) //log in the wiki
{

}
public function get_page($page, $wiki="") //get page's content
{	$wiki=$this->wiki($wiki);
        $name = urlencode($page);
	$php = file_get_contents($wiki.'api.php?action=query&prop=revisions&titles='.$name.'&rvprop=content&format=php');
        $array=$php = unserialize($php);
        $array = array_shift($array);
        $array = array_shift($array);
        $array = array_shift($array);
        $array = array_shift($array);
        $content = $php['query']/*['pages'][4]['revisions'][0]["*"]*/;
        $content1 = $content['pages'];
        $content2 = $content1[5];
        $content3 = $content2['revisions'];
        $content4 = $content3[0];
        $content5 = $content4["*"];
	echo "Reading ".$page."\n";
        return($content5);
}

function editPage($page, $oldtext, $newtext, $newtext1, $summary, $minor="", $wiki=""){
    if ($newtext==$oldtext)
	{	echo "Same content. ".$page." isn't edited\n"; //the new content is the same, nothing changes
		return FALSE;
	}
	if ($newtext=="")
	{	echo "Error: the content is void. ".$page." isn't edited\n"; //the new content is void, nothing changes
		return FALSE;
	}
	$url=$this->wiki($wiki);
        $name = urlencode($page);
        $postrequest = $url.'index.php?action=edit&title='.$name;


        //first edit of the page $page
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt ($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($ch, CURLOPT_URL, $postrequest);
        $response = curl_exec($ch);
        preg_match('/\<input type\=\\\'hidden\\\' value\=\"(.*)\" name\=\"wpStarttime\" \/\>/i', $response, $starttime);
        //<input type='hidden' value="20081221114539" name="wpStarttime" />
        preg_match('/\<input type\=\\\'hidden\\\' value\=\"(.*)\" name\=\"wpEdittime\" \/\>/i', $response, $edittime);
        preg_match('/\<input name\=\"wpAutoSummary\" type\=\"hidden\" value\=\"(.*)\" \/\>/i', $response, $autosum);
        preg_match('/\<input type\=\'hidden\' value\=\"(.*)\" name\=\"wpEditToken\" \/\>/i', $response, $token);
	curl_close($ch);

//second edit of the page $page
    $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch1, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt ($ch1, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($ch1, CURLOPT_URL, $postrequest);
        $response1 = curl_exec($ch1);
         preg_match('/\<input type\=\\\'hidden\\\' value\=\"(.*)\" name\=\"wpStarttime\" \/\>/i', $response1, $starttime1);
        //<input type='hidden' value="20081221114539" name="wpStarttime" />
        preg_match('/\<input type\=\\\'hidden\\\' value\=\"(.*)\" name\=\"wpEdittime\" \/\>/i', $response1, $edittime1);
        preg_match('/\<input name\=\"wpAutoSummary\" type\=\"hidden\" value\=\"(.*)\" \/\>/i', $response1, $autosum1);
        preg_match('/\<input type\=\'hidden\' value\=\"(.*)\" name\=\"wpEditToken\" \/\>/i', $response1, $token1);
	curl_close($ch1);

    //submit
    $this->submitPage($newtext, $newtext1, $token, $token1, $summary, $minor, $page, $starttime, $edittime, $starttime1, $edittime1);
    return true;
}

function submitPage($newtext, $newtext1, $token, $token1, $summary, $minor, $page, $starttime, $edittime, $starttime1, $edittime1, $wiki=""){
    $url=$this->wiki($wiki);
    $name = urlencode($page);
    $postrequest = $url.'index.php?title='.$name.'&action=submit';

    //submission of the first edit
    $postData['wpStartime']=$starttime[1];
    $postData['wpEdittime']=$edittime[1];
	$postData['wpTextbox1'] = $newtext;
	$postData['wpEditToken'] = $token[1];
	$postData['wpSummary'] = $summary;
        if ($minor != null)
		$postData['wpMinoredit'] = $minor;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postrequest);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt ($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);

     //submission of the second edit ==> conflict
    $postData1['wpStartime']=$starttime1[1];
    $postData1['wpEdittime']=$edittime1[1];
	$postData1['wpTextbox1'] = $newtext1;
	$postData1['wpEditToken'] = $token1[1];
	$postData1['wpSummary'] = $summary;
        if ($minor != null)
		$postData['wpMinoredit'] = $minor;
        $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_URL, $postrequest);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch1, CURLOPT_POST, 1);
        curl_setopt ($ch1, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt ($ch1, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($ch1, CURLOPT_POSTFIELDS, $postData1);
        $response1 = curl_exec($ch1);





        if (preg_match('/^Waiting for (.*) seconds lagged/', $response))
	{       echo "Error: max lag hit, not posted\r\n";
                $return=FALSE;
        }
	elseif (preg_match('/edit result\=\"Success\"/i', $response))
	{	echo "Writing ".$page."\n";
		sleep($epm);
		$return=TRUE;
	}
	else
	{	//getting the raw content of the resulting page

        $postrequest2 = $url.'index.php?action=raw&title='.$name;

        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch2, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt ($ch2, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($ch2, CURLOPT_URL, $postrequest2);
        $response2 = curl_exec($ch2);

        //echo "OK!! the new text of the article is: ".$response2;
        //$this->test($newtext, $newtext1, $response2);
		$return=$response2;
	}
        if(curl_errno($ch))
            echo curl_error($ch);
        curl_close($ch);
        return $return;
}

//test OK when conflict solving suits
//function test($newtext, $newtext1, $response2){
//    $value = 'merge entre: '.$newtext.' et '.$newtext1;
//    if($value===$response2) {
//        echo "TEST OK!\n";
//        echo "this is the new text of the article: ".$response2."\n";
//        echo "this is what it should display: ".$value."\n";
//    }
//    else {
//        echo "TEST FAILED!\n";
//        echo "this is the new text of the article: ".$response2."\n";
//        echo "this is what it should display: ".$value."\n";
//    }
//}

private function wiki($wiki)//manager wiki different from default wiki
{	if ($wiki=="")
		return $this->wiki;//if it dont declarated put default wiki
	elseif (strpos($wiki, "://")==FALSE)
		return "http://".$wiki.".wikipedia.org/w/";//if is a mediawiki project the user write only code language
	return $wiki;//if it is a other wiki project
}
} //end class
?>

