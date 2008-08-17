<?php echo form::open($action, $attributes) ?>

<?php if ( ! empty($errors)): ?>
<ul class="errors">
<?php foreach ($errors as $error): ?>
<li><?php echo $error ?></li>
<?php endforeach ?>
</ul>
<?php endif ?>

<fieldset>
<?php foreach ($inputs as $title => $input): ?>
<label><span><?php echo $title ?></span><?php echo form::input($input) ?></label>
<?php endforeach ?>
</fieldset>

<fieldset class="submit"><?php echo html::anchor($cancel, 'Cancel'), ' ', form::button(NULL, 'Save') ?></fieldset>

<?php echo form::close() ?>
