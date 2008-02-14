<?php defined('SYSPATH') or die('No direct script access.');

class Kirc_Core {

	// Database instance
	protected $db;

	// ShIRC configuration
	protected $config;

	// IRC socket
	protected $socket;

	// A newline
	protected $newline = "\r\n";

	public function __construct()
	{
		// Load the database
		$this->db = new Database('shirc');

		// Load configuration
		$this->config = Config::item('shirc');

		// Open the connection
		$this->connect();
		$this->run();
	}

	protected function connect()
	{
		// Close all output buffers
		while (ob_get_level()) ob_end_clean();

		// Handle PHP errors inline
		set_error_handler(array($this, 'error'));

		// Make the script never terminate
		set_time_limit(0);

		// Open the socket
		$this->socket = fsockopen($this->config['server'], $this->config['port']);

		// Set non-blocking streams
		stream_set_blocking($this->socket, 0);

		// Connect
		$this->send('USER '.$this->config['username'].' * * '.$this->config['realname']);
		$this->send('NICK '.$this->config['username']);
		$this->send('JOIN '.$this->config['channel']);
	}

	public function error($error, $message, $file, $line)
	{
		echo '[ERROR] '.$message.' >>> '.$file.': '.$line."\n";
	}

	protected function send($cmd)
	{
		$cmd .= $this->newline;
		fwrite($this->socket, $cmd);
		echo '[SEND] '.$cmd;
		flush();
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