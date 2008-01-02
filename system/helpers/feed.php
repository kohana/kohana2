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

		// Disable error reporting while opening the feed
		$ER = error_reporting(0);

		// Allow loading by filename or raw XML string.
		$feed = is_file($feed) ? simplexml_load_file($feed) : simplexml_load_string($feed);

		// Restore error reporting
		error_reporting($ER);

		// Feed could not be loaded
		if ($feed === FALSE)
			return array();

		// Detect the feed type. RSS 1.0/2.0 and Atom 1.0 are supported.
		$feed = isset($feed->channel) ? $feed->xpath('//item') : $feed->entry;

		$i = 0;
		$items = array();

		foreach ($feed as $item)
		{
			if ($i++ === $limit)
				break;

			$items[] = (array) $item;
		}

		return $items;
	}

} // End rss