<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Google\Cloud\Datastore\DatastoreClient;
use function keywordEngine\removePlural;

// Modules to test
require_once __DIR__ . "/../keywordEngine.php";



// load a list of keywords from a file, It will be used as a list of words to omit from keywords
$filename = __DIR__ . "/../data/omit.txt";
$omitString = file_get_contents($filename);
$omit = \keywordEngine\extractKeywords($omitString);

$filename = __DIR__ . "/../data/pluralsEs.json";
$pluralsContent = file_get_contents($filename);

$pluralsEs = json_decode($pluralsContent, true);



// Testing the keyword extractor
$strings[] = "Lote: de Fresas y aceitunas";
$strings[] = "Lote: de\nFresas y aceitunas";
$strings[] = "Recambio\nregadío para huertas de arroces";
$strings[] = "Coche: ñoño";
$strings[] = "Coches: Rojos aa ñoños arroces construcciones algodonales flores";
$strings[]= "Producto 167384 en rojo modelo 1111 1";
$strings[]= "Producto 511 modelo 1111 1";
$strings[]= " : Producto a 511 bb modelo 1111 1 #$% #$%45 ";


// Get keywords from text strings
// It should return the string as an array of keywords, excluding article words, etc.. ('the' 'for'), punctuations/symbols, accent/tilde, 
foreach ($strings as $value) {
    $keywords = \keywordEngine\extractKeywords($value, $omit);
    $keywords = \keywordEngine\removePlural($keywords, $pluralsEs);
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
    $ds->key("product", "myKey5"),
    $ds->key("product", "myKey6"),
    $ds->key("product", "myKey7"),
];


// index a title
// itshould insert the keywords in the database with the parentKey
$tr = $ds->transaction();
foreach ($strings as $i => $string) {
    \keywordEngine\indexEntity($tr, $productKeys[$i], $string);
}
$tr->commit();


// search the index
// should return an array of keys for the recods containin the keywords
$search = [
    "lote",
    "coche",
    "moto",
    "coche rojo",
    "coches rojos con flores",
    "modelo 511",

];
foreach ($search as $queryString ) {
    echo "searching for: $queryString";
    $result = \keywordEngine\searchIndex($queryString);
    var_dump($result);
}

