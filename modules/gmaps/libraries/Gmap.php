<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Google Maps API integration.
 *
 *  License:
 *  author    - Woody Gilk
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Gmap_Core {

	/**
	 * Creates a new marker to place on a map.
	 *
	 * @param   string|float  address|longitude
	 * @param   float         latitude
	 * @return  object        Gmap_Marker
	 */
	public static function marker($lon, $lat = NULL)
	{
		if ($lat === NULL)
		{
			// Get the latitude and longitude by address
			list ($lon, $lat) = Gmap::address_to_ll($address = $lon);
		}

		// Create a new marker
		$marker = new Gmap_Marker((float) $lat, (float) $lon);

		// Set the address of the marker
		isset($address) and $marker->address = $address;

		return $marker;
	}


	/**
	 * Retrieves the latitude and longitude of an address.
	 *
	 * @param   string  address
	 * @return  array   longitude, latitude
	 */
	public static function address_to_ll($address)
	{
		$lon = NULL;
		$lat = NULL;

		if ($xml = Gmap::address_to_xml($address))
		{
			// Get the latitude and longitude from the Google Maps XML
			list ($lon, $lat) = explode(',', $xml->Response->Placemark->Point->coordinates);
		}

		return array($lon, $lat);
	}

	/**
	 * Retrieves the XML geocode address lookup.
	 * ! Results of this method are cached for 1 day.
	 *
	 * @param   string  adress
	 * @return  object  SimpleXML
	 */
	public static function address_to_xml($address)
	{
		static $cache;

		// Load Cache
		($cache === NULL) and $cache = Cache::instance();

		// Address cache key
		$key = 'gmap-address-'.sha1($address);

		if ($xml = $cache->get($key))
		{
			// Return the cached XML
			return simplexml_load_string($xml);
		}
		else
		{
			// Get the API key
			$api_key = Config::item('gmaps.api_key');

			// Send the address URL encoded
			$addresss = rawurlencode($address);

			// Disable error reporting while fetching the feed
			$ER = error_reporting(0);

			// Load the XML
			$xml = simplexml_load_file
			(
				'http://maps.google.com/maps/geo?'.
				'&output=xml'.
				'&key='.$api_key.
				'&q='.rawurlencode($address)
			);

			if (is_object($xml) AND ($xml instanceof SimpleXMLElement) AND (int) $xml->Response->Status->code === 200)
			{
				// Cache the XML
				$cache->set($key, $xml->asXML(), array('gmaps'), 86400);
			}
			else
			{
				// Invalid XML response
				$xml = FALSE;
			}

			// Turn error reporting back on
			error_reporting($ER);
		}

		return $xml;
	}

	public function __construct($config = NULL)
	{
		empty($config) and $config = Config::item('gmaps');
	}

	public function add_marker($value='')
	{
		# code...
	}

} // End Gmap