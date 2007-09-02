Article status [Draft] requires [Editing] Configuring the Framework, add remaining entries
# Configuration
Kohana adopts the philosophy of *convention* over *configuration*. The goal is to minimize user  configuration. Where configuration is required or enabled, sensible defaults are utilised.

System configuration is specified by entries in files located in folder */application/config/*

Configuration files are ordinary text files with a default *php* file extension. 

The primary configuration file is <file>application/config/config</file> System wide configuration is
specified here and is required for every new installation.

**Note:** Optional entries are indicated by square brackets.

## Config entries
### Base URL
Specifies the url which points to the base or root of your application. 
<pre>
base_url			domain name/[subdirectory/]
<code>
$config['base_url'] = 'http://'.$_SERVER['SERVER_NAME'].'/kohana/';
</code>
</pre>
*base_url* is **mandatory** and requires a correct server domain that **must** terminate with a "**/**"

### Front Controller
Specifies the name of the system <definition>Front Controller</definition>, usually <file>index</file>, but may be renamed.
<pre>
index_page			[front controller name]
<code>
$config['index_page'] = 'index.php';
</code>
</pre>
*index_page* is **optional**. If your web server supports dynamic URL re-writing. The front controller
may be dynamically removed fron the URI path. Set this item to blank if you are using re-writes.

### URL Suffix
Specifies the suffix which should be dynamically added to the URI path.
<pre>
url_suffix		[suffix]
<code>
$config['url_suffix'] = '.html';
</code>
</pre>
*url_suffix* is **optional**. You may specify any valid extension as a suffix. Set to blank to remove.

### Permitted URI Characters
Specifies the characters which the system will allow as part of a uniform resource indicator.
<pre>
permitted_uri_chars		character set
<code>
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_-';
</code>
</pre>
*permitted_uri_chars* is **mandatory**. A minimal character set must be specified or the system cannot function.

**Security Note:** It is recommended that the default character set be used, to secure the system against
hacking attempts. Additional characters may be configured, but please be aware of the security implication.

### Locale
Specifies the system locale using the standard two letter abbreviation.
<pre>
locale			international language abbreviation
<code>
$config['locale'] = 'en';
</code>
</pre>
*locale* is **mandatory**. Kohana default is English.

### Include Paths
Specifies additional locations the system should search for resources. 
<pre>
include_paths			[file path names]
<code>
$config['include_paths'] = array
(
	'modules/user_guide'
);
</code>
</pre>
*include_paths* is **optional**. Kohana will always search the application path first, then any defined
include paths, and finally the system path. Primary resources are libraries, helpers and views.

### Enable Hooks
Specifies the enabling of system Hooks  
<pre>
enable_hooks			boolean
<code>
$config['enable_hooks'] = FALSE;
</code>
</pre>
*enable_hooks* is **mandatory**. By default Hooks are disabled. If you plan to add to hooks into your
application, you will activate them by setting this entry to *TRUE*.

### SubClass Prefix
Specifies the prefix for user extensions to Core Kohana classes.
<pre>
subclass_prefix			prefix_
<code>
$config['subclass_prefix'] = 'MY_';
</code>
</pre>
*subclass_prefix* is **mandatory**. Core class extensions must be prefixed. 
By convention the prefix "My_" is used. You may change this prefix, provided it terminates with an *underscore*. If you are not extending Core classes, you may safely leave the default setting.

### Timezone
Specifies an standard international timezone for the system.
<pre>
timezone			[zone/location]
<code>
$config['timezone'] = '';
</code>
</pre>
*timezone* is **optional**. By default, the setting is blank and Kohana will use the timezone supplied by
PHP on the server. An example timezone would be "Africa/Johannesburg".

## Autoloader Entries
To maintain a small footprint, resources are manually loaded in your Controller. In Applications where
a resource is utilised across many Controllers, you may specify that Kohana automatically loads the resource and makes it available to all Controllers.
 
Such resources would typically be databases, libraries (session), and helpers (url).
<pre>
resource type			[resource name[,resource name&hellip;]]
<code>
$config['libraries'] = 'session, encrypt';
</code>
</pre>
Note: If you are specifying that a resource is autoloaded, you still need to ensure that the resource is
correctly configured, via it's own configuration file. For example, if you autoload the Cookie Helper,
then you must check that cookies are correctly configured for your system in <file>application/config/cookie</file>

## Configuration Files
The following files are used for Class configuration.

* <file>application/config/cookie</file> configures Cookies, used by the [Cookie helper](<?php echo url::base(TRUE).'user_guide/helpers/cookie' ?>) class.
* <file>application/config/database</file> configures Databases, used by the Database class.
* <file>application/config/encryption</file> configures Encryption, used by the Encryption class.
* <file>application/config/log</file> configures Logging, used by the Log class.
* <file>application/config/pagination</file> configures Pagination, used by the Pagination class.
* <file>application/config/routes</file> configures Routing, used by the Routing class.
* <file>application/config/session</file> configures Sessions, used by the Session class.


<p></p>
*[PHP]: PHP Hypertext Processor
*[URI]: Uniform Resource Indicator
*[URL]: Uniform Resource Locator