<?php


/**
 *A blobInfo is the logootPosition array and the text lines array
 * corresponding to a specified revision
 *
 * @author mullejea
 */
class BlobInfo {
    private $mBlobInfo = array();

    private $mTextImage = array();

    public function __construct() {

    }

/**
 * returns the model (logootPositions + text lines)
 * either load from DB if it exists or new object (BlobInfo)
 * @param <Integer> $rev_id
 * @return <Object> BlobInfo object
 */
    public static function loadBlobInfo($rev_id) {
        if($rev_id!=0){
            return self::getBlobInfoDB($rev_id);
        }
        else{
            return new BlobInfo();
        }
    }

    function getBlobInfo(){
        return $this->mBlobInfo;
    }

    function setBlobInfo($blobInfo){
        $this->mBlobInfo = $blobInfo;
    }

    function setBlobInfoText($textImage){
        $this->mTextImage = $textImage;
    }

    function getBlobInfoText(){
        return $this->mTextImage;
    }


    /**
     * to add a position to the blobInfo (the model)
     * @param <Integer> $lineNumber
     * @param <Object> $position
     */
    function add($lineNumber, $position){

        $listIds = $this->mBlobInfo;
        //position shifting
        $nbLines = count($listIds);
        
        for($i=$nbLines+1; $i>$lineNumber; $i--){
            $listIds[$i] = $listIds[$i-1];
        }
        unset ($listIds[$lineNumber]);
        $listIds[$lineNumber] = $position;
        //unset the current blobinfo and refill it
        unset ($this->mBlobInfo);
        $this->setBlobInfo($listIds);
    }

    /**
     * to add a line to the blobInfo (the model)
     * @param <Integer> $lineNumber
     * @param <Object> $line
     */
    function addLine($lineNumber, $line){

        $listLines = $this->mTextImage;
        //position shifting
        $nbLines = count($listLines);
       
        for($i=$nbLines+1; $i>$lineNumber; $i--){
            $listLines[$i] = $listLines[$i-1];
        }
        unset ($listLines[$lineNumber]);
        $listLines[$lineNumber] = $line;
        //unset the current blobinfo and refill it
        unset ($this->mTextImage);
        $this->setBlobInfoText($listLines);
    }

    /**
     * to delete a position to the blobInfo (the model)
     * @param <Integer> $lineNb
     */
    function delete($lineNb){
        $this->mBlobInfo = $this->array_delete_key($this->mBlobInfo, $lineNb);
        $this->keyShifting($lineNb);
    }

    /**
     * to delete a line in the blobInfo (the model)
     * @param <Integr> $lineNb
     */
    function deleteLine($lineNb){
        $this->mTextImage = $this->array_delete_key($this->mTextImage, $lineNb);
        $this->textKeyShifting($lineNb);
    }

    /**
     * to get the previous position (logootPosition)
     * @param <Integer> $lineNumber
     * @return <Object> LogootPosition
     */
    function getPrevPosition($lineNumber){
        $listIds = $this->mBlobInfo;
        $exists = false;
        $predecessor;


        for($i=$lineNumber-1; $i>0; $i--){
            if(isset ($listIds[$i])){
                $exists = true;
                $predecessor = $i;
                break;
            }
        }

        //if there is a predecessor
        if($exists==true){
            return $listIds[$predecessor];
        }
        else{
            $posMin = new LogootPosition(array(LogootId::IdMin()));
            return $posMin;
        }
    }

    //to get the next position
    function getNextPosition($lineNumber){
        $listIds = $this->mBlobInfo;

        if(isset ($listIds[$lineNumber])){
            return $listIds[$lineNumber];
        }
        else{
            $posMax = new LogootPosition(array(LogootId::IdMax()));
            return $posMax;
        }
    }

    /**
     * to get a position
     * @param <Integer> $lineNumber
     * @return <Object> logootPosition
     */
    function getPosition($lineNumber){
        $listIds = $this->mBlobInfo;
        return $listIds[$lineNumber];
    }

    /**
     * Size of the logootPosition array
     * @return <Integer>
     */
    function size(){
        return count($this->mBlobInfo);
    }

    //    private function array_delete_value($array,$search) {
    //        $temp = array();
    //        foreach($array as $key => $value) {
    //            if($value!=$search) $temp[$key] = $value;
    //        }
    //        return $temp;
    //    }

    /**
     * used to remove an element (with the given key) of the array
     * @param <array> $array
     * @param <Integer> $search
     * @return <array> array after element deletion
     */
    private function array_delete_key($array,$search) {
        $temp = array();
        foreach($array as $key => $value) {
            if($key!=$search) $temp[$key] = $value;
        }
        return $temp;
    }

    /**
     * used to shift the array elements after deletion
     * it only concerns the logootPosition array
     * @param <Integer> $lineNb
     */
    private function keyShifting($lineNb){
        $listIds = $this->mBlobInfo;
        $tmp = array();
        foreach ($listIds as $key=>$value){
            if($key>$lineNb){
                $tmp[$key-1]=$value;
            }
            else{
                $tmp[$key]=$value;
            }
        }

        $this->setBlobInfo($tmp);
    }

    /**
     * used to shift the array elements after deletion
     * it only concerns the text array
     * @param <Integer> $lineNb
     */
    private function textKeyShifting($lineNb){
        $listLines = $this->mTextImage;
        $tmp = array();
        foreach ($listLines as $key=>$value){
            if($key>$lineNb){
                $tmp[$key-1]=$value;
            }
            else{
                $tmp[$key]=$value;
            }
        }


        $this->setBlobInfoText($tmp);

    }

/**
 * generation of a position, logoot algorithm
 * @param <Object> $start is the previous logootPosition
 * @param <Object> $end is the next logootPosition
 * @param <Integer> $N number of positions generated (should be 1 in our case)
 * @param <Object> $sid session id 
 * @return <Object> a logootPosition between $start and $end
 */
    function getNPositionID($start, $end, $N, $sid) {
        //$clock = 0;
        $result = array();
        $Id_Max = LogootId::IdMax();
        $Id_Min = LogootId::IdMin();
        $i = 0;

        $pos = array();
        $currentPosition = new LogootPosition($pos);//voir constructeur

        $inf = gmp_init("0");
        $sup = gmp_init("0");

        $isInf = false;

        while (true) {
            $inf = gmp_init($start->get($i)->getInt());

            if($isInf==true)
            $sup = gmp_init(INT_MAX);
            else
            $sup = gmp_init($end->get($i)->getInt());

            if (gmp_cmp(gmp_sub(gmp_sub($sup, $inf), gmp_init("1")), $N)>0) {
                //				inf = start.get(i).getInteger();
                //				sup = end.get(i).getInteger();
                break;
            }

            $currentPosition->add($start->get($i));

            $i++;

            if ($i == $start->size())
            $start->add($Id_Min);

            if ($i == $end->size())
            $end->add($Id_Max);

            if(gmp_cmp($inf, $sup)<0)$isInf=true;

        }

$binf = gmp_add($inf, gmp_init("1"));
$bsup = gmp_sub($sup, gmp_init("1"));
$slot = gmp_sub($bsup, $binf);
$step = gmp_div_q($slot, $N);

        $old = clone $currentPosition;

        if (gmp_cmp($step, INT_MAX)>0) {
            $lstep = INT_MAX;

            $r = clone $currentPosition;

            $r->set($i, gmp_strval($this->random($inf, $sup)), $sid/*, $clock*/);

            $result[]=$r;//result est une arraylist<Position>
            return $result;
        } else
        $lstep = $step;

        if (gmp_cmp($lstep, gmp_init("0")) == 0) {
            $lstep = gmp_init("1");
        }

        $p = clone $currentPosition;

        $p->set($i, gmp_strval($inf), $sid/*, $clock*/);
        for ($j = 0; $j < gmp_intval($N); $j++) {
            $r = clone $p;
            if (!gmp_cmp($lstep, gmp_init("1")) == 0) {

                $add = $this->random(gmp_init($p->get($i)->getInt()),
                                    gmp_add(gmp_init($p->get($i)->getInt()), $lstep));

                $r->set($i, gmp_strval($add), $sid/*, $clock*/);
            } else
            $r->add1($i, gmp_init("1"), $sid/*, $clock*/);

           
            $result[]=clone $r;//voir
            $old = clone $r;
            $p->set($i, gmp_strval(gmp_add($p->get($i)->getInt(), $lstep)), $sid/*,$clock*/);

        }
        return $result;
    }

/**
 * adapted binary search
 * $arr is the positions'array of the document (this blobInfo)
 * "$position" is ressearched in this $arr, the function returns:
 * ->the position in the array if it is found,
 * ->'-1' if $position is before the first element,
 * ->'-2' if $position is after the last element or
 * -> an array with both positions in the array surrounding $position
 * @param <Object> $position LogootPosition
 * @param <function> $fct
 * @return <array or Integer>
 */
    function dichoSearch1($position,  $fct = 'dichoComp1')
    {

        $arr = $this->mBlobInfo;
        if(count($arr)==0){
            return -1;
        }else{
            $gauche = 1;
            $droite = count($arr);
            $centre = round(($droite+$gauche)/2);

            if(count($arr)>2){
                while($centre != $droite && $centre != $gauche )
                {

                    if($this->$fct($position, $arr[$centre]) == -1)
                    {
                        $droite = $centre;
                        $centre = floor(($droite+$gauche)/2);
                    }
                    if($this->$fct($position, $arr[$centre]) == 1)
                    {
                        $gauche = $centre;
                        $centre = round(($droite+$gauche)/2);
                    }
                    if($this->$fct($position, $arr[$centre]) == 0)
                    {
                        return $centre;
                    }

                }
            }else{/*with an array<=2*/
                if($this->$fct($position, $arr[$gauche]) == 0) return $gauche;
                elseif($this->$fct($position, $arr[$droite]) == 0)
                                                               return $droite;
            }

            // if there is no occurence
            ksort($arr, SORT_NUMERIC);
            reset($arr);
            $firstElementKey = key($arr);
            end($arr);
            $lastElementKey = key($arr);

            if($this->$fct($position, $arr[$firstElementKey]) == -1)
            return -1; /* if the value is less than the first element of
                              the array*/
            elseif($this->$fct($position, $arr[$lastElementKey]) == 1)
            return -2;/* if the value is greater than the last element of
                              the array*/
            else  /*else we return the values surrounding the ressearched
                    value */
            return array(0=>$gauche, 1=>$droite);
        }
    }

    /**
     * utility function used in the binary search
     * @param <Object> $position1 LogootPosition
     * @param <Object> $position2 LogootPosition
     * @return <Integer> -1, 0 or 1
     */
    function dichoComp1($position1, $position2)
    {
       
        //if both positions are 1 vector Ids
        if($position1->size()==1 && $position2->size()==1){
            $tab1= $position1->getThisPosition();
            $tab2= $position2->getThisPosition();
            if($position1->lessThan($tab1[0], $tab2[0])){
                return -1;
            }
            if($position1->greaterThan($tab1[0], $tab2[0])){
                return 1;
            }
            if($position1->equals($tab1[0], $tab2[0])){
                return 0;
            }
        }
        else{//else if both logootIds are n vectors Ids
            if($position1->nLessThan($position2)){
                return -1;
            }
            if($position1->nGreaterThan($position2)){
                return 1;
            }
            if($position1->nEquals($position2)){
                return 0;
            }
        }
    }

/**
 * the binary search function 'dichosearch' returns the place to execute
 *  the operation (insert or delete) and it is executed (integrated to
 * the BlobInfo (the model)
 * @param <Object> $operation logootOperation (insert or delete)
 */
    function integrateBlob(/*$listPos*/$operation/*, $clock*/){

        //clock setting
//        $clock->incrementClock();
//        $tmpPos = $operation->getLogootPosition();
//
//        $tmpPos->setClock($clock->getValue());
//        $operation->setLogootPosition($tmpPos);

        

        if($operation instanceof LogootIns){
            $result = $this->dichoSearch1($operation->getLogootPosition());

            if(is_array($result)){
        /* position array begins at key '1' which corresponds with line1
         * Lines array begins at key '0' as a normal array, because there is
         * no need to make it corresponding with line numbers
         */


                $this->add($result[1], $operation->getLogootPosition());
                $this->addLine($result[1], $operation->getLineContent());

            }else{
                if($result==-1){/* if the value is less than the first element of
                              the array*/

                    $this->add('1', $operation->getLogootPosition());
                    $this->addLine('1', $operation->getLineContent());

                }
                elseif($result==-2){/* if the value is greater than the last element of
                              the array*/
                    $line = $this->size();
                    $this->add($line+1, $operation->getLogootPosition());
                    $this->addLine($line+1, $operation->getLineContent());

                }
                else{/*the value is found in the array. It should not be, because
         * the logootid has to be unique
         */
                 throw new MWException( __METHOD__.': Logoot algorithm error,
                                position already exists' );
                    
                }
            }

        }
        elseif ($operation instanceof LogootDel) {
            $result = $this->dichoSearch1($operation->getLogootPosition());
            if(is_numeric($result)){
                $this->delete($result);
                $this->deleteLine($result);

            }
            else{
                throw new MWException( __METHOD__.': Logoot algorithm error,
                                did not find the line to delete' );
                
            }

        }

    }

/**
 *Calculate the diff between two texts
 * Returns a list of operations applied on this blobinfo(document model)
 * For each operation (insert or delete), an operation object is created
 * an applied via the 'integrateBlob' function call. These objects are
 *  stored in an array and returned for further implementations.
 *
 * NB: the direct implementation is necessary because the generation of
 * a new position (LogootPosition) is based on the positions of the model
 * (BlobInfo) and so we have to update (immediat integration) this model after
 * each operation (that we get from the difference engine)
 * @global <Object> $wgContLang
 * @param <String> $oldtext
 * @param <String> $newtext
 * @param <Integer> $firstRev if it's the first revision
 * @return <array> list of logootOperation
 */
    function handleDiff($oldtext, $newtext, $firstRev/*, $clock*/)
    {
        $blobInfo = $this;
        global $wgContLang;
       

/* explode into lines*/
        $ota = explode( "\n", $wgContLang->segmentForDiff( $oldtext ) );
        $nta = explode( "\n", $wgContLang->segmentForDiff( $newtext ) );
        $tmp = $nta;
        $counter = 0;

        if(count($ota)==1 && $ota[0]=="") unset ($ota[0]);

        $listPos = array();
        $diffs = new Diff1( $ota, $nta );

/* convert 4 operations into 2 operations*/
        foreach($diffs->edits as $operation){
            switch ($operation->type) {
                case "add":
                    $adds = $operation->closing;
                    ksort($adds, SORT_NUMERIC);

                    foreach($adds as $key=>$lineins){

                        $lineNb = $key;

                        if($firstRev==1){
                            $posMin = new LogootPosition(array(LogootId::IdMin()));
                            $posMax = new LogootPosition(array(LogootId::IdMax()));
                            $positions = $blobInfo->getNPositionID($posMin, $posMax, gmp_init("1"), $sid=session_id());
                            $position = $positions[0];
                            $firstRev = 0;
                        }
                        else{
                            $start = $blobInfo->getPrevPosition($lineNb);
                            $end = $blobInfo->getNextPosition($lineNb);
                            $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid=session_id());
                            $position = $positions[0];
                        }



       


                        $LogootIns = new LogootIns($lineNb, $position, $lineins);
                        $this->integrateBlob($LogootIns/*, $clock*/);
                        
                        $listPos[] = $LogootIns;

                        $counter = $counter + 1;
                    }
                    break;
                case "delete":
                    foreach($operation->orig as $key2=>$linedel){
                        $lineNb2 = $key2 + $counter;
                        $position = $blobInfo->getPosition($lineNb2);
                        //$diffElements[]=$linedel;
                        if(!is_null($position)){
                            $LogootDel = new LogootDel($position, $linedel);
                            $this->integrateBlob($LogootDel/*, $clock*/);
                            $listPos[] = $LogootDel;
                        }
                        $counter = $counter - 1;
                    }

                    break;
                case "copy":
                    break;
                case "change":
                    foreach($operation->orig as $key3=>$linedel1){
                        $lineNb3 = $key3 + $counter;

                        $position = $blobInfo->getPosition($lineNb3);
                        if(!is_null($position)){
                            $LogootDel1 = new LogootDel($position, $linedel1);
                            $this->integrateBlob($LogootDel1/*, $clock*/);
                            $listPos[] = $LogootDel1;
                        }
                        $counter = $counter - 1;
                    }
                    $adds1 = $operation->closing;
                    ksort($adds1, SORT_NUMERIC);

                    foreach($adds1 as $key1=>$lineins1){


                        $lineNb4 = $key1;
                        if($firstRev==1){
                            $posMin = new LogootPosition(array(LogootId::IdMin()));
                            $posMax = new LogootPosition(array(LogootId::IdMax()));
                            $positions = $blobInfo->getNPositionID($posMin, $posMax, gmp_init("1"), $sid=session_id());
                            $position = $positions[0];
                            $firstRev = 0;
                        }
                        else{
                            $start = $blobInfo->getPrevPosition($lineNb4);
                            $end = $blobInfo->getNextPosition($lineNb4);
                            $positions = $blobInfo->getNPositionID($start, $end, gmp_init("1"), $sid=session_id());
                            $position = $positions[0];
                        }

                        $LogootIns1 = new LogootIns($lineNb4, $position, $lineins1);
                        $this->integrateBlob($LogootIns1/*, $clock*/);
                        $listPos[] = $LogootIns1;

                        $counter = $counter + 1;

                    }

                    break;
            }
        }

        return $listPos;
    }

/**
 * transforms the text array into a string
 * @return <String>
 */
    function getTextImage(){

        $tmp = $this->mTextImage;
        $nb=0;


        $nb = sizeof($tmp);
        for($i=1; $i<=$nb; $i++){

            if($i==1) $textImage = $tmp[$i];
            else $textImage = $textImage."\n".$tmp[$i];
        }
        return $textImage;
    }

/**
 * sets the text array attribute (mTextImage) from a string
 * @param <String> $textImage
 */
    function setTextImage($textImage){
        if(!$textImage==""){

            unset ($this->mTextImage);
            //at this point, the array starts at key 0 and we want to start
            //at key 1

            $listLines = explode( "\n", $textImage);
            $tmp = array();
            foreach ($listLines as $key=>$value){
                $tmp[$key+1]=$value;
            }
            $this->setBlobInfoText($tmp);
        }
    }

/**
 * to get a random value between $min and $max
 * @param <gmp_ressource> $min
 * @param <gmp_ressource> $max
 * @return <gmp_ressource> random value
 */
    function random ($min,$max) {
        $min = gmp_add($min, gmp_init("1"));
        $rdm = gmp_add($min, gmp_mod(gmp_random(2), gmp_sub($max, $min)));
        return $rdm;
    }

    

   
/*******************Database access functions************************/
    /**
     * integrate BlobInfo to DB
     * @param <Integer> $rev_id
     * @param <String> $sessionId
     * @param <Object> $blobCB (should have been a causal barrier object but
     * not used yet)
     */
    function integrate($rev_id, $sessionId, $blobCB){
        
        $blobInfo1 = serialize($this);

        wfProfileIn( __METHOD__ );
        $dbw = wfGetDB( DB_MASTER );
        $dbw->insert( 'model', array(
            'rev_id'        => $rev_id,
            'session_id'    => $sessionId,
            'blob_info'     => $blobInfo1,
            'causal_barrier'  => $blobCB,
            ), __METHOD__ );


        wfProfileOut( __METHOD__ );
    }

/**
 * To get the blobInfo of the given revision
 * --> A blobInfo is the logootPosition array corresponding to this revision
 * @param <Integer> $rev_id
 * @return <Object> BlobInfo object
 */
    static function getBlobInfoDB($rev_id){

        wfProfileIn( __METHOD__ );
        $dbr = wfGetDB( DB_SLAVE );
        $blobInfo = $dbr->selectField('model','blob_info', array(
        'rev_id'=>$rev_id), __METHOD__);

        wfProfileOut( __METHOD__ );
        $blobInfo1 = unserialize($blobInfo);

        return $blobInfo1;
    }

/**
 * Our model is stored in the DB just before Mediawiki creates
 * the new revision that's why we have to get the last existing revision ID
 * and the new will be lastId+1 ...
 * @return <Integer> last revision id + 1
 */
    function getNewArticleRevId(){
        wfProfileIn( __METHOD__ );
        $dbr = wfGetDB( DB_SLAVE );
        $lastid = $dbr->selectField('revision','MAX(rev_id)');

        wfProfileOut( __METHOD__ );

        return $lastid + 1;
    }
}
?>
