<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gmaps module demo controller.
 *
 * $Id$
 *
 * @package    Gmaps
 * @author     Woody Gilk
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Google_Map_Controller extends Controller {

	public function index()
	{
		$this->db = Database::instance('mysql://root:r00tdb@localhost/azmap');

		// Create a new Gmap
		$map = new Gmap('map');

		// Set the map center point
		$map->center(0, 0, 1)->controls('large');

		// Add a new marker
		$map->add_marker(44.9801, -93.2519, '<strong>Minneapolis, MN</strong><p>Hello world!</p>');

		View::factory('gmaps/api_demo')->set('map', $map->render())->render(TRUE);
	}

	public function azmap()
	{
		$this->db = Database::instance('mysql://root:r00tdb@localhost/azmap');

		// Create a new Gmap
		$map = new Gmap('map', array
		(
			'ScrollWheelZoom' => TRUE,
		));

		// Set the map center point
		$map->center(0, 35, 2)->controls('large');

		// Set marker locations
		foreach (ORM::factory('location')->find_all() as $location)
		{
			// Add a new marker
			$map->add_marker($location->lat, $location->lon,
				// Get the info window HTML
				View::factory('gmaps/info_window')->bind('location', $location)->render());
		}

		header('Content-type: text/javascript');
		echo $map->render();
	}

	public function jquery()
	{
		View::factory('gmaps/jquery')->render(TRUE);
	}

	public function xml()
	{
		$this->db = Database::instance('mysql://root:r00tdb@localhost/azmap');

		// Get all locations
		$locations = ORM::factory('location')->find_all();

		// Create the XML container
		$xml = new SimpleXMLElement('<gmap></gmap>');

		foreach ($locations as $location)
		{
			// Create a new mark
			$node = $xml->addChild('marker');

			// Set the latitutde and longitude
			$node->addAttribute('lon', $location->lon);
			$node->addAttribute('lat', $location->lat);

			$node->html = View::factory('gmaps/xml')->bind('location', $location)->render();

			foreach ($location->as_array() as $key => $val)
			{
				// Skip the ID
				if ($key === 'id') continue;

				// Add the data to the XML
				$node->$key = $val;
			}
		}

		header('Content-Type: text/xml');
		echo $xml->asXML();
	}

} // End Google Map Controller