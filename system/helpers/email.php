<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Email helper class.
 *
 * $Id$
 *
 * @package    Validation
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class email_Core {

	// SwiftMailer instance
	protected static $mail;

	/**
	 * Creates a SwiftMailer instance.
	 *
	 * @param   string   DSN connection string
	 * @return  void
	 */
	public static function connect($dsn = NULL)
	{
		// Load default configuration
		($dsn === NULL) and $dsn = Config::item('email.dsn');

		if ( ! class_exists('Swift', FALSE))
		{
			// Load SwiftMailer
			require_once Kohana::find_file('vendor', 'swift/Swift');

			// Register the Swift ClassLoader as an autoload
			spl_autoload_register(array('Swift_ClassLoader', 'load'));
		}

		if (substr($dsn, 0, 4) === 'smtp')
		{
			$dsn = parse_url($dsn);

			// Create the connection
			$connection = new Swift_Connection_SMTP($dsn['host'], empty($dsn['port']) ? 25 : (int) $dsn['port']);

			// Do authentication, if part of the DSN
			empty($dsn['user']) or $connection->setUsername($dsn['user']);
			empty($dsn['pass']) or $connection->setPassword($dsn['pass']);

			// Set the timeout to 5 seconds
			$connection->setTimeout(5);
		}
		elseif (substr($dsn, 0, 8) === 'sendmail')
		{
			if (($dsn = substr($dsn, 11)) === '')
			{
				// Auto-detect the paths
				$dsn = Swift_Connection_Sendmail::AUTO_DETECT;
			}
			else
			{
				// Add the sendmail flags
				$dsn.= ' -bs';
			}

			// Use the sendmail connection
			$connection = new Swift_Connection_Sendmail($dsn);

			// Set the timeout to 5 seconds
			$connection->setTimeout(5);
		}
		else
		{
			// Use the native connection
			$connection = new Swift_Connection_NativeMail;
		}

		// Create the SwiftMailer instance
		self::$mail = new Swift($connection);
	}

	/**
	 * Send an email message.
	 *
	 * @param   string|array  recipient email (and name)
	 * @param   string|array  sender email (and name)
	 * @param   string        message subject
	 * @param   string        message body
	 * @param   boolean       send email as HTML
	 * @return  integer       number of emails sent
	 */
	public static function send($to, $from, $subject, $message, $html = FALSE)
	{
		// Connect to SwiftMailer
		(self::$mail === NULL) and self::connect();

		// Determine the message type
		$html = ($html === TRUE) ? 'text/html' : 'text/plain';

		// Create the message
		$message = new Swift_Message($subject, $message, $html, '8bit', 'utf-8');

		// Make a personalized From: address
		$to = is_array($to) ? new Swift_Address($to[0], $to[1]) : new Swift_Address($to);

		// Make a personalized From: address
		$from = is_array($from) ? new Swift_Address($from[0], $from[1]) : new Swift_Address($from);

		return self::$mail->send($message, $to, $from);
	}

} // End email