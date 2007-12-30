<?php

$ER = error_reporting(0);

// Normalizing
$title = trim($title);
$edit_action = rtrim($edit_action, '/').'/';
$delete_action = rtrim($delete_action, '/').'/';
$new = trim($new);
$items = (array) $items;

error_reporting($ER);

?>
<?php if ($title != ''): ?>
<h2><?php echo $title ?></h2>
<?php endif ?>

<ul class="edit_list">
<?php if ($new != ''): ?>
<li class="new"><?php echo html::anchor($edit_action.'new', $new) ?></li>
<?php endif ?>
<?php foreach ($items as $id => $name): ?>
<li><?php echo html::anchor($edit_action.$id, $name) ?> <span>[<?php echo html::anchor($delete_action.$id, 'Delete') ?>]</span></li>
<?php endforeach ?>
</ul>