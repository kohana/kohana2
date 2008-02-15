<?php defined('SYSPATH') or die('No direct script access.');

class Kobot_Controller extends Controller {

	public function index()
	{
		// Start a new bot
		$bot = new Kobot('irc.freenode.net');

		// Enable debugging
		$bog->log_level = 2;

		// Login and join the default channel
		$bot->login('koboto');
		$bot->join('#koboto');

		$bot->read();

		// $bot->send('PRIVMSG #koboto :Go away, Shadowhand!');
		// $bot->quit('hahahaha');
	}

} // End