<?php

class revisionPool {
    private $mcontent = array();
    private $mpoolFile, $mlocation;

    public function __construct(/*$location*/) {
        // $this->mlocation = $location;
         $this->initializePool();
         }


     function initializePool()
     {
          global $mpoolFile/*, $mlocation*/;
          if(!$mpoolFile){
          $mpoolFile = fopen(/*$mlocation*/"/home/mullejea/Bureau/pool.txt", "w");
          if(!$mpoolFile)
          {
              return false;
          }
          else
          {
          fclose($mpoolFile);
          }
          }
     }

     function storePool()
     {
          global $mpoolFile/*, $mlocation*/;
         
          $mpoolFile = fopen(/*$mlocation*/"/home/mullejea/Bureau/pool.txt", "w");
          if(!$mpoolFile)
          {
              return false;
          }
          else
          {
              $ser = serialize($this->getContent());
              fwrite($mpoolFile, $ser);
              //flush();
              fclose($mpoolFile);
          }
        
     }

      function loadPool()
     {
             global $mpoolFile/*, $mlocation*/;

          $mpoolFile = fopen(/*$mlocation*/"/home/mullejea/Bureau/pool.txt", "r");
          if(!$mpoolFile)
          {
              return false;
          }
          else
          {
              $ser = fread($mpoolFile, filesize("/home/mullejea/Bureau/pool.txt"));
              fclose($mpoolFile);
              $this->setContent(unserialize($ser));
          }
              
     }

     function setContent($array)
    {
        $this->mcontent = $array;
    }

    function getContent()
    {
        return $this->mcontent;
    }

    function remove($key)
    {
        if(!is_null($this->mcontent))
        {
            return $this->mcontent[$key];
        }
    }

    function get($key)
    {
        if(!is_null($this->mcontent))
        {
            return $this->mcontent[$key];
        }
        return NULL;
    }

}

?>
