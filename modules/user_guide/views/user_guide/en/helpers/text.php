Article status [First Draft] requires [Editing] Describe parameters
# Text Helper
Provides methods for working with TEXT.

### Word Limiter
The **text::limit_words()** method accepts multiple parameters. Only the input **string** is required.
The default end character is the ellipsis.
<pre>
<code>
$long_description = 'The rain in Spain falls mainly in the plain';
$limit = 4;
$end_char = '&amp;nbsp;';

$short_description = html::limit_words($long_description, $limit, $end_char);
</code>
</pre>
Generates: <pre>The rain in Spain </pre>


### Character Limiter
The **text::limit_chars()** method accepts multiple parameters. Only the input **string** is required.
The default end character is the ellipsis.
<pre>
<code>
$long_description = 'The rain in Spain falls mainly in the plain';
$limit = 4;
$end_char = '&amp;nbsp;';
$preserve_words = FALSE;

$short_description = html::limit_chars($long_description, $limit, $end_char, $preserve_words);
</code>
</pre>
Generates: <pre>The r </pre>