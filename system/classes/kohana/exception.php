<?php

class Kohana_Exception_Core extends Exception {

	// Error code
	protected $code = E_KOHANA;

	/**
	 * Creates a new i18n Kohana_Exception using the passed error and arguments.
	 *
	 * @return  void
	 */
	public function __construct($error)
	{
		$args = array_slice(func_get_args(), 1);

		// Fetch the error message
		$message = Kohana::lang($error, $args);

		if ($message === $error OR empty($message))
		{
			// Unable to locate the message for the error
			$message = 'Unknown Exception: '.$error;
		}

		// Sets $this->message the proper way
		parent::__construct($message);
	}

} // End Kohana_Exception
