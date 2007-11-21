<h2>Donating</h2>
<p>To donate any amount to the Kohana project, enter the amount in the box below and click Submit. You will be sent to PayPal to login, then back to our site to complete the donation.</p>
<?php echo form::open('donate/paypal') ?>
<?php echo form::hidden('payerid', 'KohanaUser') ?>
<h4>Amount</h4>
<p>$<?php echo form::input('amount') ?> </p>
<p><?php echo form::submit('submit', 'Donate') ?></p>
<?php echo form::close()?>