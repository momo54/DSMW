<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author mullejea
 */
interface Clock {
    public function load();

    public function store();

    public function getValue();

    public function setValue($i);

    public function incrementClock();
}
?>
