<?php defined('SYSPATH') or die('No direct script access.');

class Donate_Controller extends Website_Controller {

	public $auto_render = TRUE;

	public function index()
	{
		$this->template->title   = Kohana::lang('donate.title');
		$this->template->content = View::factory('pages/donate/index')
			->bind('post', $post)
			->bind('errors', $errors);

		$post = Validation::factory($_POST)
			->pre_filter('trim')
			->add_rules('name', 'valid::standard_text')
			->add_rules('email', 'valid::email')
			->add_rules('amount', 'required', 'valid::decimal[2]');

		if ($post->validate())
		{
			// Format the donation amount
			$post['amount'] = number_format($post['amount'], 2);

			// Set session variables
			$_SESSION['donation'] = array
			(
				'name'   => $post['name'],
				'email'  => $post['email'],
				'amount' => $post['amount'],
			);

			// Set the PayPal parameters
			$params = array
			(
				'cmd'           => '_donations',
				'item_name'     => 'Kohana PHP Framework',
				'amount'        => $post['amount'],
				'currency_code' => 'USD',
				'charset'       => 'utf-8',
				'no_note'       => 1,
				'no_shipping'   => 1,
				'return'        => url::site('donate/thanks', 'http'),
				'cancel_return' => url::site('donate', 'http'),
				'business'      => 'alwayson@kohanaphp.com',
			);

			// Send the user to PayPal to complete their payment
			url::redirect('https://www.paypal.com/cgi-bin/webscr?'.http_build_query($params));
		}

		if ($post['amount'] > 0)
		{
			// Format the post amount
			$post['amount'] = number_format($post['amount'], 2);
		}

		// Load errors
		$errors = $post->errors('donate.errors');
	}

	public function thanks()
	{
		if ( ! isset($_SESSION['donation']))
			url::redirect('donate');

		// Create the donation and save it
		$donation = ORM::factory('donation');
		$donation->type   = 'PayPal';
		$donation->name   = empty($_SESSION['donation']['name']) ? 'Anonymous' : $_SESSION['donation']['name'];
		$donation->email  = $_SESSION['donation']['email'];
		$donation->amount = $_SESSION['donation']['amount'];
		$donation->save();

		// Clear the donation information
		unset($_SESSION['donation']);

		$this->template->title   = Kohana::lang('donate.title');
		$this->template->content = View::factory('pages/donate/thanks');
	}

	public function donation_list()
	{
		$this->template->title   = Kohana::lang('donate.list');
		$this->template->content = View::factory('pages/donate/list')
			->bind('donation_list', $donations);

		// Load all donations
		$donations = ORM::factory('donation')->find_all();
	}

	public function _check_amount($amount)
	{
		return ((float) $amount > 0.50);
	}

} // End Controller_Donate