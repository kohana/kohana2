<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Captcha driver for "riddle" style.
 *
 * $Id$
 *
 * @package    Captcha
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Captcha_Riddle_Driver extends Captcha_Driver {

	private $question;

	/**
	 * Generates a new Captcha challenge.
	 *
	 * @return  string  the challenge answer
	 */
	public function generate_challenge()
	{
		// TODO: pull random riddle from i18n file
		//       make a selection based on complexity setting?
		$riddles = array
		(
			array
			(
				'Do you hate spam? (yes or no)',
				'yes'
			),
			array
			(
				'Fire is... (hot or cold)',
				'hot'
			),
			array
			(
				'Which day of the week is it today?',
				strftime('%A')
			),
		);

		// Pick a riddle
		$riddle = $riddles[array_rand($riddles)];

		// Store the question for output
		$this->question = $riddle[0];

		// Return the answer
		return $riddle[1];
	}

	/**
	 * Outputs the Captcha riddle.
	 *
	 * @param   boolean  html output
	 * @return  mixed
	 */
	public function render($html)
	{
		return $this->question;
	}

} // End Captcha Riddle Driver Class