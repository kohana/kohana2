Article status [First Draft] requires [Editing] Complete and Describe parameters
# URL Helper
Provides methods for working with Uniform Resource Locators

### Base URL
The **url::base()** method accepts one **optional** parameter.
Returns the *base_url* defined in <file>config</file>

<code>echo url::base();</code>

### Site URL
The **url::site()** method accepts one **mandatory** parameter.
Returns a url, based on the *base_url*, *index_page*, *url_suffix* defined in <file>config</file> and the url segments passed to the method.

<code>echo url::site($uri);</code>

### Title
The **url::title()** method accepts multiple parameters. Only the input **title** string is mandatory.
Returns a properly formatted title, for use in a URI.

<pre>
<code>
$input_title = " _Eclectic title's entered by crazed users- ?&gt;  ";

echo url::title($input_title, $seperator = '_');
</code>
</pre>

Generates: <pre>Eclectic_titles_entered_by_crazed_users-</pre>

### Redirect
The **url::redirect()** method accepts multiple **optional** parameters.
Generates an HTTP Server Header (302), which will redirect the browser to a specified URL, *base_url* by default.

<code>url::redirect("www.whitehouse.gov");</code>