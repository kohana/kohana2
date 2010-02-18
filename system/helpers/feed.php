<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Feed helper class.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class feed_Core {

	/**
	 * Parses a remote feed into an array.
	 * 
	 * ##### Examples
	 * 		//Parse a remote RSS or ATOM feed
	 * 		$feed_items = feed::parse('http://rss.slashdot.org/Slashdot/slashdot');
	 * 		
	 * 		//Parse a local RSS feed from file
	 * 		$feed_items = feed::parse('path/to/awesome_site.rss');
	 * 		
	 * 		//Parse a string as an RSS feed
	 * 		$feed_items = feed::parse($xml);	
	 *
	 * @param string $feed remote feed URL
	 * @param integer $limit item limit to fetch
	 * @return array
	 */
	public static function parse($feed, $limit = 0)
	{
		// Check if SimpleXML is installed
		if( ! function_exists('simplexml_load_file'))
			throw new Kohana_User_Exception('Feed Error', 'SimpleXML must be installed!');

		// Make limit an integer
		$limit = (int) $limit;

		// Disable error reporting while opening the feed
		$ER = error_reporting(0);

		// Allow loading by filename or raw XML string
		$load = (is_file($feed) OR valid::url($feed)) ? 'simplexml_load_file' : 'simplexml_load_string';

		// Load the feed
		$feed = $load($feed, 'SimpleXMLElement', LIBXML_NOCDATA);

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
			if ($limit > 0 AND $i++ === $limit)
				break;

			$items[] = (array) $item;
		}

		return $items;
	}

	/**
	 * Creates a feed from the given parameters.
	 * 
	 * ##### Example
	 * 		//Information for the whole feed
	 * 		$info = array('notes' => 'Foo bar');
	 * 		
	 * 		//List of items inside feed
	 * 		$items = array(
	 * 			array(
	 * 		        'title' => 'My very first feed created by KohanaPHP',
	 * 		        'link' => 'http://www.example.com/article/34',
	 * 		        'description' => 'This article is really nice!',
	 * 		        'author' => 'Flip van Rijn',
	 * 		        'pubDate' => 'Wed, 23 Sept 2009 17:13:25 GMT',
	 * 		    ),
	 * 		);
	 * 		
	 * 		//echo out the feed
	 * 		echo feed::create($info, $items);
	 * 
	 * @param array $info feed information
	 * @param array $items items to add to the feed
	 * @param string $format define which format to use
	 * @param string $encoding define which encoding to use
	 * @return string
	 */
	public static function create($info, $items, $format = 'rss2', $encoding = 'UTF-8')
	{
		$info += array('title' => 'Generated Feed', 'link' => '', 'generator' => 'KohanaPHP');

		$feed = '<?xml version="1.0" encoding="'.$encoding.'"?><rss version="2.0"><channel></channel></rss>';
		$feed = simplexml_load_string($feed);

		foreach ($info as $name => $value)
		{
			if (($name === 'pubDate' OR $name === 'lastBuildDate') AND (is_int($value) OR ctype_digit($value)))
			{
				// Convert timestamps to RFC 822 formatted dates
				$value = date(DATE_RFC822, $value);
			}
			elseif (($name === 'link' OR $name === 'docs') AND strpos($value, '://') === FALSE)
			{
				// Convert URIs to URLs
				$value = url::site($value, 'http');
			}

			// Add the info to the channel
			$feed->channel->addChild($name, $value);
		}

		foreach ($items as $item)
		{
			// Add the item to the channel
			$row = $feed->channel->addChild('item');

			foreach ($item as $name => $value)
			{
				if ($name === 'pubDate' AND (is_int($value) OR ctype_digit($value)))
				{
					// Convert timestamps to RFC 822 formatted dates
					$value = date(DATE_RFC822, $value);
				}
				elseif (($name === 'link' OR $name === 'guid') AND strpos($value, '://') === FALSE)
				{
					// Convert URIs to URLs
					$value = url::site($value, 'http');
				}

				// Add the info to the row
				$row->addChild($name, $value);
			}
		}

		return $feed->asXML();
	}

} // End feed