<?php

class Paypal_Controller extends Controller {

	function __construct()
	{
		parent::__construct();

		$this->paypal = new Payment();
	}

	function index()
	{
		$this->paypal->amount = 5;
		$this->paypal->process();
	}

	function return_test()
	{
		$this->paypal->amount = 5;
		$this->paypment->payerid = $this->input->get('payerid'); 
		echo '<pre>'.print_r($this->paypal->process(), true);
	}

	function cancel_test()
	{
		echo 'cancelled';
	}

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