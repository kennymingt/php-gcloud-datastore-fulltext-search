<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Google\Cloud\Datastore\DatastoreClient;

// Modules to test
require_once __DIR__ . "/../loadKeywords.php";
require_once __DIR__ . "/../parseString.php";
require_once __DIR__ . "/../keywordEngine.php";

$strings[] = "Lote: de Fresas y aceitunas";
$strings[] = "Lote: de\nFresas y aceitunas";
$strings[] = "Recambio\nregadío para huertas";
$strings[] = "Coche: ñoño";
$strings[] = "Coche: Rojo aa ñoño";

// load a list of keywords from a file, It will be used as a list of words to omit from keywords
$omit = loadKeywords(__DIR__ . "/../data/omit.txt");
var_dump($omit);

print_r("Testing keyword loading: ");
assert(is_array($omit), "failed");
print_r("\n");


// parsse  a strisng
// it should retsurn the string as an array of words, excluding article words ('the' 'for'), punctuations/simbols, accent/tilde, 
foreach ($strings as $value) {
    $keywords = \keywordEngine\findKeywords($value);
    var_dump($value);
    var_dump($keywords);
}


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
    $keywords = \keywordEngine\findKeywords($string);
    \keywordEngine\index($ds, $productKeys[$i], $keywords);
}


// search the index
// should return an array of keys for the recods containin the keywords
search( "lote") ;
search( "coche") ;

// search the index with two keywords
search( "coche rojo") ;

// search the index with 3 keywords
search( "coche rojo oooo") ;


function search($search)
{
    echo "Searching for: '$search'\n";

    $searchKeywords = \keywordEngine\findKeywords($search);
    $keys = \keywordEngine\searchByKeywords($searchKeywords);

    echo "Id found:\n";
    foreach ($keys as $key => $value) {
        echo "$value\n";
    };
    echo "\n\n";
}





