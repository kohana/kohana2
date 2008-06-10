<h2><?php echo Kohana::lang('donate.paypal');?></h2>
<p><?php echo Kohana::lang('donate.paypal_help');?></p>
<?php echo form::open('donate/process_paypal', array(), array('payerid' => $payerid, 'donate_amount' => $donate_amount))?>
<p><?php echo Kohana::lang('donate.total_amount', $donate_amount);?></p>
<p><?php echo form::submit('Confirm', Kohana::lang('donate.confirm_payment'))?></p>
<?php echo form::close()?>