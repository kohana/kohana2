<?php defined('SYSPATH') or die('No direct script access.');

class Kobot_Controller extends Controller {

	public function index()
	{
		// Start a new bot
		$bot = new Kobot('irc.freenode.net');

		// Enable debugging
		$bog->log_level = 4;

		// Add triggers
		$bot->add_trigger('^goodnight, bot$', array($this, 'trigger_quit'))
		    ->add_trigger('^tell (.+?) about (.+)$', array($this, 'trigger_say'))
		    ->add_trigger('^([r|#])(\d+)$', array($this, 'trigger_trac'))
		    ->add_trigger('^[a-z_]+$', array($this, 'trigger_default'));

		// Login and join the default channel
		$bot->login('koboto');
		$bot->join('#koboto');

		$bot->read();

		// $bot->send('PRIVMSG #koboto :Go away, Shadowhand!');
		// $bot->quit('hahahaha');
	}

	public function trigger_default(Kobot $bot, array $data, array $params)
	{
		if (function_exists($params[0]))
		{
			$bot->send('PRIVMSG '.$data['target'].' :'.$data['sender'].': http://php.net/'.$params[0]);
		}
	}

	public function trigger_quit(Kobot $bot, array $data)
	{
		$bot->quit('goodnight, '.$data['sender']);
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

	public function trigger_trac(Kobot $bot, array $data, array $params)
	{
		switch ($params[1])
		{
			case '#':
				$bot->send('PRIVMSG '.$data['target'].' :Ticket #'.$params[2].' is http://trac.kohanaphp.com/ticket/'.$params[2]);
			break;
			case 'r':
				$bot->send('PRIVMSG '.$data['target'].' :Revision r'.$params[2].' is http://trac.kohanaphp.com/changeset/'.$params[2]);
			break;
		}
	}

} // End