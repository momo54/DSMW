<?php

/**
 * @copyright INRIA-LORIA-ECOO project
 * @author muller jean-philippe
 */
interface Clock {
    public function load();

    public function store();

    public function getValue();

    public function setValue($i);

    public function incrementClock();
}
?>
