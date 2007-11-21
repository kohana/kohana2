<h2>Donating</h2>
<?php echo $this->session->get('donate_status')?>
<p>To donate any amount to the Kohana project, enter the amount in the box below and click Submit. You will be sent to PayPal to login, then back to our site to complete the donation.</p>
<?php echo form::open('donate/paypal') ?>
<h4>Name</h4>
<p><?php echo form::input('name')?></p>
<h4>Email</h4>
<p><?php echo form::input('email')?></p>
<h4>Amount</h4>
<p>$<?php echo form::input('amount') ?> </p>
<p><?php echo form::submit('submit', 'Donate') ?></p>
<?php echo form::close()?>