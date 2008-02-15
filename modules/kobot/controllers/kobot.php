<?php defined('SYSPATH') or die('No direct script access.');

class Kobot_Controller extends Controller {

	public function index()
	{
		// Start a new bot
		$bot = new Kobot('irc.freenode.net');

		// Enable debugging
		$bog->log_level = 4;

		// Add triggers
		$bot->set_trigger('^goodnight, bot$', array($this, 'trigger_quit'))
		    ->set_trigger('^tell (.+?) about (.+)$', array($this, 'trigger_say'))
		    ->set_trigger('^([r|#])(\d+)$', array($this, 'trigger_trac'))
		    ->set_trigger('^[a-z_]+$', array($this, 'trigger_default'));

		// Add timers
		$bot->set_timer(5, array($this, 'say_hi'));

		// Login and join the default channel
		$bot->login('koboto');
		$bot->join('#koboto');
		$bot->read();
	}

	public function say_hi(Kobot $bot)
	{
		// Say hello!
		$bot->log(1, 'Just saying a timed hello!');

		// Only execute the timer once
		$bot->remove_timer(array($this, __FUNCTION__));
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
				$bot->send('PRIVMSG '.$data['target'].' :Ticket '.$params[2].', http://trac.kohanaphp.com/ticket/'.$params[2]);
			break;
			case 'r':
				$bot->send('PRIVMSG '.$data['target'].' :Revision '.$params[2].', http://trac.kohanaphp.com/changeset/'.$params[2]);
			break;
		}
	}

} // End