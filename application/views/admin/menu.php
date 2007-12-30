<ul>
<?php foreach ($actions as $action): ?>
<li><?php echo html::anchor('admin/'.$action, ucwords(inflector::humanize($action))) ?></li>
<?php endforeach ?>
</ul>