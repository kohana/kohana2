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

	// IRC socket, MOTD, and stats
	protected $socket;
	protected $motd;
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
		echo Kohana::debug($this->parse(':anthony.freenode.net 353 koboto @ #koboto :koboto @Shadowhand'));exit;
		
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

			// Automatically reply to PING comamnds
			$this->set_response('ping', array($this, 'pong'));
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
			// Display the message with a timestamp, flush the output
			echo date('Y-m-d g:i:s').' --- '.$message."\n"; flush();
		}
	}

	public function set_response($command, $callback)
	{
		if ( ! is_callable($callback))
			throw new Kohana_Exception('kobot.invalid_callback');

		// Set the response callback
		$this->responses[$command] = $callback;

		return $this;
	}

	public function login($username, $password = NULL, $realname = 'Kohana PHP Bot')
	{
		// Send the login commands
		$this->send('USER '.$username.' * * :'.$realname);
		$this->send('NICK '.$username);

		// Update the last ping
		$this->stats['last_ping'] = microtime(TRUE);

		// Set the MOTD response
		$this->set_response('375', array($this, 'read_motd'));
		$this->set_response('372', array($this, 'read_motd'));
		$this->set_response('376', array($this, 'read_motd'));
	}

	public function read_motd($data)
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

	public function join($channel)
	{
		if (empty($this->channels[$channel]))
		{
			// Set the channel as joined
			$this->channels[$channel] = array();

			// Join the channel
			$this->send('JOIN '.$channel);

			// Read the USERS command
			$this->set_response('353', array($this, 'read_userlist'));
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
			while ($raw = fgets($this->socket, 512))
			{
				$this->log(2, '<<< '.trim($raw));

				// Parse the command
				$data = array_combine(array('sender', 'sendhost', 'command', 'target', 'message'), $this->parse($raw));

				// Run the event
				Event::run('kirc.'.strtolower($data['command']), $data);
			}
			// One half-second is high enough interactivity
			usleep(500000);
		}
	}

	// Return: array(sender, sendhost, command, target, message)
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

			// CMD str, Extract the command from the remaining string
			list ($data['command'], $str) = explode(' ', $str, 2);

			if (strpos($str, ' :') !== FALSE)
			{
				// target :message, some kind of communication
				list ($data['target'], $data['message']) = explode(' :', $params, 2);
			}
			elseif ($str{0} === ':')
			{
				// :target, without a message
				$data['target'] = substr($str, 1);
			}
			else
			{
				$data['target'] = $str;
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

	/**
	 * Kobot default responses. You can overload these in your own extension class,
	 * or attach your own event handlers
	 */

	public function response_drop($data)
	{
		// Log dropped responses
		$this->log(2, $data);
	}

	public function response_ping($data)
	{
		// Reply with a PONG
		$this->send('PONG '.substr($data['message'], 1));
	}

	// 353
	public function response_userlist($data)
	{
		if (strpos($data['target'], ' @ ') !== FALSE)
		{
			// Get the channel name from the target
			list ($bot, $channel) = explode(' @ ', $data['target'], 2);

			// Set the current users
			$this->channels[$channel] = explode(' ', $data['message']);

			// Log the user count
			$this->log(1, 'Read '.count($this->channels[$channel]).' users');
		}
	}

} // End Kobot