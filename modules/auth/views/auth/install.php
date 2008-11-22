<div class="box">

<p class="intro">This demo will walk you through installing the Auth module using the ORM driver.</p>

<p>The following tables must be installed in your database: <code>users</code>, <code>roles</code>, <code>roles_users</code>, and <code>user_tokens</code>. If you have not already installed these tables, please run the installation query below.</p>

<p>After the tables have been installed, you will be able to <?php echo html::anchor('auth/create', 'create a user') ?>.</p>
<p>If you have already created an account, <?php echo html::anchor('auth/login', 'login now') ?>.</p>

<p><em>This query is MySQL-specific, but should be easy to adapt to an database that supports foreign keys.</em></p>

<?php echo form::open('auth') ?>

<?php if (is_object($result) AND $result instanceof Exception): ?>
<ul class="errors">
<li><?php echo $result->getMessage() ?></li>
</ul>
<?php endif ?>

<fieldset>
<label><span>Installation SQL</span><?php echo form::textarea(array('name' => 'query', 'style' => 'height:30em'), $sql) ?></label>
</fieldset>

<fieldset class="submit"><?php echo form::button(NULL, 'Run Query') ?></fieldset>

<?php echo form::close() ?>

</div>
