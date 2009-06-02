<?php
class phpwikibot
{	public $wiki;		//wiki where bot works
	public $epm;		//edit for minute
	public $max_lag;	//max lag to server
public function __construct($username, $password, $wiki='', $epm=5, $lag=5) //log in the wiki
{       if (!isset($username)||!isset($password))
		die("\r\nError: configuration variables not set\r\n");
	
		$this->wiki = $wiki; //if is a wiki project
	$this->epm = 60/$epm; //set edit per minute
	$this->max_lag = $lag; //set max lag to server
        $url = $this->wiki.'api.php?action=login&lgname='.$username.'&lgpassword='.$password.'&format=php';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "wpName=".$username."&wpPassword=".$password."&wpLoginattempt=true");
        $input = curl_exec($ch);
        curl_close($ch);
        $login = unserialize($input);
        $result = $login['login']['result'];
        if ($result != "Success")
        	die("Login failed: ".$result."\r\n");
	else
                echo "\r\nLogged in as $username \r\n";
}


public function get_page($page, $wiki="") //get page's content
{	$wiki=$this->wiki($wiki);
        $name = urlencode($page);
	$php = file_get_contents($wiki.'api.php?action=query&prop=revisions&titles='.$name.'&rvprop=content&format=php');//timestamp|user|comment|content
        $array=$php = unserialize($php);
        $array = array_shift($array);
        $array = array_shift($array);
        $array = array_shift($array);
        $array = array_shift($array);
        $content = $php['query']['pages'][$array]['revisions'][0]["*"];
	echo "Reading ".$page."\n";
        return($content);

}
public function single_replace($page, $oldtext, $newtext, $summary, $except_tag=array(), $regex=0, $minor="", $all=0, $wiki="")
{	$wiki=$this->wiki($wiki);
	if ($all==1) //replace all the pages from $page
	{	$this->replace_all($page, $oldtext, $newtext, $summary, $except_tag, $regex, $minor, 0, $wiki);
		return TRUE;
	}
	elseif ($all==2) //replace all page's content
		$oldcontent=$oldtext;
	else
		$oldcontent=$this->get_page($page, $wiki); //replace only part of page's content
	if (($newcontent=$this->replace($oldcontent, $oldtext, $newtext, $summary, $except_tag, $regex, $minor, $wiki))<>$oldcontent)
	{	//$answer=$this->confirm($oldcontent, $newcontent, $oldtext, $newtext);
//		if ($answer[0]=="y")
			$this->put_page($page, $oldcontent, $newcontent, $summary, $minor, $wiki); //edit the changes
		return TRUE;
	}
	return FALSE;
}
public function multi_replace($page, $array, $summary, $regex=0, $minor="", $all=0, $wiki="")
{	$wiki=$this->wiki($wiki);
	$content=$this->get_page($page, $wiki);
	$oldtext=$array[0];
	$newtext=$array[1];
	$n=count($oldtext);
	if ($all==1) //replace all the pages from $page
	{	$this->replace_all($page, $array, "" , $summary , $regex, $minor, $wiki);
		return TRUE;
	}
	$newcontent=$content;
	for ($i=1; $i<=$n; $i++)
		if ($text=$this->replace($newcontent, $oldtext[$i], $newtext[$i], $summary, $regex, $minor, $wiki)<>FALSE)
			$newcontent=$text; //if the replace is correct it replace the old content
	$this->put_page($page, $summary, $text, $minor, $wiki); //edit the changes
	return TRUE;
}
public function create_page($page, $text, $summary, $wiki) //create a new page
{	$wiki=$this->wiki($wiki);
	$content=$this->get_page($page, $wiki);
	if ($content<>"")//it check if the content is void
		put_page($page, $summary, $text, "", $wiki);
	else
		echo "Error: page exixt already";
}//end create_page
public function category($category, $ns="all", $wiki="", $start="") //get all the pages of a category
{	$wiki=$this->wiki($wiki);
	$cat="Category:".$category;
        $cat = urlencode($cat);//convert the spaces in pluses
	//echo $start== urlencode($start); sleep(10);
	$start=str_replace (" ", "_", $start);
	$url = $wiki.'api.php?action=query&list=categorymembers&cmtitle='.$cat.'&format=xml&cmlimit=500';
	if ($ns<>"all")
		$url.="&cmnamespace=".$ns;
	if ($start<>"")
		$url.="&cmcontinue=".$start;
	$xml=file_get_contents ($url);
	$pages=array();
	$next=strstr($xml, "cmcontinue=");
	$next=substr($next, 12);
	$pos=strpos($next, '" />');
	$next=substr($next, 0, $pos);
	while (strpos($xml, '" />')<>FALSE)
	{ 	$xml=strstr($xml, "ns=");
		$xml=substr($xml, 4);
		$pos=strpos($xml, '"');
		$namespace=substr($xml, 0, $pos);
		$xml=strstr($xml, "title=");
		$xml=substr($xml, 7);
		$pos=strpos($xml, '" />');
		$result=substr($xml, 0, $pos);
		if ($namespace==14)
		{	$pos=strpos($result, ":");
			$result=substr($result, $pos+1);
			$result=$this->category($result);
			foreach ($result as $page)
				if (is_array($page))
					$result=$this->category($page);
			//	else
			//		$result=$page;
		}
		if (is_array($result)==FALSE)
		{	echo "Finding ".$result."\n";
			array_push($pages, $result);
		}
		$i++;
	}
	if ($next<>"")
		array_push($pages, $this->category($category, $ns, $wiki, $next));
	return $pages;
}
public function backlinks($page, $wiki="") //get all backlinks of a page
{	$url=$this->wiki($wiki);
	$page=urlencode($page);
	$url.="api.php?action=query&list=embeddedin&eititle=".$page."&eilimit=500&format=xml";
	$xml=file_get_contents ($url);
	$i=0;
	while (strpos($xml, '" />')<>FALSE)
	{ 	$xml=strstr($xml, "title=");
		$xml=substr($xml, 7);
		$pos=strpos($xml, '" />');
		$result[$i]=substr($xml, 0, $pos);
 		$i++;
	}
	return $result;
}//end backlinks
public function put_notice($page, $template, $summary="", $wiki="") //put a notice template
{	$wiki=$this->wiki($wiki);
	$content=get_page($page, $wiki);
	if (preg_match("/\{\{s\|(.*)\}\}", $content, $val)>0)//if there is already a template stub
		$newtext="{{s|".$val[1]."|".$template."}}";
	else
		$newtext="{{".$template."}}\n";
	return $this->single_replace($page, $content, $newtext, $summary, $wiki);
}//end put_notice
public function get_template($page, $par, $wiki="") //get the values of a template
{	$content=$this->get_page($page, $wiki="");
	$i=0;
	foreach ($par as $name)
	{	$string="/".$name."[ ]{0,}=[ ]{0,}([^\n]{1,})/i";
		preg_match($string, $content,  $val);
			$template[$i]=$val[1];
			$i++;
	}
	$i--;
	$pos=strpos($template[$i], "}}");//check if the template ends
	if ($pos>0)
		$template[$i]=substr($template[$i], $pos-1);
	return $template;
}//end get_template
public function put_template($page, $par, $val, $summary, $wiki="") //put the values of a template
{	$url=$this->wiki($wiki);
	$newtext="{{".$par[0]."\n";//the first array value is the array name
	$i=0;
	array_shift($par);
	foreach ($par as $name)
	{	if (is_array($name)==TRUE)
			$newtext.=put_template($page, $name, $val[$i], $summary, $wiki);
		else
			$newtext.="|".$name." = ".$val[$i]."\n";
		$i++;
	}
	$newtext.="}}";
	return $newtext;
}//end put_template
public function delete_template($page, $template, $wiki="") //completely delete a template
{	$url=$this->wiki($wiki);
	$content=$this->get_page($page, $wiki="");
	$needle="{{".$template;
	$pos=strrpos($content, $needle);
	if ($pos>0)
		$newtext=substr($content, 0, $pos-1);
	$content=stristr($content, $needle );
	$open=$close=$i=0;
	do
	{	if ($content[$i]=="{")
			$open++;
		if ($content[$i]=="}")
			$close++;
		$i++;
	}while ($open<>$close);
	$newtext.=substr($content, $i);
	return $newtext;
} //end delete_template
public function correct_redirect($oldname, $newname, $summary, $wiki="") //correct the redirect's links
{	$url=$this->wiki($wiki);
	$res=$this->backlinks($oldname, $wiki);
	foreach ($res as $page)
	{	$oldname2=str_replace(" ", "_", $oldname);
		$this->replace($page, "[[".$oldname, $newname, $summary, 1);
		$this->replace($page, "[[".$oldname2, $newname, $summary, 1);
	}
} //end correct_redirect
private function wiki($wiki)//manager wiki different from default wiki
{	if ($wiki=="")
		return $this->wiki;//if it dont declarated put default wiki
	elseif (strpos($wiki, "://")==FALSE)
		return "http://".$wiki.".wikipedia.org/w/";//if is a mediawiki project the user write only code language
	return $wiki;//if it is a other wiki project
}
private function put_page($page, $oldtext, $newtext, $summary, $minor="", $wiki="") //edit a page
{	if ($newtext==$oldtext)
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt ($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($ch, CURLOPT_URL, $postrequest);
        $response = curl_exec($ch);
        preg_match('/\<</span>input type\=\\\'hidden\\\' value\=\"(.*)\" name\=\"wpStarttime\" \/\>/i', $response, $starttime);
        preg_match('/\<</span>input type\=\\\'hidden\\\' value\=\"(.*)\" name\=\"wpEdittime\" \/\>/i', $response, $edittime);
        preg_match('/\<</span>input name\=\"wpAutoSummary\" type\=\"hidden\" value\=\"(.*)\" \/\>/i', $response, $autosum);
        preg_match('/\<</span>input type\=\'hidden\' value\=\"(.*)\" name\=\"wpEditToken\" \/\>/i', $response, $token);
	curl_close($ch);
	$postrequest = $url.'api.php?title='.$name.'&action=edit&format=xml&bot&maxlag='.$this->max_lag;
	$postData['text'] = $newtext;
	$postData['token'] = $token[1];
	$postData['summary'] = $summary;
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
	{	echo "Error";
		$return=FALSE;
	}
        if(curl_errno($ch))
            echo curl_error($ch);
        curl_close($ch);
        return $return;
}
private function replace($oldcontent, $oldtext, $newtext, $summary, $except_tag, $regex=0, $minor="", $wiki="") //replace text
{	$wiki=$this->wiki($wiki);
	//if (stripos($except_page, $oldtext)==TRUE)
	//	return FALSE;
	$i=0;
	foreach ($except_tag as $tag)
		if (($except=str_replace($oldtext, $newtext, $tag))<>$tag)
		{	$oldcontent=str_replace($tag, "phpwikibot".$i, $oldcontent);	//method to no show the except text in old content
			$i++;
		}
	if ($regex==1)
		$newcontent=ereg_replace($oldtext, $newtext, $oldcontent); //if is the replace is a regex
	else
		$newcontent=str_replace($oldtext, $newtext, $oldcontent);
	$i=0;
	foreach ($except_tag as $tag)
	{	$newcontent=str_replace("phpwikibot".$i, $tag, $newcontent);	//method to no show the except text in old content
		$i++;
	}
	return $newcontent;
} //end replace
private function confirm($text1, $text2, $oldtext, $newtext) //confirm changes by user
{	$stdin = fopen('php://stdin', 'r');
	while (($pos1=stripos($text1, $oldtext))<>FALSE)
	{	$pos2=stripos($text2, $newtext);
		if ($pos1<=50)
			$start1=0;
		else
			$start1=$pos1-50;
		if ($pos2<=50)
			$start2=0;
		else
			$start2=$pos2-50;
		echo substr($text1, $start1, 100)."\n";
		echo substr($text2, $start2, 100)."\n\n";
		$text1=substr($text1, $pos1+1);
		$text2=substr($text2, $pos2+1);
	}
	echo "Do you want to do these changes? ";
	return fgets($stdin, 100);
} //end confirm
private function replace_all($start, $oldtext, $newtext="", $summary="", $except_tag, $regex=0, $minor="", $all=0, $wiki="") //replace content of the all pages
{	$url=$this->wiki($wiki);
	$start=urlencode($start);
	$url.='api.php?action=query&list=allpages&apfrom='.$start.'&aplimit=500&format=xml';
	$xml=file_get_contents ($url);
	$pages=array();
	$i=0;
	$xml=strstr($xml, "apfrom=");
	$xml=substr($xml, 8);
	$pos=strpos($xml, '" />');
	$next=substr($xml, 0, $pos);
	while (strpos($xml, '" />')<>FALSE)
	{ 	$xml=strstr($xml, "title=");
		$xml=substr($xml, 7);
		$pos=strpos($xml, '" />');
		$result[$i]=substr($xml, 0, $pos);
		$result[$i];
		if ($all==1)
		{	$array=$oldtext; //the content of second value is a array with old text, new text and summary
			$this->multi_replace($page, $array, $summary, $regex, $minor, 0, $wiki);
		}
		elseif ($newcontent=$this->single_replace($result[$i], $oldtext, $newtext, $summary, $except_tag, $regex, $minor, $all, $wiki)<>FALSE)
		{
		}
 		$i++;
	}
	$result=$this->replace_all($next, $oldtext, $newtext, $summary, $regex, $minor, $wiki);
	array_push($pages, $result);
	return $pages;
	//return TRUE;
}//end replace_all

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
    $retVal = $this->submitPage($newtext, $newtext1, $token, $token1, $summary, $minor, $page, $starttime, $edittime, $starttime1, $edittime1);
    return $retVal;
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
		$postData1['wpMinoredit'] = $minor;
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
		//$return=FALSE;
        $return=$response2;
	}
        if(curl_errno($ch))
            echo curl_error($ch);
        curl_close($ch);
        return $return;
}

//test OK when conflict solving suits
function test($newtext, $newtext1, $response2){
    $value = 'merge entre: '.$newtext.' et '.$newtext1;
    if($value===$response2) {
        echo "TEST OK!\n";
        echo "this is the new text of the article: ".$response2."\n";
        echo "this is what it should display: ".$value."\n";
    }
    else {
        echo "TEST FAILED!\n";
        echo "this is the new text of the article: ".$response2."\n";
        echo "this is what it should display: ".$value."\n";
    }
}

//private function wiki($wiki)//manager wiki different from default wiki
//{	if ($wiki=="")
//		return $this->wiki;//if it dont declarated put default wiki
//	elseif (strpos($wiki, "://")==FALSE)
//		return "http://".$wiki.".wikipedia.org/w/";//if is a mediawiki project the user write only code language
//	return $wiki;//if it is a other wiki project
//}
} //end class
?>

