<?php

class url {

	public static function site_url($uri)
	{
		$uri = trim($uri, '/');

		$base_url   = rtrim(Config::item('base_url'), '/');
		$index_page = Config::item('index_page').'/';
		$url_suffix = Config::item('url_suffix');

		return $base_url.$index_page.$uri.$url_suffix;
	}

	public static function title($title, $separator = 'dash')
	{
		$separator = ($separator == 'dash') ? 'dash' : 'underscore';
		
		// Remove all dashes, underscores, and whitespace
		$title = preg_replace('/[-_\s]+/', $separator, $title);
		// Remove all characters that are not a-z, 9-9, or the separator
		$title = preg_replace('/[^a-zA-Z0-9'.$separator.']/', '', $title);
		
		return $title;
	}

	public static function anchor($uri, $title = FALSE, $attributes = FALSE)
	{
		if ( ! is_array($uri))
		{
			$site_url = (strpos($uri, '://') === FALSE) ? self::site_url($uri) : $uri;
		}
		else
		{
			$site_url = self::site_url($uri);
		}

		if ($title == '')
		{
			$title = $site_url;
		}

		$attributes = ($attributes == TRUE) ? Kohana::attributes($attributes) : '';

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}

	public static function redirect($uri = '', $method = '302')
	{
		if (strpos($uri, '://') === FALSE)
		{
			$uri = site_url($uri);
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



}
