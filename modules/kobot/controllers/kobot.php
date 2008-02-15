<?php defined('SYSPATH') or die('No direct script access.');

class Kobot_Controller extends Controller {

	public function index()
	{
		// Start a new bot
		$bot = new Kobot('irc.freenode.net');

		// Add triggers
		$bot->add_trigger(array($this, 'trigger_say'),  '^tell (.+?) about (.+)$')
		    ->add_trigger(array($this, 'trigger_trac'), '^[r|#](\d+)$')
		    ->add_trigger(array($this, 'trigger_php'),  '^([a-z_]+)$');

		// Enable debugging
		$bog->log_level = 2;

		// Login and join the default channel
		$bot->login('koboto');
		$bot->join('#koboto');

		$bot->read();

		// $bot->send('PRIVMSG #koboto :Go away, Shadowhand!');
		// $bot->quit('hahahaha');
	}

	public function trigger_say(Kobot $bot, array $data, array $params)
	{
		switch ($params[1])
		{
			case 'yourself':
				$bot->send('PRIVMSG '.$data['target'].' :Who wants to know? '.$params[0].'? HA!');
			break;
		}
	}

} // End