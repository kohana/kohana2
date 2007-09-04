Article status [First Draft] requires [Editing] Complete and Describe parameters
# HTML Helper
Provides methods for html generation.

### Convert special characters to HTML entities
The **html::specialchars()** method accepts multiple parameters. Only the input **string** is required.
Converts special characters to HTML entities using a UFT-8 character set.

<code>
$encoded_string = html::specialchars($string, $double_encode = TRUE);
</code>

### Generate an HTML anchor link
The **html::anchor()** method accepts multiple parameters. Only the url segment(s) are required.

A standard HTML anchor link is generated. If you want to generate a link that is internal to your website,
pass only the url segments to the function, and the anchor is automatically constructed from the site url
defined in <file>config</file>

<code>
echo html::anchor('pub/articles/7', 'Articles', array('title' => 'Fuel price increase!'));
</code>

Generates <code>&lt;a href="http://example.com/index.php/pub/articles/7" title="Fuel price increase!"&gt;Articles&lt;/a&gt;</code>

<?php /* $Id$ */ ?>