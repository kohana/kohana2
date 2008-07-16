<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Google Maps API integration.
 *
 * $Id$
 *
 * @package    Gmaps
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Gmap_Core {

	/**
	 * Retrieves the latitude and longitude of an address.
	 *
	 * @param   string  address
	 * @return  array   longitude, latitude
	 */
	public static function address_to_ll($address)
	{
		$lat = NULL;
		$lon = NULL;

		if ($xml = Gmap::address_to_xml($address))
		{
			// Get the latitude and longitude from the Google Maps XML
			// NOTE: the order (lon, lat) is the correct order
			list ($lon, $lat) = explode(',', $xml->Response->Placemark->Point->coordinates);
		}

		return array($lat, $lon);
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
			$ER = error_reporting(~E_NOTICE);

			// Load the XML
			$xml = simplexml_load_file
			(
				'http://maps.google.com/maps/geo?'.
				'&output=xml'.
				'&oe=utf-8'.
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

	// Map settings
	protected $id;
	protected $options;
	protected $center;
	protected $control;

	// Map markers
	protected $markers;

	/**
	 * Set the GMap center point.
	 *
	 * @param   string  HTML map id attribute
	 * @param   array   array of GMap constructor options
	 * @return  void
	 */
	public function __construct($id = 'map', $options = NULL)
	{
		// Set map ID and options
		$this->id = $id;
		$this->options = new Gmap_Options((array) $options);
	}

	/**
	 * Set the GMap center point.
	 *
	 * @chainable
	 * @param   float    latitude
	 * @param   float    longitude
	 * @param   integer  zoom level (1-16)
	 * @return  object
	 */
	public function center($lat, $lon, $zoom = 6)
	{
		// Set center location and zoom
		$this->center = array($lat, $lon, $zoom);

		return $this;
	}

	/**
	 * Set the GMap controls size.
	 *
	 * @chainable
	 * @param   string   small or large
	 * @return  object
	 */
	public function controls($size = NULL)
	{
		// Set the control type
		$this->controls = (strtolower($size) === 'small') ? 'Small' : 'Large';

		return $this;
	}

	/**
	 * Set the GMap marker point.
	 *
	 * @chainable
	 * @param   float   latitude
	 * @param   float   longitude
	 * @param   string  HTML for info window
	 * @return  object
	 */
	public function add_marker($lat, $lon, $html = '')
	{
		// Add a new marker
		$this->markers[] = new Gmap_Marker($lat, $lon, $html);

		return $this;
	}

	/**
	 * Render the map into GMap Javascript.
	 *
	 * @return  string
	 */
	public function render()
	{
		// Latitude, longitude, and zoom
		list ($lat, $lon, $zoom) = $this->center;

		// Map
		$map = 'var map = new GMap2(document.getElementById("'.$this->id.'"));';

		// Map controls
		$controls = empty($this->controls) ? '' : 'map.addControl(new G'.$this->controls.'MapControl());';

		// Map centering
		$center = 'map.setCenter(new GLatLng('.$lat.', '.$lon.'));';

		// Map zoom
		$zoom = 'map.setZoom('.$zoom.');';

		// Render the Javascript
		return View::factory('gmaps/javascript', array
			(
				'map' => $map,
				'options' => $this->options,
				'controls' => $controls,
				'center' => $center,
				'zoom' => $zoom,
				'markers' => $this->markers,
			))
			->render();
	}

} // End Gmap