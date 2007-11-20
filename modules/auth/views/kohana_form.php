<?php echo form::open($action) ?>

<table class="form">
<caption><?php echo $title ?></caption>
<?php

foreach($inputs as $name => $data):

	// Generate label
	$label = empty($data['label']) ? '' : form::label($name, arr::remove('label', $data))."\n";

	// Generate error
	$error = arr::remove('error', $data);

	// Set input name and id
	$data['name'] = $name;

	if ( ! empty($data['options']))
	{
		// Get options and selected
		$options  = arr::remove('options', $data);
		$selected = arr::remove('selected', $data);
		// Generate dropdown
		$input = form::dropdown($data, $options, $selected);
	}
	else
	{
		switch(@$data['type'])
		{
			case 'textarea':
				// Remove the type, textarea doesn't need it
				arr::remove('type', $data);
				// Generate a textarea
				$input = form::textarea($data);
			break;
			case 'submit':
				// Generate a submit button
				$input = form::button($data);
			break;
			default:
				// Generate a generic input
				$input = form::input($data);
			break;
		}
	}
?>
<tr>
<td><label><?php echo $label ?></label></td>
<td><?php echo $input.$error ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php echo form::close() ?>