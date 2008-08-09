<h2><span>&copy; 2007, Geert De Deckere</span>Setting Up a Multilingual Website</h2>

<p>You want to create a multilingual site. Each translation should be available via a <abbr title="Uniform Resource Locator">URL</abbr> with a language code of two characters in its first segment, like this: <tt>example.com/en/page</tt>, <tt>example.com/nl/page</tt>, etc. This may seem a daunting task. However, this tutorial is here to show you how to tackle this situation using the power and flexibility of Kohana, in just four steps.</p>


<h3>1. Force language in every URL via .htaccess</h3>

<p>Start by creating a .htaccess file in the document root of your website.</p>

<?php

echo geshi_highlight('RewriteEngine On
RewriteBase /

# Force EVERY URL to contain a language in its first segment.
# Redirect URLs without a language to the invalid xx language.
RewriteCond $2 !^([a-z]{2}(/|$)) [NC]
RewriteRule ^(index\.php/?)?(.*)$ xx/$2 [R=301,L]

# Silently prepend index.php to EVERY URL.
RewriteCond $1 !^(index\.php)
RewriteRule ^(.*)$ index.php/$1 [L]
', 'apache', NULL, TRUE)

?>

<p>At this point you know that every URL that gets to your Kohana app will have a language as its first segment. No exceptions. If a URL has no language in it (e.g. <tt>example.com/page</tt>), it will be redirected to a temporary language placeholder (e.g. <tt>example.com/xx/page</tt>), which we take care of in step two.</p>


<h3>2. Dynamically set locale config via a hook</h3>

<p>The core of the language system is a hook that I call site_lang. Note that you need to <strong>enable hooks</strong> via <tt>applications/config/hooks.php</tt>. Also, you will need to <strong>set <tt>allow_config_set</tt> to TRUE</strong> since the hook needs to set config items at runtime. You find that option in <tt>application/config/config.php</tt>.</p>

<p>Basically what this hook does, is look at the language key found in the URL and set the locale config values according to it. If the language key in the URL is invalid (e.g. 'xx'), it will automatically look for the most appropriate alternative, taking into account (in order of precedence): a possible language cookie set on a previous visit, the HTTP_ACCEPT_LANGUAGE request header and the default language chosen by you. Finally, you will be redirected to the corrected URL.</p>

<p><tt>application/hooks/site_lang.php</tt></p>
<?php

echo geshi_highlight('<?php

// This hook sets the locale.language and locale.lang config values
// based on the language found in the first segment of the URL.

Event::add(\'system.routing\', \'site_lang\');

function site_lang()
{
	// Array of allowed languages
	$locales = Kohana::config(\'locale.allowed_locales\');

	// Extract language from URL
	$lang = strtolower(substr(url::current(), 0, 2));

	// Invalid language is given in the URL
	if ( ! array_key_exists($lang, $locales))
	{
		// Look for default alternatives and store them in order
		// of importance in the $new_langs array:
		//  1. cookie
		//  2. http_accept_language header
		//  3. default lang

		// Look for cookie
		$new_langs[] = (string) cookie::get(\'lang\');

		// Look for HTTP_ACCEPT_LANGUAGE
		if (isset($_SERVER[\'HTTP_ACCEPT_LANGUAGE\']))
		{
			foreach(explode(\',\', $_SERVER[\'HTTP_ACCEPT_LANGUAGE\']) as $part)
			{
				$new_langs[] = substr($part, 0, 2);
			}
		}

		// Lowest priority goes to default language
		$new_langs[] = \'nl\';

		// Now loop through the new languages and pick out the first valid one
		foreach(array_unique($new_langs) as $new_lang)
		{
			$new_lang = strtolower($new_lang);

			if (array_key_exists($new_lang, $locales))
			{
				$lang = $new_lang;
				break;
			}
		}

		// Redirect to URL with valid language
		url::redirect($lang.substr(url::current(), 2));
	}

	// Store locale config values
	Kohana::config_set(\'locale.lang\', $lang);
	Kohana::config_set(\'locale.language\', $locales[$lang]);

	// Overwrite setlocale which has already been set before in Kohana::setup()
	setlocale(LC_ALL, Kohana::config(\'locale.language\').\'.UTF-8\');

	// Finally set a language cookie for 6 months
	cookie::set(\'lang\', $lang, 15768000);
}
', 'php', NULL, TRUE)

?>

<p>This hook works in combination with the locale config file to which I added two items: allowed_locales and lang.</p>

<p><tt>application/config/locale.php</tt></p>
<?php

echo geshi_highlight('<?php

$config = array
(
	// Array of locales your site is available in
	\'allowed_locales\' => array
	(
		\'nl\' => \'nl_NL\',
		\'en\' => \'en_US\',
		\'fr\' => \'fr_FR\',
		\'de\' => \'de_DE\',
	),

	// Long version of language (name of i18n folder)
	\'language\'        => \'nl_NL\',

	// Short version of language (for use in URLs)
	\'lang\'            => \'nl\',
);
', 'php', NULL, TRUE)

?>

<p>Note that from this point <tt>Kohana::lang()</tt> will pull text from the i18n/locale folder based on the language in the URL. Also, know that the current language now is available via <tt>Kohana::config('locale.lang')</tt>.</p>


<h3>3. Catch-all route</h3>

<p><tt>application/config/routes.php</tt></p>
<?php

echo geshi_highlight('<?php

// Collision check
isset($lang) and die(\'Variable collision in \'.__FILE__);

// Regex part for URL language
$lang = \'[a-zA-Z]{2}\';

$config = array
(
	// \'_default\' => \'home\',
	$lang => \'home\',

	// Catch-all language route
	$lang.\'/(.*)\' => \'$1\',
);

// Clean up
unset($lang);
', 'php', NULL, TRUE)

?>

<p>Because of these routes you can put all your controllers straight into the <tt>application/controllers</tt> folder. No need to create subfolders like <tt>application/controllers/en</tt> for every language.</p>


<h3>4. A url_lang helper</h3>

<p>Finally a simple helper that makes it just a bit easier to link to pages on your site. Calling <tt>url_lang::site('aboutus')</tt> creates a URL with the current language automatically prepended (e.g. <tt>site.com/fr/aboutus</tt>).</p>

<p><tt>application/helpers/url_lang.php</tt></p>
<?php

echo geshi_highlight('<?php defined(\'SYSPATH\') or die(\'No direct script access.\');
/*
 * Class: url_lang
 *  URL language helper class.
 */
class url_lang {

	/*
	 * Method: site
	 *  Creates a site URL based on the given URI string and
	 *  automatically prepends the language segment.
	 *
	 * Parameters:
	 *  uri      - URI string
	 *  lang     - non-default language
	 *  protocol - non-default protocol
	 *
	 * Returns:
	 *  A URL string.
	 */
	public static function site($uri = \'\', $lang = FALSE, $protocol = FALSE)
	{
		if ($lang === FALSE)
		{
			$lang = Kohana::config(\'locale.lang\');
		}

		return url::site($lang.\'/\'.trim($uri, \'/\'), $protocol);
	}

	/*
	 * Method: current
	 *
	 * Returns:
	 *  The current URI string without the lang part
	 */
	public static function current()
	{
		return substr(url::current(), 3);
	}

	/*
	 * Method: redirect
	 *  Sends a page redirect header and
	 *  automatically prepends the language segment.
	 *
	 * Parameters:
	 *  uri    - site URI or URL to redirect to
	 *  lang   - non-default language
	 *  method - HTTP method of redirect
	 *
	 * Returns:
	 *  A HTML anchor, but sends HTTP headers. The anchor should never be seen
	 *  by the user, unless their browser does not understand the headers sent.
	 */
	public static function redirect($uri = \'\', $lang = FALSE, $method = \'302\')
	{
		if ($lang === FALSE)
		{
			$lang = Kohana::config(\'locale.lang\');
		}

		return url::redirect($lang.\'/\'.trim($uri, \'/\'), $method);
	}

}
', 'php', NULL, TRUE)

?>


<h3>Final thoughts</h3>

<p>The attentive reader might remark that the .htaccess file is not strictly necessary. Right. You could perfectly move its functionality to the site_lang hook. I do prefer the .htaccess though because it is called before any PHP code is run. It may save you some regexes in the hook as well.</p>

<p>If you are wondering why I go through the hassle of including the language in every URL, here are some arguments. Number one is <abbr title="Search Engine Optimization">SEO</abbr>. Google absolutely needs to index all different translations of my site. Also, language specific URLs allow visitors to bookmark pages in the language of their choice.</p>