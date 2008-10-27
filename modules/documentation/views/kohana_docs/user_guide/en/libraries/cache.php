<?php
$page->metadata = array
(
	'status'    => 'draft', // Completion status of the page
	'version'   => '2.2',   // Version of Kohana it's compatible with
	'class'     => 'Cache', // Class name the page contents relates to, used to generate API links
	'prev_page' => array(),      // URI to use in previous link
	'next_page' => array('libraries/calendar', 'Calendar Library'),
	'related'   => array    // Links related to this page, can be an internal URI or a full URL
	(
		'helpers/expires'                      => 'Expires helper',
		'http://en.wikipedia.org/wiki/Caching' => 'Caching on Wikipedia'
	)
);
?>
# Cache Library

Kohana lets you cache any data in order to achieve maximum performance.

The majority of web pages served today are generated dynamically, usually by an application server querying a back-end database. Caching consists in storing objects or pages in their fully rendered state so that they are directly loaded for next requests. It allows to cut response times and save server resources and memory.

**What should I cache?** Any objects or content requiring "heavy" dynamic generation.

Kohana's cache library can currently store caches in various containers including file and database. This is configurable by setting the driver. Cached objects or contents can be loaded using a powerful tag system or with their identifier.

For the API documentation:
  * not available yet

---

## Configuration {#configuration}
Configuration is done in the `application/config/cache.php` file, if it's not there take the one from `system/config` and copy it to the application folder (see [cascading filesystem](http://en.wikipedia.org/wiki/Image:Kohana-modules.png)):

<code class="php">$config['driver']   = 'file';
$config['params']   = APPPATH . 'cache';
$config['lifetime'] = 1800;
$config['requests'] = 1000;
</code>

### Drivers

`config['driver']` sets the driver, which is the container for your cached files. There are 6 different drivers:
  * File - File cache is fast and reliable, but requires many filesystem lookups.
  * SQlite - Database cache can be used to cache items remotely, but is slower.
  * Memcache - Memcache is very high performance, but prevents cache tags from being used.
  * APC - Alternative Php Cache
  * Eaccelerator
  * Xcache

### Driver parameters

`$config['params']` contains driver specific parameters. (in above example - path to server writable cache dir)

### Cache Lifetime

`$config['lifetime']` sets the lifetime of the cache. Specific lifetime can be set when creating a new cache. 0 means it will never be deleted automatically

### Garbage Collector

`$config['requests']` average number of requests before automatic garbage collection begins. Set to a negative number will disable automatic garbage collection

---

## How do I set up caching in my application?

Suppose you want to retrieve some information from your database and build a table of the entries you get. To cache the generated content, you would use in your controller code like this:

<code class="php">$this->cache = new Cache;

$table = $this->cache->get('table');

if ( ! $table) {
	$table = build_table();
	$this->cache->set('table', $table, array('mytag1', 'mytag2'), 3600);
}

echo $table;
</code>

There are 3 main steps:
  * Instantiate the cache library
  * Get the cache:
	*  If the cache doesn't exist, we build the table by querying the database, we cache it for 1 hour (3600 seconds) for next requests and we print it.
	*  If the cache exists, we directly print the cached version of the table

---

## Loading the library {#loading}

<code class="php">$this->cache = new Cache;</code>

## Methods

  * `$this->cache->set($id, $data, $tags = NULL, $lifetime = NULL)` is used to set caches.
  * `$this->cache->get($id)` retrieves a cache with the given $id, returns the data or NULL
  * `$this->cache->find($tag)` supply with a string, retrieves all caches with the given tag.
  * `$this->cache->delete($id)` deletes a cache item by id, returns a boolean.
  * `$this->cache->delete_tag($tag)` deletes all cache items with a given tag, returns a boolean.
  * `$this->cache->delete_all()` deletes all cache items, returns a boolean.

---

## Setting caches {#setting}
### set
`$this->cache->set($id, $data, $tags = NULL, $lifetime = NULL)` is used to set caches.

  * `$id` The id should be unique
  * `$data` If $data is not a string it will be serialized for storage.
  * `$tags`defaults to none, an array should be supplied.  This is useful when grouping caches together.
  * `$lifetime` specific lifetime can be set. If none given the default lifetime from the configuration file will be used.

<code class="php">$data = array('Jean Paul Sartre', 'Albert Camus', 'Simone de Beauvoir');

$tags = array('existentialism', 'philosophy', 'french');
$this->cache->set('existentialists', $data, $tags);
</code>

---

## Finding and getting caches {#getting}
### get
`$this->cache->get($id)` retrieves a cache with the given $id, returns the data or NULL

<code class="php">print_r($this->cache->get('existentialists'));
//returns:
// Array (
	[0] => Jean Paul Sartre
	[1] => Albert Camus
	[2] => Simone de Beauvoir
)
</code>

### find
`$this->cache->find($tag)` supply with a string, retrieves all caches with the given tag.
<code class="php">$food = array('French bread','French wine','French cheese');

$this->cache->set('food', $food, array('french'));

print_r($this->cache->find('french'));
//returns
//Array (
	[existentialists] => Array (
		[0] => Jean Paul Sartre
		[1] => Albert Camus
		[2] => Simone de Beauvoir
	)
	[food] => Array (
		[0] => French bread
		[1] => French wine
		[2] => French cheese
	)
)
</code>

---

## Deleting caches {#deleting}

There are several methods to delete caches

### delete

`$this->cache->delete($id)` deletes a cache item by id, returns a boolean
<code class="php">$this->cache->delete('food');</code>

### delete_tag

`$this->cache->delete_tag($tag)` deletes all cache items with a given tag, returns a boolean
<code class="php">$this->cache->delete_tag('french');</code>

### delete_all

`$this->cache->delete_all()` deletes all cache items, returns a boolean
<code class="php">$this->cache->delete_all();</code>

---

## SQLite Driver Schema {#sqlite_schema}

If you use the SQlite driver to store the caches the table can be constructed with this query.

<code class="php">create table caches(
	id varchar(127),
	hash char(40),
	tags varchar(255),
	expiration int,
	cache blob
);
</code>
