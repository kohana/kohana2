<?php

if ( ! function_exists('kodoc_html')):

function kodoc_html($comment)
{
	// Create anchors
	$comment = preg_replace_callback('/<([^ ]+) ?([^>]+)?>/', 'kodoc_html_anchor', $comment);

	// Break out the comment
	$comment = explode("\n", $comment);

	foreach($comment as $key => $line)
	{
		if (strpos($line, '-') === FALSE)
			continue;

		// Definition lists
		if (preg_match('/([a-z_]+)\s+\-\s+(.+)/', trim($line), $matches))
		{
			// Add a definition list item
			$comment[$key] =
				'<dt>'.$matches[1].'</dt>'."\n".
				'<dd>'.$matches[2].'</dd>'."\n";

			if (isset($end))
			{
				if ($line === $end)
				{
					// End the DL
					$comment[$key] .= '</dl>'."\n";

					// Clear the list
					unset($end);
				}
			}
			else
			{
				// Start the comment
				$comment[$key] = '<dl>'."\n".$comment[$key];

				// End of list
				$end = '';
			}
		}
		else
		{
			if ( ! isset($paragraph))
			{
				// Start a paragraph
				$paragraph = '<p>';

				// End of paragraph
				$end = '';
			}

			if ($line === $end)
			{
				// Set the paragraph
				$comment[$key] = $paragraph.'</p>'."\n";

				// Clear the paragraph
				unset($paragraph);

				continue;
			}

			// Add the line to the paragraph
			$paragraph .= $line.' ';

			// Remove the comment
			unset($comment[$key]);
		}
	}

	// Re-form the comment
	$comment = implode("\n", $comment);

	// Copyright symbols
	$comment = str_replace('(c)', '&copy;', $comment);

	return $comment;
}

function kodoc_html_anchor($matches)
{
	if (strpos($matches[1], '://') === FALSE)
	{
		// Add HTTP protocol
		$matches[1] = 'http://'.$matches[1];
	}

	if (empty($matches[2]))
	{
		// No title
		return html::anchor($matches[1]);
	}
	else
	{
		// With title
		return html::anchor($matches[1], $matches[2]);
	}
}

endif;
if (empty($this->kodoc) OR count($docs = $this->kodoc->get()) < 1):

?>
<p><strong>Kodoc not loaded</strong></p>
<?php

return;
endif;

?>
<h2><?php echo $docs['file'] ?></h2>
<?php

if ( ! empty($docs['comment']['about'])):

	echo kodoc_html($docs['comment']['about']);

endif;
foreach($docs['classes'] as $class):

?>
<h3><?php echo $class['name'] ?></h3>
<?php

	if ( ! empty($class['comment']['license'])):

		echo kodoc_html($docs['comment']['license']);

	endif;
	if ( ! empty($class['comment']['about'])):

		echo kodoc_html($docs['comment']['about']);

	endif;
	foreach ($class['methods'] as $method):
		$sigil = ' '.(($method['static'] == TRUE) ? '::' : '->').' ';

?>
<div class="method">
<h4><span class="visibility"><?php echo $method['visibility'] ?></span> <?php echo $class['name'].$sigil.$method['name'] ?></h4>
<div class="method"><?php echo Kohana::debug($method['about']) ?></div>
<?php

		if ($method['final']):

?>
<p class="note">This method is <tt>final</tt> and cannot be extended.</p>
<?php

		endif;
		if ($method['abstract']):

?>
<p class="note">This method is <tt>abstract</tt> and must be implemented in extended classes.</p>
<?php

		endif;

?>
</div>
<?php

	endforeach;
endforeach;

?>