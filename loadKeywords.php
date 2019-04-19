<?php

/**
 * Load a list of word from a text file
 */


function loadKeywords($filename){

    $string = file_get_contents($filename);

    $keywords = explode("\n",$string);

    return $keywords;

}