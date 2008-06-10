<h2><?php echo Kohana::lang('donate.subtitle');?></h2>
<?php echo $this->session->get('donate_status')?>
<p><?php echo Kohana::lang('donate.help');?></p>
<?php echo form::open('donate/paypal') ?>
<h4><?php echo Kohana::lang('donate.name');?></h4>
<p><?php echo form::input('name')?></p>
<h4><?php echo Kohana::lang('donate.email');?></h4>
<p><?php echo form::input('email')?></p>
<h4><?php echo Kohana::lang('donate.amount');?></h4>
<p>$<?php echo form::input('amount') ?> </p>
<p><?php echo form::submit('submit', Kohana::lang('donate.title')) ?></p>
<?php echo form::close()?>