<?php
namespace keywordEngine;

function findKeywords($string, $omit = null)
{
    // to lowercase
    $string = strtolower($string);

    // trim, remove returns and symbols
    $string = trim($string);
    $string = str_replace("\n", "", $string); // Why regex doesn't remove this?
    $string = str_replace("\r", "", $string);
    $string = preg_replace("/[^a-zA-Z0-9ñáéíóúàèìòùäëïöüç\s]/u", " ", $string);


    // Replace accent symbols for rgular vowels to index
    $accentLetters = [
        ["á", "é", "í", "ó", "ú"],
        ["à", "è", "ì", "ò", "ù"],
        ["ä", "ë", "ï", "ö", "ü"],        
    ];
    $letters = ["a", "e","i","o","u"];
    foreach ($accentLetters as $arr) {
        $string = str_replace($arr, $letters, $string);
    }


    // omit words shorter than 3leters
    $string = preg_replace('/(\b[^a-zA-Z0-9ñáéíóúàèìòùäëïöüç].{1,2}\b)/u',' ',$string);


    // omit undesired words 
    // foreach ($omit as &$word) {
    //     $word = '/\b' . preg_quote($word, '/') . '\b/u';
    // }

    // $string = preg_replace($omit, " ", $string);

    if($omit != null) {
        $string = preg_replace('/\b(' . implode('|', $omit) . ')\b/u', '', $string);
    }



    // remove double spaces
    $string = preg_replace("/\s+/", " ", $string);

    $keywords = explode(" ", $string);

    return $keywords;
}
