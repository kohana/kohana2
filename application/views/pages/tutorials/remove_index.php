<h2><span>&copy;2007 &amp; 2008, Christophe Prudent, Woody Gilk, and Jim Auldridge</span>Removing index.php From URLs</h2>

<p>Removing the <tt>index.php</tt> from your website URLs look better, and can help with <abbr title="Search Engine Optimization">SEO</abbr>.</p>

<p><strong>Note:</strong> This tutorial only focuses on Apache, but can be adapted for other HTTP servers.</p>

<h4>.htaccess</h4>

<p>We should start out by reminding you that in programming and computing there is always more than one way to accomplish the same job.  The same goes for this particular task and, as is always the case, each has its own pros and cons.  Let's look at a few to help you decide which is best for your situation.</p>

<p>First, you will need to create a <tt>.htaccess</tt> document to enable URL rewriting:</p>

<?php

echo geshi_highlight(
'# Turn on URL rewriting
RewriteEngine On

# Put your installation directory here:
# If your URL is www.example.com/, use /
# If your URL is www.example.com/kohana/, use /kohana/
RewriteBase /

# Do not enable rewriting for files or directories that exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# For reuests that are not actual files or directories,
# Rewrite to index.php/URL
RewriteRule ^(.*)$ index.php/$1 [PT,L]
', 'apache', NULL, TRUE);

?>

<p>This example is quite dynamic in that you can add files and directories to your document root as you desire and you'll never need to modify the rewrite rules.  Any files and directories that exist under your document root will be served.  If a request is made for a non-existant file or directory (which is really what the index.php-less Kohana URLs are), the request is rewritten to be routed through index.php transparently.  If it can be routed by Kohana, the page is served.  Finally, if it wasn't a request for an existing file or directory and could not be routed by Kohana, a Kohana error page (ex: 404) is displayed.  So you not only have dynamic rewrite rules, but you consistency in your error pages site wide.</p>
<p><em>However</em>em>, this approch does not protect your more sensitive an unintended PHP files against access.  So someone could enter, for example, <tt>http://www.example.com/application/views/</tt> and get a list of all your view files.  Ideally, you should never have your system or application directories, or any other files you do not want accessed, under your document root.  But some servers are setup such that your access to the server is restricted to document root and you have no choice.  Read <?php echo html::anchor('http://doc.kohanaphp.com/installation','&quot;Moving system and application directory out of webroot&quot; at http://doc.kohanaphp.com/installation'); ?> to learn how to move <tt>system/</tt> and <tt>application/</tt> out of your document root.  If you find that you do not have proper access on your server to change your file system setup, continue on in this tutorial for other options.</p>

<h4>.htaccess</h4>

<p>Let's protect against direct access to these files:</p>

<?php

echo geshi_highlight(
'# Turn on URL rewriting
RewriteEngine On

# Put your installation directory here:
# If your URL is www.example.com/kohana/, use /kohana/
# If your URL is www.example.com/, use /
RewriteBase /kohana/

# Protect application and system files from being viewed
RewriteCond $1 ^(application|system)

# Rewrite to index.php/access_denied/URL
RewriteRule ^(.*)$ index.php/access_denied/$1 [PT,L]

# Do not enable rewriting for other files that exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Rewrite to index.php/URL
RewriteRule ^(.*)$ index.php/$1 [PT,L]
', 'apache', NULL, TRUE);

?>

<h4>controllers/access_denied.php</h4>

<p>Because we are rewriting the URL to <tt>index.php/access_denied/</tt>, we will need a Controller to handle these URLs. Let's create one now:</p>

<?php echo geshi_highlight(
'<?php

class Access_denied_Controller extends Controller {

	function _remap()
	{
		// Attempted access path, with "access_denied/" removed
		$path = preg_replace(\'|^access_denied/|\', \'\', $this->uri->string());

		// Display an error page
		throw new Kohana_User_Exception
		(
			\'Direct Access Denied\',
			\'The file or directory you are attempting to access, <tt>\'.$path.\'</tt>, cannot be accessed directly. \'.
			\'You may return to the \'.html::anchor(\'\', \'home page\').\' at any time.\'
		);
	}

}
', 'php', NULL, TRUE);

?>

<p>Now, when a user attempts to access any file or directory inside of <tt>application/</tt> or <tt>system/</tt>, an error page will be displayed.</p>

<p>Now we have a good, working solution that keeps <tt>index.php</tt> our of our URL, and prevents access to protected files. However, we can still make this more secure, by only allowing specific files to be displayed, and rewriting everything else.</p>

<h4>.htaccess</h4>

<?php

echo geshi_highlight(
'# Turn on URL rewriting
RewriteEngine On

# Put your installation directory here:
# If your URL is www.example.com/kohana/, use /kohana/
# If your URL is www.example.com/, use /
RewriteBase /kohana/

# Protect application and system files from being viewed
RewriteCond $1 ^(application|system)

# Rewrite to index.php/access_denied/URL
RewriteRule ^(.*)$ index.php/access_denied/$1 [PT,L]

# Allow these directories and files to be displayed directly:
# - index.php (DO NOT FORGET THIS!)
# - robots.txt
# - favicon.ico
# - Any file inside of the images/, js/, or css/ directories
RewriteCond $1 ^(index\.php|robots\.txt|favicon\.ico|images|js|css)

# No rewriting
RewriteRule ^(.*)$ - [PT,L]

# Rewrite all other URLs to index.php/URL
RewriteRule ^(.*)$ index.php/$1 [PT,L]
', 'apache', NULL, TRUE);

?>

<p>Now we are really done. You can change the allowed files and directories to whatever you own, specific needs are.</p>

<p><strong>Note:</strong> Don't feel like you have to use the most secure solution, or any of these solutions. Choose the <tt>.htaccess</tt> file that best suits <strong>your</strong> needs. Each of the examples here will work better for some situations than others. If you want more information about mod_rewrite, check the <?php echo html::anchor('http://httpd.apache.org/docs/2.0/mod/mod_rewrite.html', 'Apache Documentation') ?>.</p>