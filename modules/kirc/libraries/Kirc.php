<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana IRC Bot. Yah, we do that too.
 *
 * $Id$
 *
 * @package    Kirc
 * @author     Woody Gilk
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kirc_Core {

	// The characters that represent a newline
	public static $newline = "\r\n";

	// Log level: 1 = errors, 2 = debug
	public $log_level = 1;

	// IRC socket and stats
	protected $socket;
	protected $stats = array
	(
		'start'              => 0,
		'last_ping'          => 0,
		'last_sent'          => 0,
		'last_received'      => 0,
		'commands_sent'      => 0,
		'commands_received'  => 0,
	);

	// Connected channels
	protected $channels = array();

	public function __construct($server, $port = NULL, $timeout = NULL)
	{
		if (PHP_SAPI !== 'cli')
			throw new Kirc_Exception('kirc.command_line_only');

		// Close all output buffers
		while (ob_get_level()) ob_end_clean();

		// Keep-alive: TRUE
		set_time_limit(0);

		// Use internal an internal exception handler, to write logs
		set_error_handler(array($this, 'exception_handler'));
		set_exception_handler(array($this, 'exception_handler'));

		// Set the port
		empty($port) and $port = 6667;

		// Set the timeout
		empty($timeout) and $timeout = 10;

		// Disable error reporting
		$ER = error_reporting(0);

		if ($this->socket = fsockopen($server, $port, $errno, $errstr, $timeout))
		{
			// Enable error reporting
			error_reporting($ER);

			// Set the start time
			$this->stats['start'] = microtime(TRUE);

			// Keep the response time as short as possible, for greater interactivity
			stream_set_blocking($this->socket, 0);

			// Connection is complete
			$this->log(1, 'Connected to '.$server.':'.$port);
		}
		else
		{
			// Nothing left to do if the connection fails
			$this->log(1, 'Could not to connect to '.$server.':'.$port.' in less than '.$timeout.' seconds: '.$errstr);
			exit;
		}
	}

	public function exception_handler($exception, $message = NULL, $file = NULL, $line = NULL)
	{
		if (func_num_args() === 5)
		{
			if ((error_reporting() & $exception) !== 0)
			{
				// PHP Error
				$this->log(1, $message.' in '.$file.' on line '.$line);
			}
		}
		else
		{
			// Exception
			$this->log(1, $exception->getMessage());
		}
	}

	public function log($level, $message)
	{
		if ($level >= $this->log_level)
		{
			echo date('Y-m-d g:i:s').' --- '.$message."\n"; flush();
		}
	}

	public function login($username, $password = NULL, $realname = 'Kohana PHP Bot')
	{
		// Send the login commands
		$this->send('USER '.$username.' * * :'.$realname);
		$this->send('NICK '.$username);

		// Update the last ping
		$this->stats['last_ping'] = microtime(TRUE);

		// Read the MOTD before continuing
		$this->read(375, 376, FALSE);
		$this->log(2, 'Received MOTD (suppressed)');
	}

	public function join($channel)
	{
		if (empty($this->channels[$channel]))
		{
			// Join the channel
			$this->send('JOIN '.$channel);

			// Read the USERS command
			$this->channels[$channel] = explode(':', $this->read(353));
			$this->channels[$channel] = explode(' ', $this->channels[$channel][1]);

			// The end of the USERS command
			$this->read(366, FALSE);
			$this->log(2, 'Received USERS (suppressed)');
		}
	}

	public function part($channel)
	{
		if ( ! empty($this->channels[$channel]))
		{
			// Leave the channel
			$this->send('PART '.$channel);

			// Remove the channel
			unset($this->channels[$channel]);
		}
	}

	public function quit($message = '</Kirc> by Kohana Team')
	{
		// Quit, wait, and exit
		$this->send('QUIT '.$message);
		sleep(2);
		exit;
	}

	public function send($command)
	{
		if (feof($this->socket))
		{
			// The socket has been terminated unexpectedly. Abort, now!
			$this->log(1, 'Disconnected unexpectedly, shutting down.');
			exit;
		}

		if (fwrite($this->socket, $command.self::$newline))
		{
			// Log the sent command
			$this->log(2, '>>> '.$command);

			// Update the stats
			$this->stats['last_sent'] = microtime(TRUE);
		}
		else
		{
			// Log error
			$this->log(1, 'Error sending command >>> '.$command);
		}
	}

	public function read($start = NULL, $end = NULL, $return = TRUE)
	{
		if (is_bool($end))
		{
			// Return value and end are shifted one place
			$return = $end;
			$end = NULL;
		}

		// Make sure the start and end are strings
		($start === NULL) or $start = (string) $start;
		($end   === NULL) or $end   = (string) $end;

		$buffer = '';
		while ( ! feof($this->socket))
		{
			while ($raw = fgets($this->socket, 128))
			{
				if ( ! empty($start))
				{
					// Get the host and command from the stream
					list ($host, $cmd, $msg) = explode(' ', $raw, 3);

					if ($cmd === $start)
					{
						// Trim the message
						$msg = trim($msg);

						if (empty($end))
						{
							// Return the message
							return ($return === TRUE) ? $msg : NULL;
						}

						$buffer .= $msg;
					}

					if ( ! empty($buffer))
					{
						if ($return === TRUE)
						{
							// Only add to the buffer for returns
							$buffer .= $msg;
						}

						if ($cmd === $end)
						{
							// Return the complete buffer
							return ($return === TRUE) ? $buffer : NULL;
						}
					}
				}
				else
				{
					$this->log(2, '<<< '.trim($raw));
				}
			}
			// One half-second is high enough interactivity
			usleep(500000);
		}
	}

	protected function run()
	{
		// Current username and size of username
		$bot = $this->config['username'];
		$len = strlen($bot);

		// Parts of a publicly spoken message
		$parts = array('nickname', 'username', 'hostname', 'channel', 'message');

		while ( ! feof($this->socket))
		{
			while ($raw = fgets($this->socket, 1024))
			{
				// Remove extra whitespace
				$raw = trim($raw);

				if (substr($raw, 0, 4) === 'PING')
				{
					// Send a PONG response
					$this->send('PONG'.substr($raw, 4));
					break;
				}
				else
				{
					if (strpos($raw, 'PRIVMSG') !== FALSE
					    AND strpos($raw, $bot) !== FALSE
					    AND preg_match('/^:(.+?)!n=(.+?)@(.+?) PRIVMSG (#.+?) :(.+)$/', $raw, $data))
					{
						// Make an associative array of the data
						$data = array_combine($parts, array_slice($data, 1));

						if (substr($data['message'], 0, $len) === $bot)
						{
							// A command has been sent
							$command = ltrim(substr($data['message'], $len), ' :;,');

							if ($command === 'say hello')
							{
								$this->send('PRIVMSG '.$data['channel'].' :Go away, '.$data['nickname'].'!');
							}
							elseif (preg_match('/^r(\d+)$/', $command, $match))
							{
								// The URL for a revision number
								$url = 'http://trac.kohanaphp.com/changeset/'.$match[1];

								if ($this->url_status($url))
								{
									$this->send('PRIVMSG '.$data['channel'].' :Revision r'.$match[1].', '.$url);
								}
							}
							elseif (preg_match('/^#(\d+)$/', $command, $match))
							{
								// The URL for a ticket number
								$url = 'http://trac.kohanaphp.com/ticket/'.$match[1];

								if ($this->url_status($url))
								{
									$this->send('PRIVMSG '.$data['channel'].' :Ticket #'.$match[1].', '.$url);
								}
							}
						}
					}
					else
					{
						echo $raw."\n";
					}

					// 
					// if (($offset = strpos($raw, ' PRIVMSG :'.$user)) !== FALSE AND )
					// {
					// 	
					// }
					// list ($host, $cmd, $msg) = explode(' ', $raw, 3);
					// 
					// $host = trim($host);
					// $cmd  = trim($cmd);
					// $msg  = trim($msg);
					// 
					// list ($chan, $msg) = explode(' ', $msg);
					// $msg = substr($msg, 1);
					// 
					// print_r(array('host' => $host, 'cmd' => $cmd, 'chan' => $chan, 'msg' => $msg));

					// if (($offset = strpos($raw, ':', 1)) !== FALSE)
					// {
					// 	if (($offset = substr($raw, $offset, $size)) === $user)
					// 	{
					// 		// Process the command
					// 		$this->send('PRIVMSG '.$chan.' :saying hello?');
					// 	}
					// }
				}

				// Flush the console output
				flush();
			}
		}
	}

	protected function url_status($url)
	{
		if (($status = $this->db_url_status($url)) === NULL)
		{
			// Extract the URL params
			extract(parse_url($url), EXTR_PREFIX_ALL, 'url');

			// Invalid URL by default
			$status = FALSE;
			if ($socket = fsockopen($url_host, 80, $errno, $errstr, 6))
			{
				// Fetch the HTTP HEAD
				fwrite($socket, "HEAD $url_path HTTP/1.0\r\nHost: $url_host\r\n\r\n");

				// Read the response
				$status = fgets($socket, 22);

				// Set the response
				$status = (strpos($status, '200 OK') !== FALSE);

				// Close the connection
				fclose($socket);
			}

			// Save the URL to the database
			$this->db->insert('urls', array('url' => $url, 'status' => (int) $status));
		}

		return $status;
	}

	protected function db_url_status($url)
	{
		// Fetch the status of the URL
		$status = $this->db->select('status')->where('url', $url)->limit(1)->get('urls');

		return $status->count() ? (bool) $status->current()->status : NULL;
	}

}