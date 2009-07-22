<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of logoot
 *
 * @author Jean-Philippe Muller
 */
interface  logoot {
    
     //generate includes integrate
    function generate($oldText, $newText);//{return (array)$opList;}
    function integrate($opList);//{return (object)$modelafterIntegrate;}
    function getModel();
}
?>
