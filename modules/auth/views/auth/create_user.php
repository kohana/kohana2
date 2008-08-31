<div class="box">

<p class="intro">You may create new users here.</p>

<p>After creating a user, you will be automatically logged in.</p>
<p>If you want to login as a different user go to the <?php echo html::anchor('auth/login', 'login') ?> page.</p>

<?php echo form::open('auth/create', array('style' => 'width:50%;margin:0 auto;')) ?>

<?php include Kohana::find_file('views', 'kohana/form_errors') ?>

<fieldset>
<label><span>Email Address</span><?php echo form::input('email', $post['email']) ?></label>
<label><span>Username</span><?php echo form::input('username', $post['username']) ?></label>
<label><span>Password</span><?php echo form::password('password', $post['password']) ?></label>
<label><span>Confirm Password</span><?php echo form::password('password_confirm', $post['password_confirm']) ?></label>
</fieldset>

<fieldset class="submit"><?php echo form::button(NULL, 'Save User') ?></fieldset>

<?php echo form::close() ?>

</div>
