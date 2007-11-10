<h2><span>&copy;2007, Christophe Prudent and Woody Gilk</span>Removing index.php From URLs</h2>

<p>Removing the <tt>index.php</tt> from your website URLs look better, and can is better for <abbr title="Search Engine Optimization">SEO</abbr>.</p>

<p><strong>Note:</strong> This tutorial only focuses on Apache, but can be adapted for other HTTP servers.</p>

<h4>.htaccess</h4>

<p>First, you will need to create an <tt>.htaccess</tt> document to enable URL rewriting:</p>

<?php

echo geshi_highlight(
'# Turn on URL rewriting
RewriteEngine On

# Put your installation directory here:
# If your URL is www.example.com/kohana/, use /kohana/
# If your URL is www.example.com/, use /
RewriteBase /kohana/

# Do not enable rewriting for files that exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Rewrite to index.php/URL
RewriteRule ^(.*)$ index.php/$1 [PT,L]
', 'apache', NULL, TRUE);

?>

<p>That's it, we're done! Just kidding. Although this example works, it does not protect your PHP files against access, so someone could enter <tt>http://www.example.com/application/views/</tt> and get a list of all your view files.</p>

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