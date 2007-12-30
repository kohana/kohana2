<h2>Users</h2>

<ul class="user_list">
<li class="new"><?php echo html::anchor('admin/manage_users/new', 'Add New User') ?></li>
<?php foreach ($users as $id => $username): ?>
<li><?php echo html::anchor('admin/manage_users/'.$id, $username) ?> <span>[<?php echo html::anchor('admin/delete_user/'.$id, 'Delete') ?>]</span></li>
<?php endforeach ?>
</ul>