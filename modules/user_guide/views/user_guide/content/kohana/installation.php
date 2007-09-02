Article status [Draft] requires [Writing] Install for Experienced users
# Install Kohana

## For New Users
If you are new to Kohana, we suggest following the steps below:

1. Unzip the Kohana package you downloaded, into a temporary directory. (Eg. */kohana*)
2. Prepare a place on your web server to upload the Kohana files to.
    * Examples:
    * */var/www/html* (Using the web server *DOCUMENT ROOT*)
    * */var/www/html/kohana* (Using a sub-directory of root)
3. Using an FTP client, upload the Kohana files to your web server.
    * Your server directory should now contain the following files and folders<br />
	<code>
	index.php<br />
	  -- application<br />
	  -- modules<br />
	  -- system
	</code>
4. Using a Text Editor, open the file <file>application/config/config</file>
    * Set the configuration item *base_url* to the name of the directory you uploaded Kohana into
    * Examples:
		1. The sub directory *kohana* of the server named *localhost* can be accessed via this URL: http://localhost/kohana
    <code>
	$config['base_url'] = 'http://'.$_SERVER['SERVER_NAME'].'/kohana/';
    </code>
		2. The *DOCUMENT ROOT* of the server named *example.com* can be accessed via this URL: http://example.com
    <code>
	$config['base_url'] = 'http://'.$_SERVER['SERVER_NAME'].'/';
    </code>
5. Test your Installation
    * Open your favourite web browser and point it at the URL defined in *base_url*
	* You should now be viewing the "Welcome page" of the Kohana User Guide, on your server.


**Congratulations!** <br />
You can explore your new Kohana Framework by reading the User Guide, a good place
to start would be [Getting Started](<?php echo url::base(TRUE).'user_guide/kohana/starting'?>)

## Experiencing Problems?
If you were not able to view the Kohana User Guide on your server after installing, we recommend
reading the [Toubleshooting](<?php echo url::base(TRUE).'user_guide/troubleshoot'?>) page of the user 
guide for more help.

If you were unable to install Kohana successfully, please visit our [wiki]("http://kohanaphp.com/wiki")
for more information, or ask for assistance in our [forums]("http://kohanaphp.com/forums"), we would
be glad to help. 

## For Experienced Users
please write me (Installing outside doc root)

*[URL]: Uniform Resource Locator