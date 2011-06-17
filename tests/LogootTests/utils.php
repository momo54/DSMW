<?php 
$c = 0;

class utils {
	
	static function getNextClock() {
		global $c;
        return ++$c ;
	}
	
	static function getClock() {
        return $c ;
	}
}
?>