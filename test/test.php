<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Google\Cloud\Datastore\DatastoreClient;

// Modules to test
require_once __DIR__ . "/../parseString.php";
require_once __DIR__ . "/../keywordEngine.php";



// load a list of keywords from a file, It will be used as a list of words to omit from keywords
$filename = __DIR__ . "/../data/omit.txt";
$omitString = file_get_contents($filename);
$omit = \keywordEngine\extractKeywords($omitString);

var_dump($omit);

$filename = __DIR__ . "/../data/pluralsEs.json";
$pluralsContent = file_get_contents($filename);

$pluralsEs = json_decode($pluralsContent, true);
var_dump($pluralsEs);



$strings[] = "Lote: de Fresas y aceitunas";
$strings[] = "Lote: de\nFresas y aceitunas";
$strings[] = "Recambio\nregadío para huertas de arroces";
$strings[] = "Coche: ñoño";
$strings[] = "Coches: Rojos aa ñoños arroces construcciones algodonales flores";

// Get keywords from text strings
// It should return the string as an array of keywords, excluding article words, etc.. ('the' 'for'), punctuations/symbols, accent/tilde, 
foreach ($strings as $value) {
    $keywords = \keywordEngine\extractKeywords($value, $omit);
    var_dump($value);
    var_dump($keywords);
}

// Loaded from json file
// $pluralsEs = [
//     "ces" => "z",   // Arroces => arroz
//     "nes" => "n",   // Construcciones => construccion
//     "ales" => "al", // ?? Algonodales => algodonal
//     "flores" => "flor", // Iregulares, ??? casos especiales

//     "s" => "",      // Regla general, Cofres => cofre
// ];

$keywords = \keywordEngine\removePlural($keywords, $pluralsEs);

var_dump(($keywords));

$ds = new DatastoreClient();

// This emulate my products
$productKeys = [
    $ds->key("product", "myKey0"),
    $ds->key("product", "myKey1"),
    $ds->key("product", "myKey2"),
    $ds->key("product", "myKey3"),
    $ds->key("product", "myKey4"),
];


// index a title
// itshould insert the keywords in the database with the parentKey
foreach ($strings as $i => $string) {
    $keywords = \keywordEngine\extractKeywords($string);
    $keywords = \keywordEngine\removePlural($keywords, $pluralsEs);

    \keywordEngine\index($ds, $productKeys[$i], $keywords);
}


// search the index


// Example of a search function
function search($search)
{
    global $omit;
    global $pluralsEs;

    echo "Searching for: '$search'\n";

    // Search string may come from an input field
    $searchKeywords = \keywordEngine\extractKeywords($search, $omit);
    $searchKeywords = \keywordEngine\removePlural($searchKeywords, $pluralsEs);
    
    // The search
    $entityIds = \keywordEngine\searchByKeywords($searchKeywords);

    echo "Found the following Ids:\n";
    foreach ($entityIds as $id) {
        echo "$id\n";
    };
    echo "\n\n";
}

// Test the search
// should return an array of keys for the recods containin the keywords
search("lote");
search("coche");

// search the index with two keywords
search("coche rojo");

// search the index with 3 keywords
search("coche rojo oooo");

// Search plurals
search("coches rojos con flores");

