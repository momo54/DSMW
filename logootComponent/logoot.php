<?php

/**
 * The interface of the logootComponent
 *
 * @copyright INRIA-LORIA-ECOO project
 * @author Jean-Philippe Muller
 */
interface  logoot {
    
     /*
      * Generates a list of operations and integrates it to the model
      * (generate includes integrate)
      * @param <String> $oldText
      * @param <String> $newText
      *
      * return (array)$opList
      */
    function generate($oldText, $newText);

    /**
     * Integrates an operation list to the model
     * @param <array> $opList logootIns or logootDel array
     *
     * return (object)$modelafterIntegrate
     */
    function integrate($opList);
    
    function getModel();
    
    
    /**
     * 
     * Undo the patch with the $patchId, return the undo patch, 
     * that containt the undo operation.
     * @param $patchId the id of the pacth that must be undone
     */
    function undo($patchId);
    
    
    
}
?>
