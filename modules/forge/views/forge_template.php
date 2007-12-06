<?php echo $open; ?>
<table class="<?php echo $class ?>">
<caption><?php echo $title ?></caption>
<?php foreach($inputs as $input): ?>
<tr>
<th><?php echo $input->label() ?></th>
<td><?php echo $input->html() ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php echo $close ?>