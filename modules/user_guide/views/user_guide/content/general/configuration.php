Article status [Draft] requires [Writing] Configuring the Framework, add remaining entries
# Configuration
Kohana adopts the philosophy of *convention* over *configuration*. The goal is to minimize user  configuration. Where configuration is required or enabled, sensible defaults are utilised.

System configuration is specified by entries in files located in folder */application/config/*.

Configuration files are ordinary text files with a default *php* file extension. 

The primary configuration file is <file>/application/config/config</file> System wide configuration is
specified here and is required for every new installation.

**Note:** Optional entries are indicated by square brackets.

## Config entries
### Base URL
Specifies the url which points to the base or root of your application. 
Format:
<pre>config item		domain name		/[subdirectory/]</pre>
<code>
$config['base_url'] = 'http://'.$_SERVER['SERVER_NAME'].'/kohana/';
</code>
*base_url* is **mandatory** and requires a correct server domain that **must** terminate with a "**/**"

### Front Controller
Specifies the name of the system *Front Controller*, usually <file>index</file>, but may be renamed.
Format:
<pre>config item		[front controller name]</pre>
<code>
$config['index_page'] = 'index.php';
</code>
*index_page* is **optional**. If your web server supports dynamic url re-writing. The front controller
may be dynamically removed fron the uri path. Set this item to blank if you are using re-writes.

### Url Suffix
Specifies the suffix which should be dynamically added to the uri path.
Format:
<pre>*url_suffix*		[suffix]</pre>
<code>
$config['url_suffix'] = '.html';
</code>
*url_suffix* is **optional** You may specify any valid extension as a suffix. Set to blank to remove.

### Permitted URI Characters
Specifies the characters which the system will allow as part of a uniform resource indicator.
Format:
<pre>*permitted_uri_chars*		character set</pre>
<code>
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_-';
</code>
*permitted_uri_chars* is **mandatory** A minimal character set must be specified or the system cannot function.

**Security Note:** It is recommended that the default character set be used, to secure the system against
hacking attempts. Additional characters may be configured, but please be aware of the security implication. 