<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: feed
 *  Feed helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class feed_Core {

	/**
	 * Method: parse
	 *  Parses a remote feed into an array.
	 *
	 * Parameters:
	 *  feed   - remote feed URL
	 *  limit  - item limit to fetch
	 *
	 * Returns:
	 *  Array of feed items.
	 */
	public static function parse($feed, $limit = 0)
	{
		// Make limit an integer
		$limit = (int) $limit;

		// Create a DOM parser
		$parser = DOMDocument::load($feed);

		// Reset the feed to an empty array
		$feed = array();

		// Choose the name of the entry (RSS or Atom). Atom feeds use <feed>
		// as the XML container, RSS uses <rdf>.
		$entry = ($parser->getElementsByTagName('feed')->length > 0) ? 'entry' : 'item';

		// Parse each of the RSS items
		foreach($parser->getElementsByTagName($entry) as $index => $node)
		{
			if ($limit > 0 AND $index >= $limit)
				break;

			// Create a new data set
			$item = array();

			// Get all the of fields in the feed item
			foreach($node->childNodes as $node)
			{
				// Only add XML element nodes
				if ($node->nodeType === XML_ELEMENT_NODE)
				{
					$item[$node->nodeName] = $node->nodeValue;
				}
			}

			// Add the item data to the feed
			$feed[] = $item;
		}

		return $feed;
	}

} // End rss