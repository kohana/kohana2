<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: valid
 *  Validation helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class valid_Core {

	/**
	 * Method: email
	 *  Validate email, commonly used characters only
	 *
	 * Parameters:
	 *  email - email address
	 *
	 * Returns:
	 *  TRUE if email is valid, FALSE if not.
	 */
	public static function email($email)
	{
		return (bool) preg_match('/^(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}$/iD', $email);
	}

	/**
	 * Method: email_rfc
	 *  Validate email, RFC compliant version
	 *  Note: This function is LESS strict than valid_email. Choose carefully.
	 * 
	 *  Originally by Cal Henderson, modified to fit Kohana syntax standards:
	 *  - http://www.iamcal.com/publish/articles/php/parsing_email/
	 *  - http://www.w3.org/Protocols/rfc822/
	 *
	 * Parameters:
	 *  email - email address
	 *
	 * Returns:
	 *  TRUE if email is valid, FALSE if not.
	 */
	public static function email_rfc($email)
	{
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom  = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$pair  = '\\x5c[\\x00-\\x7f]';

		$domain_literal = "\\x5b($dtext|$pair)*\\x5d";
		$quoted_string  = "\\x22($qtext|$pair)*\\x22";
		$sub_domain     = "($atom|$domain_literal)";
		$word           = "($atom|$quoted_string)";
		$domain         = "$sub_domain(\\x2e$sub_domain)*";
		$local_part     = "$word(\\x2e$word)*";
		$addr_spec      = "$local_part\\x40$domain";

		return (bool) preg_match('/^'.$addr_spec.'$/', $email);
	}

	/**
	 * Method: url
	 *  Validate URL
	 *
	 * Parameters:
	 *  url    - URL
	 *  scheme - protocol
	 *
	 * Returns:
	 *  TRUE if URL is valid, FALSE if not.
	 */
	public static function url($url, $scheme = 'http')
	{
		// Scheme is always lowercase
		$scheme = strtolower($scheme);

		// Use parse_url to validate the URL
		$url = @parse_url($url);

		// If the boolean check returns TRUE, return FALSE, and vice versa
		return ! (empty($url['host']) OR empty($url['scheme']) OR $url['scheme'] !== $scheme);
	}

	/**
	 * Method: ip
	 *  Validate IP
	 *
	 * Parameters:
	 *  ip - IP address
	 *
	 * Returns:
	 *  TRUE if IP address is valid, FALSE if not.
	 */
	public static function ip($ip)
	{
		if ( ! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/D', $ip))
			return FALSE;

		$octets = explode('.', $ip);

		for ($i = 1; $i < 5; $i++)
		{
			$octet = (int) $octets[($i-1)];
			if ($i === 1)
			{
				if ($octet > 223 OR $octet < 1)
					return FALSE;
			}
			elseif ($i === 4)
			{
				if ($octet < 1)
					return FALSE;
			}
			else
			{
				if ($octet > 254)
					return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Validates a credit card number using the Luhn (mod10) formula.
	 * @see http://en.wikipedia.org/wiki/Luhn_algorithm
	 *
	 * @param   integer  credit card number
	 * @param   string   card type
	 * @return  boolean
	 */
	public static function credit_card($number, $type = 'default')
	{
		// Remove all non-digit characters from the number
		if (($number = preg_replace('/[^0-9]/', '', $number)) === '')
			return FALSE;

		$cards = Config::item('credit_cards');

		// Check card type
		$type = strtolower($type);

		if ( ! isset($cards[$type]))
			return FALSE;

		// Check card number length
		$length = strlen($number);

		// Validate the card length by the card type
		if ( ! preg_match('/\b'.$length.'\b/', $cards[$type]['length']))
			return FALSE;

		// Check card number prefix
		if ( ! preg_match('/^'.$cards[$type]['prefix'].'/', $number))
			return FALSE;

		// No Luhn check required
		if ($cards[$type]['luhn'] == FALSE)
			return TRUE;

		// Checksum of the card number
		$checksum = 0;

		for ($i = $length - 1; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit, starting from the right
			$checksum += substr($number, $i, 1);
		}

		for ($i = $length - 2; $i >= 0; $i -= 2)
		{
			// Add up every 2nd digit doubled, starting from the right
			$double = substr($number, $i, 1) * 2;

			// Subtract 9 from the double where value is greater than 10
			$checksum += ($double >= 10) ? $double - 9 : $double;
		}

		// If the checksum is a multiple of 10, the number is valid
		return ($checksum % 10 === 0);
	}

	/**
	 * Checks if a phone number is valid.
	 *
	 * @todo  This function is not l10n-compatible.
	 *
	 * @param   string   phone number to check
	 * @return  boolean
	 */
	public static function phone($number)
	{
		$number = preg_replace('/\D+/', '', $number);

		if (strlen($number) > 10 AND substr($number, 0, 1) === '1')
		{
			// Remove the "1" prefix from the number
			$number = substr($number, 1);
		}

		// If the length is not 10, it's not a valid number
		return (strlen($number) === 10);
	}

	/**
	 * Method: alpha
	 *  Checks whether a string consists of alphabetical characters only
	 *
	 * Parameters:
	 *  str  - input string
	 *  utf8 - trigger UTF-8 compatibility
	 *
	 * Returns:
	 *  TRUE if string only contains alphabetical characters, FALSE if not.
	 */
	public static function alpha($str, $utf8 = FALSE)
	{
		return (bool) ($utf8 == TRUE)
			? preg_match('/^\pL+$/uD', (string) $str)
			: ctype_alpha((string) $str);
	}

	/**
	 * Method: alpha_numeric
	 *  Checks whether a string consists of alphabetical characters and numbers only
	 *
	 * Parameters:
	 *  str  - input string
	 *  utf8 - trigger UTF-8 compatibility
	 *
	 * Returns:
	 *  TRUE if string only contains alphabetical characters and numbers, FALSE if not.
	 */
	public static function alpha_numeric($str, $utf8 = FALSE)
	{
		return (bool) ($utf8 == TRUE)
			? preg_match('/^[\pL\pN]+$/uD', (string) $str)
			: ctype_alnum((string) $str);
	}

	/**
	 * Method: alpha_dash
	 *  Checks whether a string consists of alphabetical characters, numbers, underscores and dashes only
	 *
	 * Parameters:
	 *  str  - input string
	 *  utf8 - trigger UTF-8 compatibility
	 *
	 * Returns:
	 *  TRUE if string only contains alphabetical characters, numbers, underscores and dashes; FALSE if not.
	 */
	public static function alpha_dash($str, $utf8 = FALSE)
	{
		return (bool) ($utf8 == TRUE)
			? preg_match('/^[-\pL\pN_]+$/uD', (string) $str)
			: preg_match('/^[-a-z0-9_]+$/iD', (string) $str);
	}

	/**
	 * Method: digit
	 *  Checks whether a string consists of digits only (no dots or dashes)
	 *
	 * Parameters:
	 *  str  - input string
	 *  utf8 - trigger UTF-8 compatibility
	 *
	 * Returns:
	 *  TRUE if string only contains digits, FALSE if not.
	 */
	public static function digit($str, $utf8 = FALSE)
	{
		return (bool) ($utf8 == TRUE)
			? preg_match('/^\pN+$/uD', (string) $str)
			: ctype_digit((string) $str);
	}

	/**
	 * Method: numeric
	 *  Checks whether a string is a valid number (negative and decimal numbers allowed)
	 *
	 * Parameters:
	 *  str  - input string
	 *
	 * Returns:
	 *  TRUE if string is a valid number, FALSE if not.
	 */
	public static function numeric($str)
	{
		return (is_numeric($str) AND preg_match('/^[-0-9.]+$/', $str));
	}

	/**
	 * Method: numeric
	 *  Checks whether a string is a valid text
	 *
	 * Parameters:
	 *  str  - input string
	 *
	 * Returns:
	 *  TRUE if string is a valid text, FALSE if not.
	 */	
	public static function standard_text($str)
	{
		return preg_match('/^[-\pL\pN\pZs_]+$/uD', (string) $str);
	}

} // End valid
