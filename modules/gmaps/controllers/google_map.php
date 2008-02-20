<?php defined('SYSPATH') or die('No direct script access.');

class Google_Map_Controller extends Controller {

	public function index()
	{
		View::factory('gmaps/api_demo')->render(TRUE);
	}

	public function markers()
	{
		$map = new Gmap;
		
		$markers[] = Gmap::marker('South Africa');
		$markers[] = Gmap::marker('8601 Edinbrook Crossing, 55443');

		foreach ($markers as $marker)
		{
			echo html::anchor
			(
				'http://maps.google.com/?&q='
				.rawurlencode($marker->address).'&ll='
				.rawurlencode($marker->lon.','.$marker->lat)
				.'&z=10&iwloc=addr'
			).'<br/>';
		}

	}

	public function georss()
	{
		View::factory('gmaps/rss_demo')->render(TRUE);
	}

	public function feed()
	{
		header('Content-Type: text/xml; charset=utf-8');
		require Kohana::find_file('views', 'gmaps/rss', FALSE, '.xml');
	}

} // End Google Map Controller