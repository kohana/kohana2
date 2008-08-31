<?php echo form::open($action, $attributes) ?>

<?php include Kohana::find_file('views', 'kohana/form_errors') ?>

<fieldset>
<?php foreach ($inputs as $title => $input): ?>
<label><span><?php echo $title ?></span><?php echo form::input($input) ?></label>
<?php endforeach ?>
</fieldset>

<fieldset class="submit"><?php echo html::anchor($cancel, 'Cancel'), ' ', form::button(NULL, 'Save') ?></fieldset>

<?php echo form::close() ?>
