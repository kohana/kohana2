<?php

class Paypal_Controller extends Controller {

	// This will demo a simple paypal transaction. It really only comes down to two steps.
	function __construct()
	{
		parent::__construct();

		$this->paypal = new Payment();
	}

	// This will set up the transaction and redirect the user to paypal to login
	function index()
	{
		$this->paypal->amount = 5;
		$this->paypal->process();
	}

	// Once the user logs in, paypal redirects them here (set in the config file), which processes the payment
	function return_test()
	{
		$this->paypal->amount = 5;
		$this->paypment->payerid = $this->input->get('payerid'); 
		echo ($this->paypal->process()) ? "WORKED" : "FAILED";
	}

	// This is the cancel URL (set from the config file)
	function cancel_test()
	{
		echo 'cancelled';
	}

	// Just some utility functions.
	function reset_session()
	{
		$this->session->destroy();
		url::redirect('paypal/index');
	}

	function session_status()
	{
		echo '<pre>'.print_r($this->session->get(), true);
	}
}