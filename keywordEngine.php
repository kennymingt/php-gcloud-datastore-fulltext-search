<?php
namespace keywordEngine;

use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Transaction;
use Google\Cloud\Datastore\Key;
use Google\Cloud\Datastore\Query\Query;


require __DIR__ . "/parseString.php";

/**
 * Index an entity for search by keyword
 *
 * @param Transaction $tr Datastore client
 * @param Key $key Entity key
 * @param string $string string to index
 * @return void
 */
function indexEntity($tr, $key, $string, $meta = null)
{

    // Load list of words to omit whil extracting keywords
    // Also load the list of plurals rules
    // TODO: Should I decouple it? pass it as param?
    $values = loadValues();
    $omit = $values["omit"];
    $pluralsEs = $values["plurals"];

    $keywords = \keywordEngine\extractKeywords($string, $omit);
    $keywords = \keywordEngine\removePlural($keywords, $pluralsEs);

    \keywordEngine\index($tr, $key, $keywords, $meta);
}


/**
 * Indexes a datastore key, and their related list of keywords
 *
 * @access private
 * @param DatastoreClient $ds Datastore Client
 * @param Key $key Key of the enity we want to index
 * @param string[] $keywords List of keywords
 * @return void
 */
function index($tr, $key, $keywords, $meta)
{
    $ds =new DatastoreClient();
    $entityId = $key->pathEndIdentifier();
    $keywordKey = $ds->key("keywordIndex", $entityId)->ancestorKey($key);

    // find it in the database
    $keywordEntity = $tr->lookup($keywordKey);

    if ($keywordEntity == null) {
        $keywordEntity = $ds->entity($keywordKey);
    }

    $keywordEntity["list"] = $keywords;

    // add metadata
    if (is_array($meta)) {
        foreach ($meta as $key => $value) {
            $keywordEntity[$key] = $value;
        }
    }

    $tr->upsert($keywordEntity);
}




/**
 * Search the index by a string
 *
 * @param string $search
 * @return Key[] returns the Id of the matching entities
 */
function searchIndex($search, $orderField)
{
    // Load list of words to omit whil extracting keywords
    // Also load the list of plurals rules
    // Should I decouple it? pass it as param?
    $values = loadValues();
    $omit = $values["omit"];
    $pluralsEs = $values["plurals"];

    $searchKeywords = \keywordEngine\extractKeywords($search, $omit);
    $searchKeywords = \keywordEngine\removePlural($searchKeywords, $pluralsEs);

    $keys = \keywordEngine\searchByKeywords($searchKeywords, $orderField);

    return $keys;
}

/**
 * Search the datastore looking for keywords
 *
 * @access private 
 * @param string[] $keywords List of keywords to search for
 * @return string[] List of entityIds of matching records
 */
function searchByKeywords($keywords, $orderField = null)
{
    $ds = new DatastoreClient();

    $query = $ds->query();
    $query->kind("keywordIndex");
    $query->keysOnly();
    if (!is_null($orderField)) {
        $query->order($orderField, Query::ORDER_DESCENDING);
    }
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



/**
 * Delete an indexed entity
 *
 * @param Transation $tr
 * @param Key $key
 * @return void
 */
function deleteIndex($tr, $key)
{
    $ds = new DatastoreClient();
    $id = $key->pathEndIdentifier();
    $indexKey = $ds->key("keywordIndex", $id)->ancestorKey($key);

    $tr->delete($indexKey);
}
