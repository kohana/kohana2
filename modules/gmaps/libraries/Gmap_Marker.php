<?php defined('SYSPATH') or die('No direct script access.');

class Gmap_Marker_Core {

	// Marker HTML
	public $html;

	// Address
	public $address;

	// Latitude and longitude
	public $latitude;
	public $longitude;

	public function __construct($lon, $lat)
	{
		if ( ! is_numeric($lat) OR ! is_numeric($lon))
			throw new Kohana_Exception('gmaps.invalid_marker', $lat, $lon);

		//http://maps.google.com/?q=South+Africa&ie=UTF8&ll=-32.175612,21.42334&spn=5.503756,11.260986&t=h&z=7

		$this->longitude = ($lon < 0) ? min(0, max($lon, -90))  : min(90, max($lon, 0));
		$this->latitude  = ($lat < 0) ? min(0, max($lat, -180)) : min(180, max($lat, 0));
	}

	public function __get($key)
	{
		if ($key === 'lat' OR $key === 'latitude')
		{
			return $this->latitude;
		}
		elseif ($key === 'lon' OR $key === 'longitude')
		{
			return $this->longitude;
		}

		return NULL;
	}

	public function __set($key, $value)
	{
		
	}

} // End Gmap Marker