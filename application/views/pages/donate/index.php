<h2><?php echo Kohana::lang('donate.subtitle');?></h2>
<p><?php echo Kohana::lang('donate.help') ?></p>

<?php echo form::open(NULL, array('accept-charset' => 'utf-8')) ?>

<?php include Kohana::find_file('views', 'form_errors') ?>

<h4><?php echo Kohana::lang('donate.name') ?></h4>
<p><?php echo form::input('name', $post['name']) ?></p>

<h4><?php echo Kohana::lang('donate.email') ?></h4>
<p><?php echo form::input('email', $post['email']) ?></p>

<h4><?php echo Kohana::lang('donate.amount');?></h4>
<p>$<?php echo form::input('amount', $post['amount']) ?> </p>

<p><?php echo form::submit('submit', Kohana::lang('donate.title')) ?></p>

<?php echo form::close()?>