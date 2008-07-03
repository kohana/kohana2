<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Email helper class.
 *
 * $Id$
 *
 * @package    Core
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
	 * @param   string  DSN connection string
	 * @return  object  Swift object
	 */
	public static function connect($config = NULL)
	{
		if ( ! class_exists('Swift', FALSE))
		{
			// Load SwiftMailer
			require_once Kohana::find_file('vendor', 'swift/Swift');

			// Register the Swift ClassLoader as an autoload
			spl_autoload_register(array('Swift_ClassLoader', 'load'));
		}

		// Load default configuration
		($config === NULL) and $config = Config::item('email');

		switch ($config['driver'])
		{
			case 'smtp':
				// Set port
				$port = empty($config['options']['port']) ? NULL : (int) $config['options']['port'];

				if (empty($config['options']['encryption']))
				{
					// No encryption
					$encryption = Swift_Connection_SMTP::ENC_OFF;
				}
				else
				{
					// Set encryption
					switch (strtolower($config['options']['encryption']))
					{
						case 'tls': $encryption = Swift_Connection_SMTP::ENC_TLS; break;
						case 'ssl': $encryption = Swift_Connection_SMTP::ENC_SSL; break;
					}
				}

				// Create a SMTP connection
				$connection = new Swift_Connection_SMTP($config['options']['hostname'], $port, $encryption);

				// Do authentication, if part of the DSN
				empty($config['options']['username']) or $connection->setUsername($config['options']['username']);
				empty($config['options']['password']) or $connection->setPassword($config['options']['password']);

				if ( ! empty($config['options']['auth']))
				{
					// Get the class name and params
					list ($class, $params) = arr::callback_string($config['options']['auth']);

					if ($class === 'PopB4Smtp')
					{
						// Load the PopB4Smtp class manually, due to it's odd filename
						require_once Kohana::find_file('vendor', 'swift/Swift/Authenticator/$PopB4Smtp$');
					}

					// Prepare the class name for auto-loading
					$class = 'Swift_Authenticator_'.$class;

					// Attach the authenticator
					$connection->attachAuthenticator(($params === NULL) ? new $class : new $class($params[0]));
				}

				// Set the timeout to 5 seconds
				$connection->setTimeout(empty($config['options']['timeout']) ? 5 : (int) $config['options']['timeout']);
			break;
			case 'sendmail':
				// Create a sendmail connection
				$connection = new Swift_Connection_Sendmail
				(
					empty($config['options']) ? Swift_Connection_Sendmail::AUTO_DETECT : $config['options']
				);

				// Set the timeout to 5 seconds
				$connection->setTimeout(5);
			break;
			default:
				// Use the native connection
				$connection = new Swift_Connection_NativeMail;
			break;
		}

		// Create the SwiftMailer instance
		return email::$mail = new Swift($connection);
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
		(email::$mail === NULL) and email::connect();

		// Determine the message type
		$html = ($html === TRUE) ? 'text/html' : 'text/plain';

		// Create the message
		$message = new Swift_Message($subject, $message, $html, '8bit', 'utf-8');

		// Make a personalized To: address
		is_object($to) or $to = is_array($to) ? new Swift_Address($to[0], $to[1]) : new Swift_Address($to);

		// Make a personalized From: address
		is_object($from) or $from = is_array($from) ? new Swift_Address($from[0], $from[1]) : new Swift_Address($from);

		return email::$mail->send($message, $to, $from);
	}

} // End email