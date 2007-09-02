Article status [Draft] requires [Editing] Comments and corrections
# Cookie Helper
Provides methods for setting, updating and deleting COOKIES.

## Configuration
Default settings for COOKIES are specified in <file>application/config/cookie</file>. You may override
these settings by passing discrete parameters when calling the helper.

A prefix may be set to avoid name collisions.
<code>
$config['prefix']   = '';
</code>
A valid domain must be provided, a blank setting is equivalent to *localhost*
<code>
$config['domain']   = '';
</code>
A valid path must be provided.
<code>
$config['path']     = '/';
</code>
The COOKIES lifetime. Set it to the number of seconds that COOKIES should persist until expired by the browser, starting from when the COOKIES are set.
Note: Set to *0* (zero) seconds to create a cookie which expires when the browser is closed.
<code>
$config['expire']   = 0;
</code>
Secure COOKIES only. IF set to TRUE, COOKIES will **only** be created **if** the transfer protocol is secure (using HTTPS)
<code>
$config['secure']   = FALSE;
</code>
Restricted COOKIES access. If set to TRUE, then COOKIES can not be read via Client-side scripts.
<code>
$config['httponly'] = FALSE;
</code>

## Using the Cookie Helper
The helper is loaded in your <definition>Controller</definition>.
<code>
$this->load->helper('cookie');
</code>

### Set a cookie.
The **cookie::set()** method takes multiple parameters, Only the cookie name and value are required.<br />
You may pass parameters to the set method as discrete values:
<code>
cookie::set($name, $value, $expires, $path, $domain, $secure, $httponly, $prefix);
</code>
Or you may pass an array of values as a parameter:
<pre>
<code>
$cookie_params = array(
                   'name'   => 'Very_Important_Cookie',
                   'value'  => 'Choclate Flavoured Mint Delight',
                   'expire' => '86500',
                   'domain' => '.example.com',
                   'path'   => '/',
                   'prefix' => 'one_',
                       );
cookie::set($cookie_params);
</code>
</pre>

### Get a cookie
The **cookie::get()** method takes multiple parameters, Only the cookie name is required.
<pre>
<code>
$cookie_value = cookie::get($cookie_name, $prefix, TRUE);
</code>
</pre>
Setting the third parameter to TRUE will filter the cookie data for unsafe data.

### Delete a cookie
The **cookie::delete()** method takes multiple parameters, Only the cookie name is required.<br />
This method is identical to the **cookie::set()** method, but sets the cookie value to ''.
<code>
cookie::delete('stale_cookie');
</code>


*[COOKIES]: Data passed between server and browser to provide persistent state 
*[HTTPS]: HyperText Transfer Protocol Secured 