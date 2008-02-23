<?php defined('SYSPATH') or die('No direct script access.');

class Gmap_Marker_Core {

	// Marker HTML
	public $html;

	// Latitude and longitude
	public $latitude;
	public $longitude;

	/**
	 * Create a new GMap marker.
	 *
	 * @param   float   latitude
	 * @param   float   longitude
	 * @param   string  HTML of info window
	 * @return  void
	 */
	public function __construct($lat, $lon, $html)
	{
		if ( ! is_numeric($lat) OR ! is_numeric($lon))
			throw new Kohana_Exception('gmaps.invalid_marker', $lat, $lon);

		// Set the latitude and longitude
		$this->latitude = $lat;
		$this->longitude = $lon;

		// Set the info window HTML
		$this->html = $html;
	}

	public function render($tabs = 0)
	{
		// Create the tabs
		$tabs = empty($tabs) ? '' : str_repeat("\t", $tabs);

		$output = array();
		$output[] = 'var m = new GMarker(new GLatLng('.$this->latitude.', '.$this->longitude.'));';
		if ($html = $this->html)
		{
			$output[] = 'GEvent.addListener(m, "click", function()';
			$output[] = '{';
			$output[] = "\t".'m.openInfoWindowHtml(';
			$output[] = "\t\t'".implode("'+\n\t\t$tabs'", explode("\n", $html))."'";
			$output[] = "\t);";
			$output[] = '});';
		}
		$output[] = 'map.addOverlay(m);';

		return implode("\n".$tabs, $output);
	}

} // End Gmap Marker