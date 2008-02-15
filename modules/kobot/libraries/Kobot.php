<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana IRC Bot. Yah, we do that too.
 *
 * $Id$
 *
 * @package    Kobot
 * @author     Woody Gilk
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Kobot_Core {

	// The characters that represent a newline
	public static $newline = "\r\n";

	// Log level: 1 = errors, 2 = debug
	public $log_level = 1;

	// Command responses
	protected $responses = array();

	// Responses to drop by default
	protected $dropped = array
	(
		'NOTICE',
		'001',
		'002',
		'003',
		'004',
		'005',
		'251',
		'252',
		'254',
		'255',
		'265',
		'266',
		'250',
		'366',
		'477',
	);

	// IRC socket, MOTD, and stats
	protected $socket;
	protected $motd;
	protected $stats = array
	(
		'start'              => 0,
		'last_ping'          => 0,
		'last_sent'          => 0,
		'last_received'      => 0,
	);

	// Connected channels
	protected $channels = array();

	public function __construct($server, $port = NULL, $timeout = NULL)
	{
		if (PHP_SAPI !== 'cli')
			throw new Kohana_Exception('kobot.command_line_only');

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

			foreach ($this->dropped as $cmd)
			{
				// Drop all requested commands
				$this->set_response($cmd, array($this, 'response_drop'));
			}

			// Read the PING command
			$this->set_response('PING', array($this, 'response_ping'));

			// Read the MOTD command
			$this->set_response('375', array($this, 'response_motd'));
			$this->set_response('372', array($this, 'response_motd'));
			$this->set_response('376', array($this, 'response_motd'));

			// Read the USERS command
			$this->set_response('353', array($this, 'response_userlist'));

			// Read the JOIN command
			$this->set_response('JOIN', array($this, 'response_join'));

			// Read the PART command
			$this->set_response('PART', array($this, 'response_part'));
		}
		else
		{
			// Nothing left to do if the connection fails
			$this->log(1, 'Could not to connect to '.$server.':'.$port.' in less than '.$timeout.' seconds: '.$errstr);
			exit;
		}
	}

	public function login($username, $password = NULL, $realname = 'Kohana PHP Bot')
	{
		// Send the login commands
		$this->send('USER '.$username.' * * :'.$realname);
		$this->send('NICK '.$username);

		// Update the last ping
		$this->stats['last_ping'] = microtime(TRUE);
	}

	public function join($channel)
	{
		if (empty($this->channels[$channel]))
		{
			// Set the channel as joined
			$this->channels[$channel] = array();

			// Join the channel
			$this->send('JOIN '.$channel);
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

	public function read()
	{
		while ( ! feof($this->socket))
		{
			// Read the raw server stream, up to 512 characters
			while ($raw = fgets($this->socket, 512))
			{
				// Update the last received time
				$this->stats['last_received'] = microtime(TRUE);

				// Parse the raw string into a command array
				$data = $this->parse($raw);

				if (isset($this->responses[$data['command']]))
				{
					// Call the response handler
					call_user_func($this->responses[$data['command']], $data);
				}
				else
				{
					// Debug the response
					$this->log(2, 'NOT FOUND: '.$data['command'].' <<< '.trim($raw));
				}
			}
			// One half-second is high enough interactivity
			usleep(500000);
		}
	}

	/**
	 * Parses are raw server string into a command array.
	 *
	 * @param   string   raw server string
	 * @return  array    sender, sendhost, command, target, message
	 */
	protected function parse($raw)
	{
		// Remove the whitespace garbage
		$raw = trim($raw);

		// These values are always returned
		$data = array
		(
			'sender'   => NULL,
			'sendhost' => NULL,
			'command'  => NULL,
			'target'   => NULL,
			'message'  => NULL,
		);

		// Extract the prefix from the string
		list ($prefix, $str) = explode(' ', $raw, 2);

		if ( ! empty($prefix) AND $prefix{0} === ':')
		{
			// A user-level command, like PRIVMSG or NOTICE
			$prefix = substr($prefix, 1);

			if (strpos($prefix, '!') !== FALSE)
			{
				// sender@host, typically a user
				list ($data['sender'], $data['sendhost']) = explode('!', $prefix, 2);
			}
			else
			{
				// sender, Typically a server
				$data['sender'] = $prefix;
			}

			if (strpos($str, ' ') !== FALSE)
			{
				// CMD str, Extract the command from the remaining string
				list ($data['command'], $str) = explode(' ', $str, 2);

				if (strpos($str, ' :') !== FALSE)
				{
					// target :message, some kind of communication
					list ($data['target'], $data['message']) = explode(' :', $str, 2);
				}
				elseif ($str{0} === ':')
				{
					// :target, without a message
					$data['target'] = substr($str, 1);
				}
				else
				{
					// target, with nothing
					$data['target'] = $str;
				}
			}
			else
			{
				$data['command'] = $str;
			}
		}
		else
		{
			// A server-level command, like PING
			$data['command'] = $prefix;
			$data['message'] = empty($str) ? NULL : $str;
		}

		return $data;
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
								;
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

	public function log($level, $message)
	{
		if ($level >= $this->log_level)
		{
			// Display the message with a timestamp, flush the output
			echo date('Y-m-d g:i:s').' --- '.$message."\n"; flush();
		}
	}

	public function exception_handler($e, $message = NULL, $file = NULL, $line = NULL)
	{
		if (func_num_args() === 5)
		{
			if ((error_reporting() & $e) !== 0)
			{
				// PHP Error
				$this->log(1, $message.' in '.$file.' on line '.$line);
			}
		}
		else
		{
			// Exception
			$this->log(1, strip_tags($e->getMessage()).' File: '.$e->getFile().' on line '.$e->getLine());
		}
	}

	public function add_trigger($callback, $pattern)
	{
		// TODO
		return $this;
	}

	public function set_response($command, $callback)
	{
		if ( ! is_callable($callback))
			throw new Kohana_Exception('kobot.invalid_callback', $command);

		// Set the response callback
		$this->responses[$command] = $callback;

		return $this;
	}

	/**
	 * Kobot default responses. You can overload these in your own extension class,
	 * or attach your own event handlers
	 */

	// *
	public function response_drop()
	{
		// Silence is golden
	}

	// PING
	public function response_ping($data)
	{
		// Update the stats
		$this->stats['last_ping'] = microtime(TRUE);

		// Reply with a PONG
		$this->send('PONG '.substr($data['message'], 1));
	}

	// 375, 372+, 376
	public function response_motd($data)
	{
		switch ($data['command'])
		{
			case '375':
				// Prepare to read the MOTD
				$this->motd = array();
			break;
			case '372':
				// Read the MOTD
				$this->motd[] = substr($data['message'], 2);
			break;
			case '376':
				// Log the number of lines in the MOTD
				$this->log(1, 'Read '.count($this->motd).' MOTD lines');

				// Make the MOTD into a string
				$this->motd = implode("\n", $this->motd);
			break;
		}
	}

	// 353, 366
	public function response_userlist($data)
	{
		if (strpos($data['target'], ' @ ') !== FALSE)
		{
			// Get the channel name from the target
			list ($bot, $channel) = explode(' @ ', $data['target'], 2);

			// Set the current users
			$this->channels[$channel] = explode(' ', $data['message']);

			// Log the user count
			$this->log(1, 'Found '.count($this->channels[$channel]).' users in channel');
		}
	}

	// JOIN
	public function response_join($data)
	{
		// Make sure the bot is joined to the target channel
		if (isset($this->channels[$data['target']]))
		{
			// Only add the user if they are not already in the list
			if ( ! in_array($data['sender'], $this->channels[$data['target']]))
			{
				// Add the sender to the channel userlist
				$this->channels[$data['target']][] = $data['sender'];

				// Debug the join
				$this->log(2, '> '.$data['sender'].' ('.$data['target'].')');
			}
		}
	}

	// PART
	public function response_part($data)
	{
		// Make sure the bot is joined to the target channel
		if (isset($this->channels[$data['target']]))
		{
			// Only remove the user if they are in the list
			if (($key = array_search($data['sender'], $this->channels[$data['target']])) !== FALSE)
			{
				// Remove the sender from the channel userlist
				unset($this->channels[$data['target']][$key]);

				// Debug the join
				$this->log(2, '< '.$data['sender'].' ('.$data['target'].')');
			}
		}
	}

} // End Kobot