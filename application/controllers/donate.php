<?php defined('SYSPATH') or die('No direct script access.');

class Donate_Controller extends Controller {

	protected $auth_required = 'developer';

	protected $auto_render = TRUE;

	protected $payment;

	public function __construct()
	{
		parent::__construct();

		// Load Payment
		$this->payment = new Payment();
	}

	public function index()
	{
		$this->template->set(array
		(
			'title'   => 'Donate',
			'content' => new View('pages/donate/index')
		));
	}

	public function paypal()
	{
		if ($amount = $this->input->post('amount')) // They are coming from index()
		{
			$this->auto_render = FALSE;

			// Set the payment amount in session for when they return from paypal
			$this->session->set(array('donate_amount' => $amount, 'donate_name' => $this->input->post('name'), 'donate_email' => $this->input->post('email')));

			// Set the amount and send em to PayPal
			$this->payment->amount = $amount;
			$status = $this->payment->process();
			
			// Something went wrong, so delete the session data and make em start over again.
			$this->session->set_flash('donate_status', '<p style="color: red;">There was a problem processing your donation. Please try again.</p>');
			$this->session->del('donate_amount', 'donate_name', 'donate_email', 'reshash', 'paypal_token');
			url::redirect('donate');
		}
		else if ($amount = $this->session->get('donate_amount') AND $payerid = $this->input->get('PayerID')) // They are returning from paypal
		{
			// Display the final 'order' page
			$this->template->set(array
			(
				'title'   => 'Donate',
				'content' => new View('pages/donate/paypal', array('payerid' => $payerid, 'donate_amount' => $amount))
			));
		}
		else
		{
			// They shouldn't be here!
			$this->auto_render = FALSE;
			url::redirect();
		}
	}

	public function process_paypal()
	{
		$this->payment->amount  = $this->input->post('donate_amount');
		$this->payment->payerid = $this->input->post('payerid');

		// Try and process the payment
		if ($status = $this->payment->process() === TRUE)
		{
			// Store the payment
			$insert = array('name'   => ($this->session->get('donate_name') == '') ? 'Anonymous' : $this->session->get('donate_name'),
			                'email'  => $this->session->get('donate_email'),
			                'amount' => $this->session->get('donate_amount'),
			                'date'   => time(),
			                'type'   => 'PayPal');

			$this->db->insert('donations', $insert);

			// Remove the session data
			$this->session->del('donate_amount', 'donate_name', 'donate_email', 'reshash', 'paypal_token');

			$this->template->set(array
			(
				'title'   => 'Donate',
				'content' => new View('pages/donate/paypal_success')
			));
		}
		else
		{
			$this->session->set_flash('donate_status', '<p style="color: red;">There was a problem processing your donation. Please try again.</p>');
			$this->session->del('donate_amount', 'donate_name', 'donate_email', 'reshash', 'paypal_token');
			$this->template->set(array
			(
				'title'   => 'Donate',
				'content' => new View('pages/donate/paypal_error')
			));
		}
	}

	public function credit_card()
	{
		
	}

	public function donation_list()
	{
		$payments = new Payments_Model();
		$payment_list = $payments->find_all();

		$this->template->set(array
		(
			'title'   => 'Donation List',
			'content' => new View('pages/donate/list', array('donation_list' => $payment_list))
		));
	}

} // End Donate