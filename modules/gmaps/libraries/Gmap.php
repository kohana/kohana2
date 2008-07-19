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
	 * @param string $address address
	 * @return array longitude, latitude
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
	 * @param string $address adress
	 * @return object SimpleXML
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
			// Disable error reporting while fetching the feed
			$ER = error_reporting(~E_NOTICE);

			// Load the XML
			$xml = simplexml_load_file
			(
				'http://maps.google.com/maps/geo?'.
				'&output=xml'.
				'&oe=utf-8'.
				'&key='.Config::item('gmaps.api_key'). // Get the API key
				'&q='.rawurlencode($address) // Send the address URL encoded
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
	
	/**
	 * Returns an image map
	 *
	 * @param mixed $lat latitude or an array of marker points
	 * @param float $lon longitude
	 * @param integer $zoom zoom level (1-16)
	 * @param string $type map type (roadmap or mobile)
	 * @param integer $width map width
	 * @param integer $height map height
	 * @return string
	 */
	public static function static_map($lat = 0, $lon = 0, $zoom = 6, $type = 'roadmap', $width = 300, $height = 300)
	{
		$api_url = 'http://maps.google.com/staticmap?key='.Config::item('gmaps.api_key');

		$types = array('roadmap', 'mobile');

		$width = min(640, (int) $width);
        $height = min(640, (int) $height);

		if ($width <= 0 OR $height <= 0)
			throw new Kohana_Exception('gmaps.invalid_dimensions', $width, $height);

		$api_url = $api_url.'&amp;size='.$width.'x'.$height;
		
		if (in_array($type, $types))
            $api_url = $api_url.'&amp;maptype='.$type;
 		
		if (is_array($lat))
		{
			foreach ($lat as $key => $value)
				$markers[] = $key.','.$value;

			$api_url = $api_url.'&amp;markers='.implode('|', $markers);
		}
		else
		{
			$api_url = $api_url.'&amp;center='.$lat.','.$lon.'&amp;zoom='.$zoom;
		}

        return $api_url;
	}

	// Map settings
	protected $id;
	protected $options;
	protected $center;
	protected $control;
	protected $type_control = FALSE;
	
	// Map types
	protected $types = array();

	// Map markers
	protected $markers = array();

	/**
	 * Set the GMap center point.
	 *
	 * @param string $id HTML map id attribute
	 * @param array $options array of GMap constructor options
	 * @return void
	 */
	public function __construct($id = 'map', $options = NULL)
	{
		// Set map ID and options
		$this->id = $id;
		$this->options = new Gmap_Options((array) $options);
	}
	
	/**
	 * Return GMap javascript url
	 * 
	 * @return string
	 */
	 public function api_uri()
	 {
	    return 'http://www.google.com/jsapi?key='.Config::item('gmaps.api_key').'&amp;oe=utf-8';
	 }

	/**
	 * Set the GMap center point.
	 *
	 * @chainable
	 * @param float $lat latitude
	 * @param float $lon longitude
	 * @param integer $zoom zoom level (1-16)
	 * @return object
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
	 * @param string $size small or large
	 * @return object
	 */
	public function controls($size = NULL)
	{
		// Set the control type
		$this->control = (strtolower($size) === 'small') ? 'Small' : 'Large';

		return $this;
	}
	
	/**
	 * Set the GMap type controls.
	 * by default renders G_NORMAL_MAP, G_SATELLITE_MAP, and G_HYBRID_MAP
	 *
	 * @chainable
	 * @param string $type map type
	 * @param string $action add or remove map type
	 * @return object
	 */
	public function types($type = NULL, $action = 'remove')
	{
		$this->type_control = TRUE;
		
		$types = array
		(
			'G_NORMAL_MAP','G_SATELLITE_MAP','G_HYBRID_MAP','G_PHYSICAL_MAP'
		);
		
		if ($type !== NULL and in_array($type, $types, true))
		{
			// Set the map type and action
			$this->types[$type] = (strtolower($action) === 'remove') ? 'remove' : 'add';
		}
		
		return $this;
	}

	/**
	 * Set the GMap marker point.
	 *
	 * @chainable
	 * @param float $lat latitude
	 * @param float $lon longitude
	 * @param string $html HTML for info window
	 * @return object
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
	 * @param string $template template name
	 * @return string
	 */
	public function render($template = 'gmaps/javascript')
	{
		// Latitude, longitude, and zoom
		list ($lat, $lon, $zoom) = $this->center;

		// Map
		$map = 'var map = new google.maps.Map2(document.getElementById("'.$this->id.'"));';
		
		// Map controls
		$controls = empty($this->control) ? '' : 'map.addControl(new google.maps.'.$this->control.'MapControl());';

		// Map Types
		if ($this->type_control === TRUE)
		{
			if (count($this->types) > 0) 
			{
				foreach($this->types as $type => $action)
					$controls .= 'map.'.$action.'MapType('.$type.');';
			}
			
			$controls .= 'map.addControl(new google.maps.MapTypeControl());';
		}

		// Map centering
		$center = 'map.setCenter(new google.maps.LatLng('.$lat.', '.$lon.'), '.$zoom.');';

		// Render the Javascript
		return View::factory($template, array
			(
				'map' => $map,
				'options' => $this->options,
				'controls' => $controls,
				'center' => $center,
				'markers' => $this->markers,
			))
			->render();
	}

} // End Gmap