<?php defined('SYSPATH') or die('No direct access allowed.');

class url {

	public static function base($index = FALSE)
	{
		$base_url = Config::item('core.base_url', TRUE);

		if ($index == TRUE AND $index = Config::item('core.index_page'))
		{
			$base_url = $base_url.$index.'/';
		}

		return $base_url;
	}

	public static function site($uri)
	{
		$uri = trim($uri, '/');

		$index_page = Config::item('core.index_page', TRUE);
		$url_suffix = Config::item('core.url_suffix');

		return self::base().$index_page.$uri.$url_suffix;
	}

	public static function title($title, $separator = 'dash')
	{
		$separator = ($separator == 'dash') ? '-' : '_';

		// Replace all dashes, underscores and whitespace by the separator
		$title = preg_replace('/[-_\s]+/', $separator, $title);
		// Replace accented characters by their unaccented equivalents
		$title = utf8::accents_to_ascii($title);
		// Convert to lowercase
		$title = strtolower($title);
		// Remove all characters that are not a-z, 0-9, or the separator
		$title = preg_replace('/[^a-z0-9'.$separator.']+/', '', $title);
		// Trim separators from the beginning and end
		$title = trim($title, $separator);

		return $title;
	}

	public static function redirect($uri = '', $method = '302')
	{
		if (strpos($uri, '://') === FALSE)
		{
			$uri = self::site($uri);
		}

		if ($method == 'refresh')
		{
			header('Refresh: 0; url='. $uri);
		}
		else
		{
			$codes = array(
				'300' => 'Multiple Choices',
				'301' => 'Moved Permanently',
				'302' => 'Found',
				'303' => 'See Other',
				'304' => 'Not Modified',
				'305' => 'Use Proxy',
				'307' => 'Temporary Redirect'
			);

			$method = (isset($codes[$method])) ? $method : '302';

			header('HTTP/1.1 '.$method.' '.$codes[$method]);
			header('Location: '.$uri);
		}

		/**
		 * @todo localize this
		 */
		exit('You should have been redirected to <a href="'.$uri.'">'.$uri.'</a>.');
	}

} // End url class