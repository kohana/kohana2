<h2>Donate via PayPal</h2>
<p>Click the confirm button below to finalize your donation.</p>
<?php echo form::open('donate/process_paypal', array(), array('payerid' => $payerid, 'donate_amount' => $donate_amount))?>
<h4>Name</h4>
<p>$<?php echo form::input('name')?></p>
<h4>Email</h4>
<p>$<?php echo form::input('email')?></p>
<h4>Amount</h4>
<p>$<?php echo form::input('amount')?></p>
<p><?php echo form::submit('Confirm')?></p>
<?php echo form::close()?>