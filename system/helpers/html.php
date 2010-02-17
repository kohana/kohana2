<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The HTML helper assists in creating various html elements such as anchors
 * stylesheets, javascript and images.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class html_Core {

	/**
	 * Enable or disable automatic setting of target="_blank"
	 * @var bool
	 */
	public static $windowed_urls = FALSE;

	/**
	 * Wrapper for htmlspecialchars() that uses the default Kohana charset and
	 * will encode both double and single quotes (using the ENT_QUOTES flag).
	 *
	 * ##### Example
	 *
	 *     echo html::chars('<p>"I\'m hungry"&mdash;Cookie Monster said.</p>');
	 *
	 *     // Output:
	 *     &lt;p&gt;&quot;I&#039;m hungry&quot;&amp;mdash;Cookie Monster said.&lt;/p&gt;
	 *
	 * @param   string   $str            String to encode
	 * @param   boolean  $double_encode  Encode existing entities
	 * @return  string
	 */
	public static function chars($str, $double_encode = TRUE)
	{
		// Return HTML entities using the Kohana charset
		return htmlspecialchars($str, ENT_QUOTES, Kohana::CHARSET, $double_encode);
	}

	/**
	 * Creates absolute anchor links. This function automatically detects
	 * internal page links and makes them absolute using [url::site]. To
	 * remove `index.php` from your links see [config::base_url].
	 *
	 * [!!] By default the anchor title will *not* be escaped
	 *
	 * ##### Example
	 *
	 * Internal Link
	 *
	 *     echo html::anchor('home/news', 'Go to our news section!');
	 *
	 *     // Output:
	 *     <a href="http://localhost/index.php/home/news">Go to our news section!</a>
	 *
	 * External Link
	 *
	 *     echo html::anchor('irc://irc.freenode.net/kohana', 'Join us on IRC!', array('style'=>'font-size: 20px;'));
	 *
	 *     // Output:
	 *     <a href="irc://irc.freenode.net/kohana" style="font-size: 20px;">Join us on IRC!</a>
	 *
	 * @param   string  $uri           URL or URI string
	 * @param   string  $title         Link text
	 * @param   array   $attributes    HTML anchor attributes
	 * @param   string  $protocol      Non-default protocol, eg: https
	 * @param   boolean $escape_title  Option to escape the title that is output
	 * @return  string
	 */
	public static function anchor($uri, $title = NULL, $attributes = NULL, $protocol = NULL, $escape_title = FALSE)
	{
		if ($uri === '')
		{
			$site_url = url::base(FALSE);
		}
		elseif (strpos($uri, '#') === 0)
		{
			// This is an id target link, not a URL
			$site_url = $uri;
		}
		elseif (strpos($uri, '://') === FALSE)
		{
			$site_url = url::site($uri, $protocol);
		}
		else
		{
			if (html::$windowed_urls === TRUE AND empty($attributes['target']))
			{
				$attributes['target'] = '_blank';
			}

			$site_url = $uri;
		}

		return
		// Parsed URL
		'<a href="'.htmlspecialchars($site_url, ENT_QUOTES, Kohana::CHARSET, FALSE).'"'
		// Attributes empty? Use an empty string
		.(is_array($attributes) ? html::attributes($attributes) : '').'>'
		// Title empty? Use the parsed URL
		.($escape_title ? htmlspecialchars((($title === NULL) ? $site_url : $title), ENT_QUOTES, Kohana::CHARSET, FALSE) : (($title === NULL) ? $site_url : $title)).'</a>';
	}

	/**
	 * Creates links to non-Kohana resources. This function works the same as
	 * [html::anchor], except it uses [url::base] instead of [url::site]
	 *
	 * [!!] The anchor title will not be escaped
	 *
	 * ##### Example
	 *
	 *     echo html::file_anchor('media/files/2007-12-magazine.pdf', 'Check out our latest magazine!');
	 *
	 *     // Output:
	 *     <a href="http://localhost/media/files/2007-12-magazine.pdf">Check out our latest magazine!</a>
	 *
	 * @param   string  $file         Name of file to link to
	 * @param   string  $title        Link text
	 * @param   array   $attributes   HTML anchor attributes
	 * @param   string  $protocol     Non-default protocol, eg: ftp
	 * @return  string
	 */
	public static function file_anchor($file, $title = NULL, $attributes = NULL, $protocol = NULL)
	{
		return
		// Base URL + URI = full URL
		'<a href="'.htmlspecialchars(url::base(FALSE, $protocol).$file, ENT_QUOTES, Kohana::CHARSET, FALSE).'"'
		// Attributes empty? Use an empty string
		.(is_array($attributes) ? html::attributes($attributes) : '').'>'
		// Title empty? Use the filename part of the URI
		.(($title === NULL) ? end(explode('/', $file)) : $title) .'</a>';
	}

	/**
	 * Generates an obfuscated version of an email address. It escapes all
	 * characters of the e-mail address into HTML, hex or raw randomly to
	 * help prevent spam and e-mail harvesting.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(html::email('test@mydomain.com'));
	 *
	 *     // Output:
	 *     (string) t&#101;&#x73;&#116;&#x40;m&#121;&#x64;o&#109;&#x61;&#105;n&#46;&#x63;o&#109;
	 *
	 * @param   string  $email   Email address
	 * @return  string
	 */
	public static function email($email)
	{
		$safe = '';
		foreach (str_split($email) as $letter)
		{
			switch (($letter === '@') ? rand(1, 2) : rand(1, 3))
			{
				// HTML entity code
				case 1: $safe .= '&#'.ord($letter).';'; break;
				// Hex character code
				case 2: $safe .= '&#x'.dechex(ord($letter)).';'; break;
				// Raw (no) encoding
				case 3: $safe .= $letter;
			}
		}

		return $safe;
	}

	/**
	 * Creates an email anchor and obfuscated the email address using [html::email].
	 *
	 * ##### Example
	 *
	 *     echo html::mailto('info@example.com');
	 *
	 *     // Output (note the output has been truncated for display purposes):
	 *     <a href="&#109;&#097;&#105;&#108;&#116;...">&#109;&#097;&#105;&#108;&#116;...</a>
	 *
	 * @param   string  $email       Email address to send to
	 * @param   string  $title       Link text
	 * @param   array   $attributes  HTML anchor attributes
	 * @return  string
	 */
	public static function mailto($email, $title = NULL, $attributes = NULL)
	{
		if (empty($email))
			return $title;

		// Remove the subject or other parameters that do not need to be encoded
		if (strpos($email, '?') !== FALSE)
		{
			// Extract the parameters from the email address
			list ($email, $params) = explode('?', $email, 2);

			// Make the params into a query string, replacing spaces
			$params = '?'.str_replace(' ', '%20', $params);
		}
		else
		{
			// No parameters
			$params = '';
		}

		// Obfuscate email address
		$safe = html::email($email);

		// Title defaults to the encoded email address
		empty($title) and $title = $safe;

		// Parse attributes
		empty($attributes) or $attributes = html::attributes($attributes);

		// Encoded start of the href="" is a static encoded version of 'mailto:'
		return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$safe.$params.'"'.$attributes.'>'.$title.'</a>';
	}

	/**
	 * Generate a "breadcrumb" list of anchors representing the URI. Uses
	 * `Router::$segments` if no segment argument is provided.
	 *
	 * ##### Example
	 *
	 *     echo Kohana::debug(html::breadcrumb())
	 *
	 *     // Output (current uri is /kohana/index.php/userguide/index):
	 *     (array) Array
	 *     (
	 *         [0] => <a href="/kohana/index.php/userguide">Userguide</a>
	 *         [1] => <a href="/kohana/index.php/userguide/index">Index</a>
	 *     )
	 *
	 * @param   array   $segments  Segments to use as breadcrumbs
	 * @return  string
	 */
	public static function breadcrumb($segments = NULL)
	{
		$segments = empty($segments) ? Router::$segments : $segments;

		$array = array();
		while ($segment = array_pop($segments))
		{
			$array[] = html::anchor
			(
				// Complete URI for the URL
				implode('/', $segments).'/'.$segment,
				// Title for the current segment
				ucwords(inflector::humanize($segment))
			);
		}

		// Retrun the array of all the segments
		return array_reverse($array);
	}

	/**
	 * Creates a meta tag. This function will automatically detect when a tag
	 * should use http-equiv or name.
	 *
	 * ##### Example
	 *
	 *     echo html::meta(array('generator' => 'Kohana 2.4', 'robots' => 'noindex,nofollow'));
	 *
	 *     // Output:
	 *     <meta name="generator" content="Kohana 2.4" />
	 *     <meta name="robots" content="noindex,nofollow" />
	 *
	 * @param   mixed  $tag    Tag name, or an array of tags
	 * @param   string $value  Tag "content" value
	 * @return  string
	 */
	public static function meta($tag, $value = NULL)
	{
		if (is_array($tag))
		{
			$tags = array();
			foreach ($tag as $t => $v)
			{
				// Build each tag and add it to the array
				$tags[] = html::meta($t, $v);
			}

			// Return all of the tags as a string
			return implode("\n", $tags);
		}

		// Set the meta attribute value
		$attr = in_array(strtolower($tag), Kohana::config('http.meta_equiv')) ? 'http-equiv' : 'name';

		return '<meta '.$attr.'="'.$tag.'" content="'.$value.'" />';
	}

	/**
	 * Creates a stylesheet link using [html::link].
	 *
	 * ##### Example
	 *
	 *     echo html::stylesheet(array
	 *     (
	 *        'media/css/site.css',
	 *        'http://assets.example.com/css/jquery_ui.css',
	 *        'media/css/reset-fonts-grids.css'
	 *     ),
	 *     array
	 *     (
	 *        'screen',
	 *        'screen',
	 *        'print'
	 *     ));
	 *
	 *     // Output:
	 *     <link rel="stylesheet" type="text/css" href="/kohana/media/css/site.css" media="screen" />
	 *     <link rel="stylesheet" type="text/css" href="http://assets.example.com/css/jquery_ui.css" media="screen" />
	 *     <link rel="stylesheet" type="text/css" href="/kohana/media/css/reset-fonts-grids.css" media="print" />
	 *
	 * @param   mixed    $style   Filename, or array of filenames to match to array of medias
	 * @param   mixed    $media   Media type of stylesheet, or array to match filenames
	 * @param   boolean  $index   Include the index_page in the link
	 * @return  string
	 */
	public static function stylesheet($style, $media = FALSE, $index = FALSE)
	{
		return html::link($style, 'stylesheet', 'text/css', $media, $index);
	}

	/**
	 * Creates a link tag. This function automatically detects relative links
	 * and makes them absolute using [url::base]. If you need to route the
	 * request through Kohana make sure you set the `$index` argument to `TRUE`.
	 *
	 * ##### Example
	 *
	 *     echo html::link(array
	 *     (
	 *         'welcome/home/rss',
	 *         'welcome/home/atom'
	 *     ),
	 *     'alternate',
	 *     array('application/rss+xml','application/atom+xml')
	 *     );
	 *
	 *     // Output:
	 *     <link rel="alternate" type="application/rss+xml" href="/kohana/welcome/home/rss" />
	 *     <link rel="alternate" type="application/atom+xml" href="/kohana/welcome/home/atom" />
	 *
	 * @param   mixed     $href    Filename
	 * @param   mixed     $rel     Relationship
	 * @param   mixed     $type    Mimetype
	 * @param   mixed     $media   Specifies on what device the document will be displayed
	 * @param   boolean   $index   Include the index_page in the link
	 * @return  string
	 */
	public static function link($href, $rel, $type, $media = FALSE, $index = FALSE)
	{
		$compiled = '';

		if (is_array($href))
		{
			foreach ($href as $_href)
			{
				$_rel   = is_array($rel) ? array_shift($rel) : $rel;
				$_type  = is_array($type) ? array_shift($type) : $type;
				$_media = is_array($media) ? array_shift($media) : $media;

				$compiled .= html::link($_href, $_rel, $_type, $_media, $index);
			}
		}
		else
		{
			if (strpos($href, '://') === FALSE)
			{
				// Make the URL absolute
				$href = url::base($index).$href;
			}

			$attr = array
			(
				'rel' => $rel,
				'type' => $type,
				'href' => $href,
			);

			if ( ! empty($media))
			{
				// Add the media type to the attributes
				$attr['media'] = $media;
			}

			$compiled = '<link'.html::attributes($attr).' />';
		}

		return $compiled."\n";
	}

	/**
	 * Creates a script link. This function automatically detects relative links
	 * and makes them absolute using [url::base]. If you need to route the
	 * request through Kohana make sure you set the `$index` argument to `TRUE`
	 *
	 * ##### Example
	 *
	 *     echo html::script(array
	 *     (
	 *         'media/js/login',
	 *         'media/js/iefixes.js',
	 *         'http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js'
	 *     ));
	 *
	 *     // Output:
	 *     <script type="text/javascript" src="/kohana/media/js/login"></script>
	 *     <script type="text/javascript" src="/kohana/media/js/iefixes.js"></script>
	 *     <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	 *
	 * @param   mixed    $script  Filename
	 * @param   boolean  $index   Include the index_page in the link
	 * @return  string
	 */
	public static function script($script, $index = FALSE)
	{
		$compiled = '';

		if (is_array($script))
		{
			foreach ($script as $name)
			{
				$compiled .= html::script($name, $index);
			}
		}
		else
		{
			if (strpos($script, '://') === FALSE)
			{
				// Add the suffix only when it's not already present
				$script = url::base((bool) $index).$script;
			}

			$compiled = '<script type="text/javascript" src="'.$script.'"></script>';
		}

		return $compiled."\n";
	}

	/**
	 * Creates a image link. This function automatically detects relative links
	 * and makes them absolute using [url::base]. If you need to route the
	 * request through Kohana make sure you set the `$index` argument to `TRUE`
	 *
	 * ##### Examples
	 * ###### Basic Example
	 *
	 *     echo html::image('media/images/thumbs/01.png', 'Thumbnail');
	 *
	 *     // Output:
	 *     <img src="/kohana/media/images/thumbs/01.png" alt="Thumbnail" />
	 *
	 * ###### Advanced Example
	 *
	 *     echo html::image(array('src' => 'media/images/thumbs/01.png', 'width' => '100', 'height' => 100), array('alt' => 'Thumbnail', 'class' => 'noborder'));
	 *
	 *     // Output:
	 *     <img src="/kohana/media/images/thumbs/01.png" width="100" height="100" alt="Thumbnail" class="noborder" />
	 *
	 * @param   mixed    $src    Image source, or an array of attributes
	 * @param   mixed    $alt    Image alt attribute, or an array of attributes
	 * @param   boolean  $index  Include the index_page in the link
	 * @return  string
	 */
	public static function image($src, $alt = NULL, $index = FALSE)
	{
		// Create attribute list
		$attributes = is_array($src) ? $src : array('src' => $src);

		if (is_array($alt))
		{
			$attributes += $alt;
		}
		elseif ( ! empty($alt))
		{
			// Add alt to attributes
			$attributes['alt'] = $alt;
		}

		if (strpos($attributes['src'], '://') === FALSE)
		{
			// Make the src attribute into an absolute URL
			$attributes['src'] = url::base($index).$attributes['src'];
		}

		return '<img'.html::attributes($attributes).' />';
	}

	/**
	 * Compiles an array of HTML attributes into an attribute string.
	 *
	 * [!!] This function will automatically escape all attribute values using `htmlspecialchars()`
	 *
	 * ##### Examples
	 *
	 *     echo echo html::attributes(
	 *     array
	 *     (
	 *     	'style' => 'font-size: 20px; border-bottom: 1px solid #000;',
	 *     	'rel' => 'lightbox',
	 *     	'class' => 'image'
	 *     )
	 *     );
	 *
	 *     // Output:
	 *     style="font-size: 20px; border-bottom: 1px solid #000;" rel="lightbox" class="image"
	 *
	 * @param   mixed  $attrs  Array of attributes
	 * @return  string
	 */
	public static function attributes($attrs)
	{
		if (empty($attrs))
			return '';

		if (is_string($attrs))
			return ' '.$attrs;

		$compiled = '';
		foreach ($attrs as $key => $val)
		{
			$compiled .= ' '.$key.'="'.htmlspecialchars($val, ENT_QUOTES, Kohana::CHARSET).'"';
		}

		return $compiled;
	}

} // End html
