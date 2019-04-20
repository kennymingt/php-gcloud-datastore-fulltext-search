<?php
namespace keywordEngine;


/**
 * Load data for keyword indexing
 * TODO: Rethink
 *
 * @return void
 */
function loadValues()
{

    // load a list of keywords from a file, It will be used as a list of words to omit from keywords
    $filename = __DIR__ . "/data/omit.txt";
    $omitString = file_get_contents($filename);
    $omit = \keywordEngine\extractKeywords($omitString);

    $filename = __DIR__ . "/data/pluralsEs.json";
    $pluralsContent = file_get_contents($filename);

    $pluralsEs = json_decode($pluralsContent, true);

    return ["omit" => $omit, "plurals" => $pluralsEs];
}


/**
 * Extract keywords from a string
 * * Puntuation and symbols are removed
 * * Accented vowels are transformed to regular vowels
 * * Words shorter than 3 characters are ignored
 * * Words passed in the omit parameter are ignored
 *
 * @param string $string The text string we want to extract keywords from
 * @param string[] $omit List of words we don't want to convert to keywords
 * @return string[] List of keyword extracted from the text
 */
function extractKeywords($string, $omit = null)
{
    // to lowercase
    $string = strtolower($string);

    // trim, remove returns and symbols
    // $string = trim($string); // we are trimming at the end 
    $string = str_replace("\n", " ", $string); // Why regex doesn't remove this?
    $string = str_replace("\r", " ", $string);
    $string = preg_replace("/[^a-zA-Z0-9ñáéíóúàèìòùäëïöüç\s]/u", " ", $string);

    
    // Replace accent symbols for rgular vowels to index
    $accentLetters = [
        ["á", "é", "í", "ó", "ú"],
        ["à", "è", "ì", "ò", "ù"],
        ["ä", "ë", "ï", "ö", "ü"],
    ];
    $letters = ["a", "e", "i", "o", "u"];
    foreach ($accentLetters as $arr) {
        $string = str_replace($arr, $letters, $string);
    }
    // $string = str_replace("ñ", "n", $string);
    // Not sure about this one. maybe index it as it is, and change the search string if there are no enough results
    
    // omit words shorter than 3leters
    $string = preg_replace('/\b[a-z0-9ñáéíóúàèìòùäëïöüç]{1,2}\b/ui', ' ', $string);
    // $string = preg_replace('/\b\w{1,2}\b/i',' ',$string);

    // omit undesired words 
    // foreach ($omit as &$word) {
    //     $word = '/\b' . preg_quote($word, '/') . '\b/u';
    // }

    // $string = preg_replace($omit, " ", $string);

    if ($omit != null) {
        $string = preg_replace('/\b(' . implode('|', $omit) . ')\b/u', '', $string);
    }

    // remove double spaces
    $string = preg_replace("/\s+/", " ", $string);


    // Sometimes, after removing words, there is a white space at the end, explode will create an entry for it
    $string = \trim($string);


    $keywords = explode(" ", $string);

    return $keywords;
}

/**
 * Remove the plural from the word
 *
 * @param string[] $words List of words to be transformed
 * @param array[string]string $dict Transformation rules
 * @return string[] List of transformed words
 */
function removePlural($words, $dict)
{
    foreach ($dict as $key => $value) {

        foreach ($words as &$word) {
            // match $key with word ending
            $wordEnd = \substr($word, -1 * \strlen($key));

            if ($wordEnd == $key) {
                $word = \substr_replace($word, $value, -1 * \strlen($key));
            }
        }
    }

    return $words;
}
