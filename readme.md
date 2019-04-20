# PHP google datastore full-text search

MTF - Main Three Functions
* indexEntity - saves into the index db
* seachIndex - query the db and returns matching keys
* deleteIndex - delete an index entity

OTF - OLD Three Functions

They are still there somewhere
* extractKeywords - returns an array with keywords extracted from a string
* index - save a list of keywords into datastore
* searchByKeywords - gets a keyword list and returns an array with Ids. from datastore

## How it works
Extract a list of keyword form a string.

It removes short words, punctuation and others. You can also pass it a list of words that you want to be omited.

It creates an entity with kind "keywordIndex" and the same Id as the key passed.

It saves the keywords related to this Id as a list.

Then the search returns those ids, if it finds the keyword in the list.

## Use
Datastore doesnt have text search, so this helps searching for small projects.

Because of the list size limit, this library is usefull to index small strings of text, like blog titles, or product names. I don't think it can be used for full text document.

As it retreives keysOnly from datastore, there no serialization of the lists, it should be fast enough.

## Cost
As April 2019.


The query costs a 1 Entity read.

The keys cost as small operation.


# TODO: Things I want to include
* Some kind of ranking for each entity. Probably a counter or score, it can be used for most seen posts or best rated products.
* Some sorting of the keys returned. By creation date?
* Remove plurals from keywords. ?
* When searching by more than one keyword, if there are not enough results, search also by single keywords.

