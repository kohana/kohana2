<div class="box">

<p class="intro">This is your user information, <?php echo $user->username ?>.</p>

<p>You may <?php echo html::anchor('auth/logout', 'log out') ?>. If no longer want this account, <?php echo html::anchor('auth/delete/'.$user->username, 'delete it') ?>.</p>

<dl>
	<dt>Username &amp; Email Address</dt>
	<dd><?php echo $user->username ?> &mdash; <?php echo $user->email ?></dd>

	<dt>Login Activity</dt>
	<dd>Last login was <?php echo date('F jS, Y', $user->last_login) ?>, at <?php echo date('h:i:s a', $user->last_login) ?>.<br/>Total logins: <?php echo $user->logins ?></dd>

	<dt>Roles</dt>
<?php foreach ($user->roles as $role): ?>
	<dd><?php echo $role->name ?> &mdash; <?php echo $role->description ?></dd>
<?php endforeach ?>

</dl>

</div>
