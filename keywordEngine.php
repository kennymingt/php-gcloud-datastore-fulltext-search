<?php
namespace keywordEngine;

use Google\Cloud\Datastore\DatastoreClient;
use Google\cloud\Datastore\Key;


/**
 * Indexes a datastore key, and their related list of keywords
 *
 * @param DatastoreClient $ds Datastore Client
 * @param Key $key Key of the enity we want to index
 * @param string[] $keywords List of keywords
 * @return void
 */
function index($ds, $key, $keywords)
{
    // $ds =new DatastoreClient();
    $entityId = $key->pathEndIdentifier();
    $keywordKey = $ds->key("keywordIndex", $entityId)->ancestorKey($key);

    // find it in the database
    $keywordEntity = $ds->lookup($keywordKey);

    if ($keywordEntity == null) {
        $keywordEntity = $ds->entity($keywordKey);
    }

    $keywordEntity["list"] = $keywords;

    $ds->upsert($keywordEntity);
}


/**
 * Search the datastore looking for keywords
 *
 * @param string[] $keywords List of keywords to search for
 * @return string[] List of entityIds of matching records
 */
function searchByKeywords($keywords)
{
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
