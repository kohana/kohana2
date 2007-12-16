<?php echo $open; ?>
<table class="<?php echo $class ?>">
<caption><?php echo $title ?></caption>
<?php
foreach($inputs as $input):

$sub_inputs = array();
if ($input->type == 'group'):
	$sub_inputs = $input->inputs;

?>
<tr>
<th colspan="2"><?php echo $input->label() ?></th>
</tr>
<tr>
<td colspan="2"><p class="group_message"><?php echo $input->message() ?></p></td>
</tr>
<?php

else:
	$sub_inputs = array($input);	
endif;

foreach($sub_inputs as $input):

?>
<tr>
<th><?php echo $input->label() ?></th>
<td>
<?php

echo $input->html();

if ($message = $input->message()):

?>
<p class="message"><?php echo $message ?></p>
<?php

endif;

foreach ($input->error_messages() as $error):

?>
<p class="error"><?php echo $error ?></p>
<?php

endforeach;

?>
</td>
</tr>
<?php

endforeach;

endforeach;
?>
</table>
<?php echo $close ?>