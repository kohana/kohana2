<?php echo $open; ?>
<table class="<?php echo $class ?>">
<caption><?php echo $title ?></caption>
<?php
foreach($inputs as $input):

$sub_inputs = array();
if ($input->type == 'group')
{
	$sub_inputs = $input->inputs;

?>
<tr>
<th colspan="2"><?php echo $input->label() ?></th>
</tr>
<?php

}
else
{
	$sub_inputs = array($input);	
}

foreach($sub_inputs as $input):

?>
<tr>
<th><?php echo $input->label() ?></th>
<td><?php echo $input->html() ?></td>
</tr>
<?php

endforeach;

endforeach;
?>
</table>
<?php echo $close ?>