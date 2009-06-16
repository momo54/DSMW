<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of p2pBot
 *
 * @author marlene
 */
class p2pBot {

    var $bot;

    function  __construct($bot) {
        $this->bot = $bot;
    }

    function createPage($source,$summary,$content) {
        $res = $this->bot->wikiFilter($source,'callbackTestFct',$summary,$content);
        return $res;
    }

    function createPush($name, $request) {
        //$post_vars['url'] = $url;
        $post_vars['name'] = $name;
        $post_vars['keyword'] = $request;
        if ($this->bot->submit( $this->bot->wikiServer . PREFIX . '/index.php?action=pushpage', $post_vars ) ) {
        // Now we need to check whether our edit was accepted. If it was, we'll get a 302 redirecting us to the article. If it wasn't (e.g. because of an edit conflict), we'll get a 200.
            $code = substr($this->bot->response_code,9,3); // shorten 'HTTP 1.1 200 OK' to just '200'
            if ('200'==$code)
                return true;
            else
                return false; // if you get this, it's time to debug.
        }else {
        // we failed to submit the form.
            return false;
        }
    }

    function push($name) {
        $get_vars = '&pull[]='.$name;

        if ($this->submit($this->bot->wikiServer . PREFIX . '/index.php?action=onpush'.$get_vars, $post_vars ) ) {
        // Now we need to check whether our edit was accepted. If it was, we'll get a 302 redirecting us to the article. If it wasn't (e.g. because of an edit conflict), we'll get a 200.
            $code = substr($this->response_code,9,3); // shorten 'HTTP 1.1 200 OK' to just '200'
            if ('200'==$code)
                return true;
            else
                return false; // if you get this, it's time to debug.
        }else {
        // we failed to submit the form.
            return false;
        }
    }

}

function callbackTestFct($content1,$content2) {
    return $content1.' '.$content2;
}

?>
