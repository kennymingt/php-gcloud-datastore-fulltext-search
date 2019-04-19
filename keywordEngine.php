<?php
namespace keywordEngine;

use Google\Cloud\Datastore\DatastoreClient;
use Google\cloud\Datastore\Key;


/**
 * Index a key and their related list of keywords
 * @param DatastoreClient   $ds The datastore client
 * @param Key     $key    Key of the Entity we wnt to index
 * @param   String[]          $keywords list of keywords
 */
function index($ds, $key, $keywords) {
    // $ds =new DatastoreClient();
    $entityId = $key->pathEndIdentifier();
    $keywordKey = $ds->key("keywordIndex", $entityId)->ancestorKey($key);

    // find it in the database
    $keywordEntity = $ds->lookup($keywordKey);

    if($keywordEntity == null) {
        $keywordEntity = $ds->entity($keywordKey);
    }

    $keywordEntity["list"] = $keywords;

    $ds->upsert($keywordEntity);

}


/**
 * @param String[]      $keywords   list of keywords to search
 * @return  String[]    Array with Ids
 */
function searchByKeywords($keywords){
    $ds = new DatastoreClient();

    $query = $ds->query();
    $query->kind("keywordIndex");
    $query->keysOnly();
    foreach ($keywords as $keyword) {
        $query->filter("list", "=", $keyword);
    }

    $result = $ds->runQuery($query);

    $keys = [];
    foreach ($result as $key => $value) {
        $id = $value->key()->pathEndIdentifier();

        array_push($keys, $id);
    };

    return $keys;
}