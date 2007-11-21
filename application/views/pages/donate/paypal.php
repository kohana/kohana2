<h2>Donate via PayPal</h2>
<p>Click the confirm button below to finalize your donation.</p>
<?php echo form::open('donate/process_paypal', array(), array('payerid' => $payerid, 'donate_amount' => $donate_amount))?>
<p>You are donating <strong>$<?php echo $donate_amount; ?></strong></p>
<p><?php echo form::submit('Confirm')?></p>
<?php echo form::close()?>