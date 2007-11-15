<?php

$nl = "\n";

foreach($classes as $name => $data):

	if (strtolower($name) != 'class'):

?>
<h2><?php echo $name ?></h2>
<?php

	endif;
	$parser = new View('kodoc_html_tags');
	if (isset($data['html'])):
		// Echo out a new tag parser
		echo $parser->set('title', '')->set('html', $data['html'])->set('h', '4')->render();
	endif;
	if (isset($data['methods'])):
		foreach($data['methods'] as $name => $method):
			if (isset($method['html'])):
				echo $parser->set('title', $name)->set('html', $method['html'])->set('h', '6')->render();
			endif;
		endforeach;
	endif;
	
return;
endforeach;

?>